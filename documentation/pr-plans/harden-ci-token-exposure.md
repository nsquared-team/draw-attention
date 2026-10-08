## Goal
Draw Attention maintainers get CI where installed packages and vendor actions can't pick up a credential that writes to this repo or acts as Otto.

Scope: one responsibility, each CI credential reaches only the steps that use it. In: `persist-credentials: false` on the Plugin Check checkout (its token keeps `pull-requests: write` for the comment) and on the release checkout, where the deploy key goes only to the two push steps; secrets set in workflow-level or job-level `env:` of a job that runs third-party code move to the steps that read them. ⏩ Out: jobs whose token is already read-only and hold no deploy key, jobs that run only this repo's own scripts or git, and the `ci.yml` auto-format push, which the read-only default token can't make today. Shape: the workflow files that carry either pattern, plus this plan; a small diff.
Risk: CI only, the plugin zip doesn't change. Undo is a revert. The load-bearing 1%: the release pushes, which only the next release run exercises; a mistake there fails the release at the push to `master`, before the tag and release are created.

## Definition of Done
- [ ] Every job that checks out the repo and then runs third-party code either has a read-only token and no deploy key left on disk, or sets `persist-credentials: false`.
- [ ] No secret is set in workflow-level or job-level `env:` of a job that runs third-party code; each one is set on the steps that read it.
- [ ] actionlint passes on every changed workflow.
- [ ] Plugin Check passes on this PR and still posts its comment.
