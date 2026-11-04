# enterprise-ui-ux

An installable UI and UX design skill for AI coding and design agents. It encodes the principles senior product designers use at large product companies, in a form agents can load on demand: visual foundations, interaction and cognitive principles, WCAG 2.2 AA, platform conventions, design systems and tokens, UX writing, data heavy patterns, AI product patterns, and low bandwidth Android first context.

Built for BMAD workflows: the UX designer agent uses it to spec, the dev agent uses it to build, and reviewers use it to gate.

## Install into a project

From the project root:

```bash
node /path/to/enterprise-ui-ux/install.mjs
```

Or pass a target:

```bash
node /path/to/enterprise-ui-ux/install.mjs ~/projects/my-app
```

Options: `--claude` (also mirror to `.claude/skills`), `--no-prompts`, `--no-agents-md`, `--no-context`, `--dry-run`, `--uninstall`.

What it does:

| Step | Result |
|---|---|
| Copies the skill | `.agents/skills/enterprise-ui-ux/` (the cross tool location) |
| Adds Copilot prompt files | `.github/prompts/enterprise-ui-ux-spec.prompt.md`, `-build`, `-review` |
| Adds the trigger block | A marked block in `AGENTS.md`, replaced in place on re-runs |
| Seeds the project context | `docs/design/design-context.md` from the template, never overwritten |

Re-running is safe. It updates the skill and the AGENTS.md block and leaves your design context alone.

## How agents use it

1. `AGENTS.md` tells every agent to follow the skill for any UI task.
2. The agent reads `SKILL.md`, picks a mode (SPEC, BUILD, REVIEW, AUDIT), reads `docs/design/design-context.md`, and loads only the reference files the task needs.
3. Before finishing, it runs the matching checklist.

## Layout

```
enterprise-ui-ux/
  SKILL.md                      router, modes, non-negotiables, BMAD mapping
  install.mjs                   installer
  references/
    01-visual-foundations.md
    02-interaction-and-ux.md
    03-accessibility.md
    04-platform-and-responsive.md
    05-design-systems-and-tokens.md
    06-ux-writing.md
    07-data-ui-patterns.md
    08-ai-and-emerging-market-context.md
    09-process-research-metrics.md
  checklists/
    ux-spec-checklist.md        UX agent, before handoff
    dev-implementation-dod.md   dev agent, before review
    design-review-rubric.md     reviewer, scoring and findings format
  templates/
    design-context.md           per project decisions, overrides generic defaults
    screen-spec.md
    agents-snippet.md
```

## Customizing per project

Edit `docs/design/design-context.md`. Brand colors, tokens, platforms, budgets, voice and decisions live there and override the generic defaults, except the non-negotiables in `SKILL.md`.

## Keeping it current

Standards move (WCAG, HIG, Material). Review the references twice a year. Treat the installed copy in `.agents/skills/` as generated: change the source skill and re-run the installer.
