## Goal
Site owners stop having a logged-in user read the featured picture of a post they may not edit, or replace it, through the image uploader Draw Attention bundles.

Focus of this PR: users reach only featured pictures of posts they may edit — an edit-permission check in the uploader's AJAX handlers only, nothing else.

Scope: the bundled uploader in `public/includes/lib/drag-drop-featured-image/index.php` registers three AJAX requests for logged-in users. `ajax_get_featured_image()` returns the featured picture's URL of any post ID it is given, and checks nothing, so a subscriber reads the picture of a draft. `ajax_upload_image_file()` checks the upload nonce the image edit screen prints, which is tied to no post, then stores the file and sets it as the featured picture of whatever post the request names. Only an editor or an administrator can open that screen, so the upload gap reaches only posts of a type they may not edit, such as a site menu. `ajax_set_featured_image()` already asks whether the user may edit the post. In: the other two ask the same question before they do anything. A user who may not edit the post gets a refusal and nothing else: no URL, no stored file, no changed picture. The upload handler reads the post ID as a number, as its two siblings do. Plus one end-to-end script that proves both refusals and that an allowed user still reads and uploads, and the CI step that runs it in the existing end-to-end job. ⏩ Out: Draw Attention Pro, which bundles the same uploader; a nonce on the read request. Shape: about 12 lines in one file; a test script of about 100 lines; one step in the test workflow; plus this file.
Risk: every featured picture set on the image's edit screen goes through these handlers. A user who can open that screen may edit the image, so the screen keeps working. Anything else that calls these two requests for a post its user may not edit now gets a refusal. The upload is refused before the file is stored, so a refused upload leaves nothing behind. Undo: revert this PR. The 1%: an upload on the image's own edit screen must still set the picture.

## Definition of Done
- [ ] A logged-in subscriber who asks for the featured picture of a draft they may not edit gets a refusal with no picture URL in it.
- [ ] An editor with a valid upload nonce who uploads to a post they may not edit leaves that post without a featured picture, and the site stores no file.
- [ ] A user who may edit the post still gets its picture's URL, and an upload on the image's edit screen still sets the picture.
- [ ] No other AJAX request in the plugin's own code or its bundled uploader lets a logged-in user read or change a post they may not edit, proven by the search that found none.
- [ ] `tests/e2e/uploader-access.sh` fails against master's handlers with "a subscriber reads the featured picture of a draft", passes on this branch, and runs in CI.
- [ ] All of the above holds with the PHP error log clean.
