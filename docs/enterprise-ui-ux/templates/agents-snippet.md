## UI/UX standard (enterprise-ui-ux skill)

Any task that creates, changes, reviews or specifies something users see or touch must follow the `enterprise-ui-ux` skill.

Skill location: `.agents/skills/enterprise-ui-ux/SKILL.md`

Required steps for every UI task:
1. Read `.agents/skills/enterprise-ui-ux/SKILL.md` and follow its modes.
2. Read `docs/design/design-context.md` first. It overrides generic defaults. If it is missing, create it from `.agents/skills/enterprise-ui-ux/templates/design-context.md` before designing or building.
3. Load only the reference files that SKILL.md maps to the task.
4. Run the matching checklist before finishing:
   - UX designer agent: `checklists/ux-spec-checklist.md`
   - Dev agent: `checklists/dev-implementation-dod.md`
   - Reviewer or QA: `checklists/design-review-rubric.md`

Hard rules: WCAG 2.2 AA, all states designed and built, tokens instead of raw values, mobile first, real copy, no dark patterns.

BMAD mapping: UX design documents and screen specs are produced in SPEC mode. Stories that touch UI include the UI acceptance criteria from the dev definition of done. Reviews that find Blocker or Major issues fail the story.
