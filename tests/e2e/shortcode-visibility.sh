#!/usr/bin/env bash
# End-to-end check of who gets to see the interactive image, against a running
# wp-env whose dev site maps the BUILT plugin (see smoke.sh).
#
# Proves that a visitor who is not logged in:
#  - sees a published image, and none of a draft, pending or private one;
#  - gets WordPress's password form for a password-protected one, on every site, and
#    lands back on the same page once the password is right;
#  - still sees an unpublished image the site showed before the plugin checked
#    visibility, until its owner hides it or changes its status.
# And that an editor still sees a draft.
#
# Usage: tests/e2e/shortcode-visibility.sh [site-url]   (default: http://localhost:8888)
set -euo pipefail
SITE_URL="${1:-http://localhost:8888}"
MARKER="da-visibility-marker"
JAR=$(mktemp)
trap 'rm -f "$JAR"' EXIT

cli() { wp-env run cli wp "$@"; }
fail() {
  echo "FAIL: $1" >&2
  exit 1
}
set_image() { cli post update "$IMAGE_ID" "$@" >/dev/null; }
# What a visitor gets from the page that embeds the shortcode.
visitor_page() { curl -sL --fail "$SITE_URL/?page_id=$PAGE_ID"; }
visitor_sees_image() {
  local body
  # A page that fails to load must never count as "the image is hidden".
  body=$(visitor_page) || fail "the page that embeds the shortcode did not load"
  grep -q "$MARKER" <<<"$body"
}
# Start over as a site whose images were stored before the plugin checked visibility.
forget_update() { cli option delete da_legacy_visible_images >/dev/null; }
# The image's edit screen, as its logged-in owner gets it.
edit_screen() {
  local body
  body=$(curl -sL -b "$JAR" "$SITE_URL/wp-admin/post.php?post=$IMAGE_ID&action=edit")
  # A screen that fails to load must never count as "the notice is gone".
  grep -q "name=.post_ID." <<<"$body" || fail "the image's edit screen did not load"
  echo "$body"
}

echo "--- set up: an image with one hotspot, on a page ---"
cli plugin activate draw-attention
# A site that has never shown an unpublished image.
cli eval "update_option( 'da_legacy_visible_images', array() );"
IMAGE_ID=$(cli post create --post_type=da_image --post_title="Visibility Image" --post_status=publish --porcelain | tail -1 | tr -dc '0-9')
[ -n "$IMAGE_ID" ]
cli media import wp-admin/images/wordpress-logo.png --post_id="$IMAGE_ID" --featured_image
cli eval "update_post_meta( $IMAGE_ID, '_da_hotspots', array( array( 'title' => '$MARKER', 'coordinates' => '10,10,60,10,60,60', 'shape' => 'polygon' ) ) );"
PAGE_ID=$(cli post create --post_type=page --post_title="Visibility Page" --post_status=publish --post_content='[drawattention]' --porcelain | tail -1 | tr -dc '0-9')
[ -n "$PAGE_ID" ]

echo "--- a published image shows to a visitor ---"
visitor_sees_image || fail "a published image is missing for a visitor"

echo "--- a draft, pending or private image does not ---"
for status in draft pending private; do
  set_image --post_status="$status"
  if visitor_sees_image; then fail "a $status image shows to a visitor"; fi
done

echo "--- an editor still sees a draft ---"
set_image --post_status=draft
EDITOR_ID=$(cli user create "da-editor-$IMAGE_ID" "da-editor-$IMAGE_ID@example.com" --role=editor --porcelain | tail -1 | tr -dc '0-9')
[ -n "$EDITOR_ID" ]
EDITOR_VIEW=$(cli eval "wp_set_current_user( $EDITOR_ID ); echo do_shortcode( '[drawattention]' );")
grep -q "$MARKER" <<<"$EDITOR_VIEW" || fail "an editor no longer sees a draft image"

echo "--- a password-protected image shows the password form instead ---"
set_image --post_status=publish --post_password=secret
PROTECTED=$(visitor_page)
if grep -q "$MARKER" <<<"$PROTECTED"; then fail "a password-protected image shows to a visitor"; fi
grep -q "post-password-form" <<<"$PROTECTED" || fail "a password-protected image shows no password form"

