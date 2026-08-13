# PR plan — add-ci-tests

## Goal
Give the repo its first automated test suite: CI jobs that fail when the shipped build regresses in the ways this stack of PRs just fixed for real, or when the plugin stops booting on a clean WordPress.

## Definition of Done
- [ ] A packaging test runs in CI on every PR and fails when dev-only paths (external/, documentation/, tests/, .github/, Gruntfile...) leak into the WordPress.org build or shipped essentials go missing.
- [ ] A direct-access sweep runs in CI and fails when any shipped own-code PHP file executed directly produces output or a fatal (the unguarded-template class: master's templates leak fatal errors with server paths on direct HTTP access).
- [ ] An end-to-end smoke boots a clean WordPress in CI, activates the built plugin, and verifies the da_image post type is registered and a published image's page renders with the plugin's assets.
- [ ] Each local-runnable guard is demonstrated failing (mutation) before being trusted.
- [ ] tests/ is excluded from the shipped build, and all new CI jobs are green on this PR.
