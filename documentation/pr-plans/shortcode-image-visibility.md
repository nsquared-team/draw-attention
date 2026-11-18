## Goal
Site owners who keep a Draw Attention image as a draft, private, pending, scheduled or password-protected stop having its hotspot text and picture shown to every visitor, and no existing site changes what it shows on the day it updates.

Focus of this PR: visitors see an interactive image only when WordPress lets them read it, without changing any page that works today — a visibility check in the `[drawattention]` shortcode handler with an exception for the images a site already shows only, nothing else.

Scope: `DrawAttention::shortcode()` in `public/class-drawattention.php` picks the newest image in any status and prints it without asking who is looking. The block, the Beaver Builder module and the single image view all call that one handler. In: after the image is picked, the handler returns nothing unless the image is published or the viewer may read it, and it returns WordPress's password form while a password is still owed. Which image gets picked stays as it is. Existing sites: on the first load after the update the plugin stores, in the option `da_legacy_visible_images`, the images that are unpublished or password-protected at that moment, since the shortcode showed them until now. Each of them skips the check until its owner next changes its status or password. A notice on such an image's edit screen tells the owner visitors can see it and offers one link to hide it. Plus one end-to-end script and its CI step. ⏩ Out: honouring the `id` attribute; the Pro plugin's handler. Shape: about 70 lines across `public/class-drawattention.php` and `admin/class-drawattention-admin.php`, one new test script, one step in `.github/workflows/tests.yml`, plus this file.
Risk: every front-end render of the plugin goes through this handler. An existing site whose image is unpublished keeps showing it, so that site stays exposed until its owner acts on the notice. A site that imports a newer unpublished image shows nothing to visitors until that image is published. Cached pages keep the old HTML until the cache clears. Undo: revert this PR; the stored option is then unused. The 1%: a published image with no password must render exactly as before, since that is nearly every site.

## Definition of Done
- [ ] On a site that never showed an unpublished image, a visitor who is not logged in gets none of the image's hotspot text or picture from a page with `[drawattention]` while the image is a draft, pending or private.
- [ ] On that site, while the image has a password, the visitor gets WordPress's password form and none of the image's content.
- [ ] A published image with no password renders for that visitor as it did before.
- [ ] A logged-in editor still sees a draft image through the shortcode.
- [ ] A site that already showed a draft image before the update still shows it to visitors after the update.
- [ ] Once that image's status or password changes, or its owner follows the notice's hide link, visitors get it only when WordPress lets them read it.
- [ ] `tests/e2e/shortcode-visibility.sh` fails against master's handler, passes on this branch, and runs in CI's end-to-end job.
- [ ] No other code path in the plugin prints an image's hotspots to a visitor without going through this check.
