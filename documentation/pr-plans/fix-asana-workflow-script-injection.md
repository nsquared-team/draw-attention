## Goal

Anyone who can comment on or review a pull request in this repo can no longer run commands on the CI runner through the Asana sync workflow, so the tokens that job holds stay out of reach of text a reviewer types.

## Definition of Done

- [ ] No `run:` block in `.github/workflows/asana.yaml` interpolates a `${{ }}` expression that carries user-controlled text; the sync action's outputs reach the script through `env:` and are referenced as quoted shell variables.
- [ ] The step's printed output is unchanged from before the fix.
- [ ] A comment on the step names why the values travel through `env:`, so a later tidying pass does not fold them back into the script.
- [ ] A review or comment body containing backticks or `$(...)` prints as literal text instead of executing.
