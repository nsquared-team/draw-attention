#!/usr/bin/env bash
# End-to-end check of who may read or replace a post's featured picture through the
# bundled uploader's AJAX requests, against a running wp-env whose dev site maps the
# BUILT plugin (see smoke.sh).
#
# Proves that:
#  - a subscriber who asks for the featured picture of a draft gets a refusal and no
#    URL, while an administrator still gets it;
#  - an editor with the upload nonce the image's edit screen prints cannot set a picture
#    on a post they may not edit, and no file is stored;
#  - that editor still uploads a picture to the image.
#
# Usage: tests/e2e/uploader-access.sh [site-url]   (default: http://localhost:8888)
set -euo pipefail
SITE_URL="${1:-http://localhost:8888}"
AJAX_URL="$SITE_URL/wp-admin/admin-ajax.php"
PICTURE="wordpress-logo"
UPLOAD="da-uploader-test"
WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT

cli() { wp-env run cli wp "$@"; }
fail() {
  echo "FAIL: $1" >&2
  exit 1
}
# Log in over HTTP as a browser does, into a cookie jar named after the user.
login() {
  curl -s -o /dev/null -c "$WORK/$1" -b "wordpress_test_cookie=WP+Cookie+check" \
    --data-urlencode "log=$1" --data-urlencode "pwd=$2" -d "wp-submit=Log+In" -d "testcookie=1" "$SITE_URL/wp-login.php"
  # A login that failed must never count as "the request was refused".
  grep -q "wordpress_logged_in" "$WORK/$1" || fail "could not log in as $1"
}
# What the uploader answers when the named user asks for a post's featured picture.
featured_picture() { curl -s -b "$WORK/$1" -d "action=dgd_get_featured_image" -d "post_id=$2" "$AJAX_URL"; }
# The ID of a post's featured picture, or 0 when it has none.
thumbnail_of() { cli eval "echo (int) get_post_meta( $1, '_thumbnail_id', true );" | tail -1 | tr -dc '0-9'; }
# How many copies of the test picture the site has stored this month.
stored_files() { cli eval "echo count( glob( wp_upload_dir()['path'] . '/$UPLOAD*' ) );" | tail -1 | tr -dc '0-9'; }
# Upload the test picture to a post as the editor, with the nonce the image's edit screen
# printed for them. Prints the HTTP status.
upload_to() {
  curl -s -o /dev/null -w '%{http_code}' -b "$WORK/$EDITOR" \
    -F "_ajax_nonce=$NONCE" -F "action=dgd_upload_featured_image" -F "postID=$1" \
    -F "async-upload=@$WORK/$UPLOAD.png;type=image/png" "$AJAX_URL"
}

echo "--- set up: a draft with a featured picture, a subscriber and an editor ---"
cli plugin activate draw-attention
PASS=$(openssl rand -hex 12)
RUN=$(date +%s)
SUBSCRIBER="da-subscriber-$RUN"
EDITOR="da-editor-$RUN"
cli user create "$SUBSCRIBER" "$SUBSCRIBER@example.com" --role=subscriber --user_pass="$PASS" >/dev/null
cli user create "$EDITOR" "$EDITOR@example.com" --role=editor --user_pass="$PASS" >/dev/null
DRAFT_ID=$(cli post create --post_title="Uploader Draft" --post_status=draft --porcelain | tail -1 | tr -dc '0-9')
[ -n "$DRAFT_ID" ]
cli media import wp-admin/images/wordpress-logo.png --post_id="$DRAFT_ID" --featured_image >/dev/null

echo "--- a subscriber does not get the featured picture of a draft ---"
login "$SUBSCRIBER" "$PASS"
REFUSED=$(featured_picture "$SUBSCRIBER" "$DRAFT_ID")
# A request that never reached the uploader must never count as a refusal.
grep -q '"response_code"' <<<"$REFUSED" || fail "the featured picture request did not reach the uploader"
if grep -q "$PICTURE" <<<"$REFUSED"; then fail "a subscriber reads the featured picture of a draft"; fi
grep -q '"response_code":403' <<<"$REFUSED" || fail "a subscriber is not told they may not edit the post"

echo "--- an administrator still gets it ---"
login admin password
grep -q "$PICTURE" <<<"$(featured_picture admin "$DRAFT_ID")" || fail "an administrator no longer gets a post's featured picture"

echo "--- an editor cannot set a picture on a post they may not edit ---"
IMAGE_ID=$(cli post create --post_type=da_image --post_title="Uploader Image" --post_status=publish --porcelain | tail -1 | tr -dc '0-9')
# Only an administrator may edit a site menu.
MENU_ID=$(cli post create --post_type=wp_navigation --post_title="Uploader Menu" --post_status=publish --porcelain | tail -1 | tr -dc '0-9')
[ -n "$IMAGE_ID" ] && [ -n "$MENU_ID" ]
login "$EDITOR" "$PASS"
NONCE=$(curl -sL -b "$WORK/$EDITOR" "$SITE_URL/wp-admin/post.php?post=$IMAGE_ID&action=edit" | grep -o '"_ajax_nonce":"[^"]*","action":"dgd_upload_featured_image"' | sed -n '1p' | sed -e 's/^"_ajax_nonce":"//' -e 's/".*$//' || true)
[ -n "$NONCE" ] || fail "the image's edit screen prints no upload nonce for an editor"
# A one-pixel PNG.
base64 --decode <<<"iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==" >"$WORK/$UPLOAD.png"
BEFORE=$(stored_files)
STATUS=$(upload_to "$MENU_ID")
[ "$(thumbnail_of "$MENU_ID")" = 0 ] || fail "an editor sets a picture on a post they may not edit"
[ "$(stored_files)" = "$BEFORE" ] || fail "a refused upload still stores its file"
[ "$STATUS" = 403 ] || fail "an upload to a post the user may not edit is not refused (HTTP $STATUS)"

echo "--- and still uploads a picture to the image ---"
upload_to "$IMAGE_ID" >/dev/null
[ "$(thumbnail_of "$IMAGE_ID")" != 0 ] || fail "an editor can no longer upload a picture to the image"

echo "OK: uploader access passed (draft $DRAFT_ID, image $IMAGE_ID, menu $MENU_ID)"