echo "--- and the right password brings the visitor back to that page, with the image ---"
PAGE_URL=$(curl -sL -o /dev/null -w '%{url_effective}' "$SITE_URL/?page_id=$PAGE_ID")
# Send what a browser would: the password, plus the form's own redirect field if it has one.
FORM_FIELDS=(--data-urlencode "post_password=secret")
REDIRECT_TO=$(grep -o 'name="redirect_to" value="[^"]*"' <<<"$PROTECTED" | sed -e 's/^.*value="//' -e 's/"$//' -e 's/&#038;/\&/g' -e 's/&amp;/\&/g' || true)
[ -z "$REDIRECT_TO" ] || FORM_FIELDS+=(--data-urlencode "redirect_to=$REDIRECT_TO")
UNLOCKED=$(curl -sL -b "" -e "$PAGE_URL" -w '\nlanded on %{url_effective}' "${FORM_FIELDS[@]}" "$SITE_URL/wp-login.php?action=postpass")
grep -qxF "landed on $PAGE_URL" <<<"$UNLOCKED" || fail "the right password did not bring the visitor back to the page"
grep -q "$MARKER" <<<"$UNLOCKED" || fail "the right password did not show the image"

echo "--- an unpublished image the site already showed keeps showing after the update ---"
set_image --post_status=draft --post_password=""
forget_update
visitor_sees_image || fail "a draft image the site already showed went missing after the update"

echo "--- until its owner follows the edit screen's hide link ---"
curl -s -o /dev/null -c "$JAR" -b "wordpress_test_cookie=WP+Cookie+check" \
  -d "log=admin" -d "pwd=password" -d "wp-submit=Log+In" -d "testcookie=1" "$SITE_URL/wp-login.php"
EDIT_SCREEN=$(edit_screen)
HIDE_PATH=$(grep -o 'href="[^"]*da_hide_legacy_image[^"]*"' <<<"$EDIT_SCREEN" | sed -n '1p' | sed -e 's/^href="//' -e 's/"$//' -e 's/&#038;/\&/g' -e 's/&amp;/\&/g' || true)
[ -n "$HIDE_PATH" ] || fail "the edit screen does not offer to hide an image the site keeps showing"
curl -sL -o /dev/null -b "$JAR" "$SITE_URL$HIDE_PATH"
if visitor_sees_image; then fail "a draft image still shows after its owner hid it"; fi

echo "--- or until its status changes ---"
forget_update
visitor_sees_image || fail "a draft image the site already showed went missing after the update"
set_image --post_status=private
if visitor_sees_image; then fail "an image still shows after its status changed to private"; fi

echo "--- or until WordPress changes it, as it does when a scheduled image goes live ---"
set_image --post_status=draft
forget_update
visitor_sees_image || fail "a draft image the site already showed went missing after the update"
# What WordPress's scheduler calls when a scheduled image's time comes.
cli eval "wp_publish_post( $IMAGE_ID );"
EDIT_SCREEN=$(edit_screen)
if grep -q "da_hide_legacy_image" <<<"$EDIT_SCREEN"; then fail "the edit screen still offers to hide an image after WordPress published it"; fi

echo "--- a password-protected image the site already showed asks for its password at once ---"
set_image --post_status=publish --post_password=secret
forget_update
AFTER_UPDATE=$(visitor_page)
if grep -q "$MARKER" <<<"$AFTER_UPDATE"; then fail "a password-protected image the site already showed still shows without its password"; fi
grep -q "post-password-form" <<<"$AFTER_UPDATE" || fail "a password-protected image the site already showed shows no password form"
EDIT_SCREEN=$(edit_screen)
if grep -q "da_hide_legacy_image" <<<"$EDIT_SCREEN"; then fail "the edit screen's notice shows for a password-protected image"; fi

echo "--- and so does an unpublished image the site keeps showing, once it has a password ---"
set_image --post_status=draft --post_password=secret
forget_update
AFTER_UPDATE=$(visitor_page)
if grep -q "$MARKER" <<<"$AFTER_UPDATE"; then fail "an unpublished image the site keeps showing shows without its password"; fi
grep -q "post-password-form" <<<"$AFTER_UPDATE" || fail "an unpublished image the site keeps showing shows no password form"

echo "OK: shortcode visibility passed (image $IMAGE_ID on page $PAGE_ID)"
