## Goal
People reading Draw Attention's WordPress.org page see what changed in the 2.1 releases, which the changelog skipped.

Focus of this PR: WordPress.org lists the changes shipped in the 2.1 releases — the missed entries in `CHANGELOG.md` and `readme.txt` only, nothing else.

Scope: seven versions shipped customer-facing changes with no changelog entry, because their pull requests carried no changelog label. This PR writes those entries into `CHANGELOG.md` by hand and regenerates the readme's changelog section from it with the release job's readme step.
- 2.1.12 - 2026-11-26. Fixes: Show interactive images only to visitors allowed to see them. Features and Improvements: Tested up to WordPress 7.2.
- 2.1.9 - 2026-09-08. Fixes: Fix hotspot description editor going blank after drag-reordering hotspots.
- 2.1.8 - 2026-08-25. Fixes: Fix info-box heading clipping long words in Left/Right layout. Features and Improvements: Tested up to WordPress 7.1.
- 2.1.4 - 2026-04-14. Fixes: Fix checkbox styling broken by WordPress 7.0.
- 2.1.3 - 2026-03-24. Features and Improvements: Add alt text support for images to improve accessibility.
- 2.1.2 - 2026-02-17. Features and Improvements: Improve lightbox accessibility with focus trapping and ARIA attributes.
- 2.1.0 - 2026-02-05. Features and Improvements: Tested up to WordPress 7.0.

The info-box fix sits under 2.1.8 because 2.1.7 never reached WordPress.org. ⏩ Out: releases before 2.1.0, and the internal changes in 2.1.5, 2.1.10 and 2.1.11. Shape: about forty lines in `CHANGELOG.md`, the changelog section of `readme.txt`, plus this file.
Risk: text only, in two files; no code runs differently. The readme ships to WordPress.org on the next release. Undo: revert this PR. The 1%: the wording, which customers read as written.

## Definition of Done
- [ ] `CHANGELOG.md` lists 2.1.12, 2.1.9, 2.1.8, 2.1.4, 2.1.3, 2.1.2 and 2.1.0 with the lines in Scope, newest first.
- [ ] `readme.txt` is exactly what the release job's readme step writes from that `CHANGELOG.md`.
- [ ] `tests/packaging/check-build.sh` passes on this branch's WordPress.org file set.
