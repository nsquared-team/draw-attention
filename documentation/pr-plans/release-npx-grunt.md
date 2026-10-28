## Goal
Whoever releases Draw Attention can run the release workflow on GitHub's Ubuntu 26.04 runner, which ships without Grunt.

Scope: `release.yml` calls Grunt directly in "Update version on php and readme files" (`grunt increment-version`) and "Package the App" (`grunt wporg`). That only works on the ubuntu-24.04 image, which installs Grunt globally. `ubuntu-latest` now runs ubuntu-26.04, which installs only `n` and `yarn`, so both steps would exit 127. Both now run through `npx`, using the `grunt-cli` in `package.json`, as `plugin-check.yml` and `tests.yml` already do. ⏩ Out: pinning runner images, re-running a release. Shape: two lines in `.github/workflows/release.yml`, plus this file.
Risk: only the release job runs these steps. If `npx` can't find Grunt, the job fails at the same step it would fail at today, and the existing Slack failure warning fires. Undo: revert this PR. The 1%: a PR can't run `release.yml`, so the changed lines first run for real at the next release.

## Definition of Done
- [x] No workflow step calls Grunt except through `npx`.
- [x] In a clean copy of this branch, on a machine with no global `grunt`, the version step's commands (after `npm version patch --no-git-tag-version`) end with `Done.` and write the new version into the plugin's PHP and readme files, and `npx grunt wporg` then builds `release/svn`.
