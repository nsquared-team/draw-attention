# PR plan — fix-plugin-check-findings

## Goal
Turn the Plugin Check job introduced by the add-plugin-check-ci PR green, by fixing our own code's findings at the root and scoping the scan so red means a new regression.

## Definition of Done
- [ ] Every own-code ERROR reported by Plugin Check on the first PR is either fixed in code or suppressed in the workflow with a stated reason.
- [ ] Vendored third-party libraries (CMB2, drag-drop-featured-image) are excluded from the scan — their findings belong upstream.
- [ ] Dev-only `external/` tooling no longer ships in the WordPress.org build.
- [ ] The Plugin Check job on this PR completes green.
