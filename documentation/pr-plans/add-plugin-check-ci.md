# PR plan — add-plugin-check-ci

## Goal
Give draw-attention the same WordPress.org compliance signal ssa-plugin and plugin-detective already have: a CI job that runs the official Plugin Check on every PR.

## Definition of Done
- [x] A Plugin Check workflow runs on every pull request, scanning the exact file set `grunt wporg` ships to WordPress.org (the built `release/svn` tree), not the repo root with its dev tooling.
- [x] The job goes red when Plugin Check reports errors, so a regression is visible on the PR itself.
- [x] Repo-only documentation (this plan file) is excluded from the shipped build so it is never scanned nor shipped.
