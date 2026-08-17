#!/usr/bin/env bash
# End-to-end boot smoke against a running wp-env whose dev site maps the BUILT
# plugin (build/draw-attention) at wp-content/plugins/draw-attention.
#
# Proves, on a clean WordPress, what was verified by hand on a real site:
#  - the plugin activates without a fatal;
#  - the da_image post type registers;
#  - a published image's page renders (HTTP 200) and enqueues the plugin's
#    assets (the drawattention script handle observed on a live install).
#
# Usage: tests/e2e/smoke.sh [site-url]   (default: http://localhost:8888)
set -euo pipefail
SITE_URL="${1:-http://localhost:8888}"

cli() { wp-env run cli wp "$@"; }

echo "--- activate plugin ---"
cli plugin activate draw-attention

echo "--- post type registered ---"
cli post-type list --field=name | grep -qx "da_image"

echo "--- publish an image and render its page ---"
POST_ID=$(cli post create --post_type=da_image --post_title="CI Smoke Image" --post_status=publish --porcelain | tail -1 | tr -dc '0-9')
[ -n "$POST_ID" ]

BODY=$(curl -sL --fail "$SITE_URL/?post_type=da_image&p=$POST_ID")
echo "$BODY" | grep -q "drawattention-plugin-script-js" || {
  echo "FAIL: rendered page is missing the drawattention script handle" >&2
  exit 1
}

echo "OK: e2e smoke passed (post $POST_ID rendered with plugin assets)"
