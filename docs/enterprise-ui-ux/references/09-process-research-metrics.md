# 09 Process, Research, Critique and Metrics

Good design is decided by evidence and judgement together. This file covers how senior designers run the work around the pixels.

## 1. Design process in a BMAD workflow

| Phase | Design activity | Output |
|---|---|---|
| Discover | Understand users, context, constraints, competitors, existing data | Problem statement, user goals, jobs to be done, assumptions list |
| Define | Prioritize problems, set success metrics, write principles | UX requirements in the PRD, success metrics, design principles |
| Structure | IA, flows, wireframes | Flow diagrams, sitemap, low fidelity screens |
| Design | High fidelity screens, components, states, copy | Screen specs, tokens, component specs |
| Validate | Usability tests, heuristic review, accessibility audit | Findings, severity, changes |
| Build | Handoff, implementation support, design QA | Built UI matching the spec |
| Measure | Instrument, analyze, iterate | Metrics review, backlog of improvements |

Work in small slices. Validate assumptions before committing to large builds. Record assumptions explicitly and mark them verified or unverified.

## 2. Problem framing

- Write the problem as a user need with context and a measurable outcome: "Small shop owners need to record a sale in under 20 seconds on a phone so they keep records during busy hours."
- List assumptions and rate them by risk and uncertainty. Test the riskiest first.
- Separate outcomes (what changes for the user or business) from outputs (features shipped).
- Use Jobs to be Done: "When [situation], I want to [motivation], so I can [outcome]."
- Personas are only useful when grounded in research and behavior. Prefer a short list of key user segments with goals, context and constraints.

## 3. Research methods

| Question | Method | Notes |
|---|---|---|
| What do people do and why? | Interviews, contextual inquiry, diary studies | 5 to 8 interviews per segment reveals most major themes |
| Can they use it? | Moderated usability testing | 5 participants per round finds most major issues, run several rounds |
| Which structure works? | Card sort, tree test | Validate navigation and labels |
| What do they prefer at scale? | Surveys, A/B tests | Needs adequate sample size and a clear hypothesis |
| What happens in real use? | Analytics, session recordings, support tickets, search logs | Combine quantitative with qualitative |
| Is it discoverable and clear? | First click test, five second test | Quick checks on hierarchy and labels |
| Competitors and norms | Competitive review | Learn conventions and gaps, do not copy blindly |

When research budget is tight (common for SME work): talk to 3 to 5 real users, watch them use a prototype or the existing product, check support chats and WhatsApp messages for repeated questions, and use analytics to find drop-off points. Record what was and was not validated.

## 4. Prototyping and fidelity

- Match fidelity to the question. Paper or low fidelity for structure and flow, interactive prototypes for behavior, coded prototypes for performance and feasibility.
- Test with realistic content and data volumes.
- Throw away prototypes that did their job. Do not let them become the product by accident.

## 5. Heuristic evaluation

1. Define scope (flow, user, device).
2. Evaluate against Nielsen's heuristics (file 02) and the accessibility checklist (file 03). Use at least two independent reviewers when possible.
3. Record each issue: location, heuristic violated, evidence, severity.
4. Severity scale: **Blocker** (prevents task completion or violates legal accessibility), **Major** (significant friction or error risk), **Minor** (annoyance), **Polish** (refinement).
5. Prioritize by severity, frequency and effort. Fix Blockers and Majors first.

## 6. Design critique

A critique improves the work, it does not judge the person.

- State the goals, users and constraints first. Critique against those, not personal taste.
- Describe what you see, then the effect on the user, then a suggestion. "The two buttons look equally prominent, so users may not know which is the main action. Consider making Save the primary and Cancel a text button."
- Separate observations, questions, and recommendations.
- Ask "what would have to be true for this to work?"
- Limit rounds. End with decisions and owners.

## 7. Prioritization

- Impact versus effort grid for quick triage. RICE (reach, impact, confidence, effort) or ICE for roadmaps. Kano for delight versus basics. MoSCoW for scope.
- Fix basics (errors, accessibility, performance, clarity) before adding delight.
- Reduce scope to the smallest slice that tests the hypothesis (MVP means minimum viable test, not minimum features).

## 8. Metrics

### Experience metrics
- **Task success rate**, **time on task**, **error rate**, **number of steps or clicks**, **completion funnel conversion**.
- **System Usability Scale (SUS):** 10 questions, score out of 100, about 68 is average, 80 or more is excellent.
- **Single Ease Question (SEQ)** after tasks. **NPS** and **CSAT** for sentiment, with caution on interpretation.
- **HEART framework:** Happiness, Engagement, Adoption, Retention, Task success. Choose goals, signals and metrics for each relevant area.

### Performance metrics
- Core Web Vitals: LCP, INP, CLS. Time to interactive, error rates, crash rates, API latency.

### Product metrics
- Activation rate, time to first value, retention cohorts, feature adoption, support contact rate, refund and churn reasons.

### Accessibility metrics
- Automated issue counts, manual audit pass rate, assistive technology task success, accessibility defects per release.

Rules:
- Define the metric and target before shipping. Instrument events with clear names, properties and owners. Respect privacy and consent.
- Pair quantitative data (what) with qualitative evidence (why).
- Beware vanity metrics, small samples and p-hacking.

## 9. Experimentation

- Hypothesis format: "We believe [change] for [users] will [outcome]. We will know when [metric] moves by [amount]."
- Test one major change at a time, run long enough to cover weekly cycles, predefine success and guardrail metrics.
- Do not A/B test accessibility or core safety. Do not ship dark patterns because they "won".

## 10. Design debt

- Track inconsistencies, accessibility gaps, outdated patterns and one-off components in a visible backlog with severity and impact.
- Allocate a regular share of capacity to paying it down.
- Audit periodically: screenshot inventory, token coverage, component usage, accessibility scan, copy consistency.

## 11. Design principles for a product

Write 3 to 5 product-specific principles that settle debates. Format: a short statement plus "so we will" and "so we will not". Example: "Fast over fancy. So we will cut steps and animations that delay entry. So we will not add decorative effects to core flows."
Store them in the design context.

## 12. Handoff and design QA

- A spec is ready when a dev can build it without asking questions about states, copy, responsiveness or accessibility.
- During build, answer questions quickly, update the spec, and record decisions.
- Design QA: compare built UI to the spec on real devices and browsers, check tokens, states, spacing, copy, accessibility and performance. Log defects with severity.
- Ship in stages, behind flags if needed, and monitor metrics and error logs after release.

## 13. Collaboration with stakeholders

- Show the problem and evidence before the solution.
- Present options with trade-offs and a recommendation.
- Translate design decisions into business terms (conversion, support cost, risk, speed).
- Keep a decision log.

## 14. Ethics and responsibility

- Consider who could be harmed or excluded. Test with diverse users.
- Be honest in design. No manipulation, no deceptive defaults, no hidden costs.
- Minimize data collection. Explain and protect what is collected.
- Plan for abuse, misuse and failure modes, especially in AI, financial and identity features.
