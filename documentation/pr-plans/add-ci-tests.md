# PR plan — add-ci-tests

## Goal
Give the repo its first automated test suite: CI jobs that fail when the shipped build regresses in the ways this stack of PRs just fixed for real, or when the plugin stops booting on a clean WordPress.

## Definition of Done
- [x] A packaging test runs in CI on every PR and fails when dev-only paths (external/, documentation/, tests/, .github/, Gruntfile...) leak into the WordPress.org build or shipped essentials go missing.
- [x] A direct-access sweep runs in CI and fails when any shipped own-code PHP file executed directly produces output or a fatal. The sweep excludes vendored code under `public/includes/lib/`, where 41 of 63 shipped PHP files still fatal on direct execution; those findings belong upstream, so this closes the defect class for own code only. Unguarded templates answered a direct request with partial markup, and leaked the server's absolute filesystem path only where `display_errors` is on; with the guards the same request returns nothing.
- [x] An end-to-end smoke boots a clean WordPress in CI, activates the built plugin, and verifies the da_image post type is registered and a published image's page renders with the plugin's assets.
- [x] Each local-runnable guard is demonstrated failing (mutation) before being trusted.
- [x] tests/ is excluded from the shipped build, and all new CI jobs are green on this PR.
