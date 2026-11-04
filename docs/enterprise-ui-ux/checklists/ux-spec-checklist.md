# UX Spec Checklist (SPEC mode, UX agent)

Run before handing any screen or flow spec to dev. Every unchecked item is either fixed or listed as an open question in the spec.

## Context
- [ ] `docs/design/design-context.md` read, and the spec follows it
- [ ] User goal, context of use (device, connectivity, urgency) and success metric stated
- [ ] Entry points, exit points and the previous and next screens defined

## Structure and hierarchy
- [ ] One primary action per view, secondary and tertiary actions ranked
- [ ] Content hierarchy described (what is read first, second, third)
- [ ] Reuses existing components and patterns, any new component justified in writing
- [ ] Navigation location and active state defined

## States and edge cases
- [ ] State matrix complete: default, loading, empty (first use and no results), error, partial, success, offline, disabled, permission denied
- [ ] Extreme data handled: zero, one, thousands, very long text, missing optional data, other scripts
- [ ] Failure paths designed: network loss, timeout, conflict, expired session, duplicate submit
- [ ] Destructive actions use undo or specific confirmation

## Interaction
- [ ] Behavior for tap, click, hover (with touch alternative), keyboard, and gestures (with visible alternative)
- [ ] Form fields: type, label, help text, validation timing, error messages, autofill attributes
- [ ] Feedback and timing: instant response, progress for waits, saved state
- [ ] Focus management defined for dialogs, route changes, dynamic content
- [ ] Motion purposes and durations stated, reduced motion alternative stated

## Responsive and platform
- [ ] Layout described at 320, 768, 1024 and 1440 (or the project's breakpoints)
- [ ] Touch target sizes and thumb reach considered
- [ ] Platform conventions respected (iOS, Android, web)
- [ ] Offline and slow network behavior defined where relevant

## Accessibility
- [ ] Contrast pairs checked, color never the only cue
- [ ] Heading outline, landmarks and reading order described
- [ ] Accessible names for icon-only controls, alt text decisions for images
- [ ] Error announcement and association approach defined
- [ ] Keyboard map for custom widgets

## Content
- [ ] Full copy deck with real strings for every state
- [ ] Terminology matches the project glossary
- [ ] Locale formats (currency, date, phone) specified
- [ ] Strings tolerate 30 to 40 percent growth

## Visual system
- [ ] All colors, type, spacing, radius, elevation reference tokens
- [ ] New tokens listed with reasons
- [ ] Light and dark themes both specified if supported

## Delivery
- [ ] Acceptance criteria written as testable statements (see DoD checklist)
- [ ] Analytics events and success measures listed
- [ ] Open questions and assumptions listed with owners
- [ ] Decisions appended to the design context decision log
