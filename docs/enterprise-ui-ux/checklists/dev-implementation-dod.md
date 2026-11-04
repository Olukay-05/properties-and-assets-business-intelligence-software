# Dev Implementation Definition of Done (BUILD mode, dev agent)

Self-check before marking any UI story ready for review. Copy the relevant items into the story's acceptance criteria.

## Fidelity to spec
- [ ] Built from the screen spec, deviations listed with reasons in the story notes
- [ ] Every state in the spec's state matrix is implemented and reachable (loading, empty, error, partial, success, offline, disabled, permission denied)
- [ ] Copy matches the copy deck, strings externalized
- [ ] Empty, one item, and many item cases verified with realistic data

## Design system and tokens
- [ ] Uses existing components first, extended or new components added to the system with docs
- [ ] No raw hex, pixel literal, font stack, shadow or z-index in components, tokens only
- [ ] Works in all supported themes (light, dark) with no hardcoded colors
- [ ] Spacing, type and radius follow the scales

## Accessibility (WCAG 2.2 AA)
- [ ] Semantic HTML (native elements, headings, landmarks, lists, tables, labels)
- [ ] Entire flow works by keyboard, visible focus, logical order, no traps
- [ ] Focus managed for dialogs, drawers, route changes and removed elements
- [ ] Accessible names, roles, states, live regions where content updates
- [ ] Form errors identified in text, associated with fields, announced, input preserved
- [ ] Contrast verified, color is never the only cue
- [ ] Touch targets 44px preferred and never below 24px, dragging has an alternative
- [ ] Reflows at 320px, 200 percent text size works, zoom not disabled
- [ ] `prefers-reduced-motion` respected
- [ ] axe or Lighthouse scan clean, manual keyboard pass done, screen reader spot check done

## Responsive and platform
- [ ] Checked at 320, 375, 768, 1024, 1440
- [ ] No unintended horizontal scroll
- [ ] On-screen keyboard does not hide inputs or primary actions, safe areas respected
- [ ] Correct input types, `inputmode`, `autocomplete` and `enterkeyhint`
- [ ] Back, refresh and deep link behavior correct, state kept in URL where specified

## Performance and resilience
- [ ] No layout shift (reserved space, image dimensions, font fallbacks)
- [ ] Images optimized and lazy loaded, route level code splitting, no oversized libraries added
- [ ] Within the project's performance budget, tested with throttled CPU and network
- [ ] Optimistic UI, retry and idempotency for writes, double submit prevented
- [ ] Offline behavior per spec

## Quality
- [ ] Component and interaction tests cover states and keyboard behavior
- [ ] Visual regression snapshot added for new or changed components
- [ ] Analytics events fire per spec, no sensitive data in events
- [ ] No console errors or warnings, lint passes including accessibility and token rules
- [ ] Security basics: output encoded, no secrets in client code, sensitive fields use proper input types and autocomplete

## Handoff
- [ ] Short note for the reviewer: what was built, deviations, how to test, known gaps
- [ ] Design context updated if new tokens, components or decisions were introduced
