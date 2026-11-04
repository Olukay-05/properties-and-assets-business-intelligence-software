# 08 AI Product Patterns and Emerging Market Context

## Part A: AI feature UX

AI output is probabilistic, can be wrong, and varies run to run. The interface carries the burden of setting expectations and making errors cheap.

### Principles
1. **Set expectations early.** Say what the feature can do, its limits, and that output may be wrong. Show example prompts and good first actions.
2. **Keep the human in control.** AI suggests, the user decides. Preview before apply. Edit, regenerate, undo.
3. **Show your work where it matters.** Cite sources for factual claims, link to the underlying records, show which data was used. Display confidence only if it is calibrated and meaningful.
4. **Make errors recoverable and cheap.** Easy undo, version history, non-destructive suggestions, "report a problem" in context.
5. **Respect latency.** Stream text as it is generated. Show meaningful progress for multi-step work (what it is doing now). Allow stop. Never freeze the UI.
6. **Use the right interface.** Chat is not always right. Inline suggestions, buttons with pre-set prompts, forms that generate, and background automations often fit better than an open text box.
7. **Do not anthropomorphize carelessly.** Be clear it is AI. Avoid fake emotions or false claims of understanding.
8. **Protect privacy and trust.** Tell users what data is sent, stored and used for training. Offer controls. Avoid exposing one user's data to another.

### Patterns
| Pattern | Guidance |
|---|---|
| Prompt input | Suggested prompts, templates, context chips (attached files, selected records), clear send and stop controls, keyboard shortcuts |
| Streaming response | Render progressively, keep scroll stable, allow copy, stop, regenerate |
| Citations and sources | Inline markers linking to the source and passage, a sources list, and a note when no source supports a claim |
| Inline suggestion | Ghost text or highlighted diff, Tab or button to accept, Escape to dismiss, never auto-apply |
| Draft then review | Generated content lands as an editable draft with clear labels, a diff against the previous version, and approval before sending or publishing |
| Agentic actions | Show a plan, require approval for irreversible or external actions, log every action with timestamps, provide a kill switch and rollback |
| Feedback | Thumbs plus optional reason, and quick fix actions. Feed learning where possible |
| Empty and failure | Explain what to try, show fallbacks (manual path), handle refusals, rate limits, timeouts and model errors with plain language |
| Cost awareness | If usage costs money, show limits, remaining quota and estimated cost before large operations |
| Evaluation | Track acceptance rate, edit distance, task completion, correction rate, and harmful output reports |

### Copy for AI features
- "Suggested by AI. Check before sending." style labels where stakes are real.
- When uncertain: "I could not find this in your records." rather than guessing.
- Avoid "I think" and personality padding in transactional contexts.

## Part B: Low bandwidth, Android first, Nigerian market realities

These are defaults for products serving Nigerian SMEs and consumers. Replace with research data from the project when available.

### Context assumptions (validate per project)
- Mobile dominates. Many users are on mid-range or entry Android devices with limited RAM and storage, and cracked or low-brightness screens.
- Data is expensive and connections fluctuate. Users switch between Wi-Fi, 4G and weak signal, and may run out of data mid-task.
- Power cuts and low battery are common. Sessions are interrupted.
- WhatsApp is the primary business and customer communication channel for many SMEs. Email is less habitual than phone.
- Many users share devices, or use multiple SIMs.
- Trust is earned slowly. Scams are common, so users scrutinize payment and identity requests.

### Performance budgets (starting targets)
- Largest Contentful Paint under 2.5s on a throttled mid-range Android over slow 4G. Interaction to Next Paint under 200ms. Cumulative Layout Shift under 0.1.
- Initial JavaScript under about 170 KB gzipped for key pages. Total page weight under about 1 MB for core flows where possible.
- Images: AVIF or WebP, correct dimensions, lazy loading, blur or color placeholders. Avoid autoplaying video. Provide a lightweight mode.
- Fonts: one or two families, subset, preloaded, with `font-display: swap`. System fonts are acceptable and fast.
- Avoid heavy animation libraries on critical paths. If using GSAP or Lenis for marketing sites, load them after first paint, respect reduced motion, and disable heavy effects on low-power devices.

### Offline and flaky network design
- Offline-first where tasks matter: cache the app shell, store drafts locally, queue writes, sync in the background, show clear sync status and last synced time.
- Make every submission idempotent, safe to retry, and visible ("Pending", "Sent", "Failed. Tap to retry").
- Resume uploads. Compress before upload.
- Avoid requiring constant connectivity for reading previously loaded data.
- Offer data saver mode, text-first views, and the ability to download once and use later.

### Payments and money UX
- Support what people actually use: card, bank transfer, USSD, mobile money and wallet options, through providers like Paystack or Flutterwave. Show options without scrolling.
- Bank transfer needs special care: show a dedicated account, exact amount, expiry timer, copy buttons, and automatic confirmation. Explain pending and failed states and what to do if debited but not confirmed.
- Show currency clearly (₦), show fees up front, send receipts by SMS, WhatsApp or email, and keep transaction references visible and copyable.
- Provide a clear dispute and support path. Do not hide support.
- Double debit and timeouts must have clear recovery copy.

### Identity, address and forms
- Phone number is often the primary identifier. Accept 080..., 0803..., +234..., normalize. Support OTP over SMS with delays in mind, with a resend countdown and a voice or WhatsApp fallback when available.
- BVN and NIN collection is sensitive. Request only when legally needed, explain why, state how it is stored, and use verified providers.
- Addresses: Nigerian addresses often depend on landmarks. Offer free text, state and LGA pickers, landmark field, map pin where useful, and NIPOST digital postcode lookup for products built around it.
- Names: allow multiple given names, long names, and no assumption of surname order.

### Language and literacy
- Plain English first. Consider Nigerian Pidgin, Yoruba, Igbo and Hausa for consumer products, with native review. Use icons plus text, voice or audio help where literacy varies.
- Avoid idioms. Use numerals for amounts. Keep reading load low.

### Trust and credibility
- Show real business details: registered name, address, phone, WhatsApp, social proof with real names and consent.
- Explain data use plainly. Display secure cues only when backed by actual security.
- Use familiar logos for payment providers and banks (with permission). Avoid look-alike branding that resembles scams.
- Let users verify actions in a second channel (confirmation messages).

### Device and input realities
- Design for small screens (360 by 640 CSS px is still common), low brightness (higher contrast), large thumbs and one-handed use.
- Keep the APK or PWA small. Prefer PWA or lightweight native where possible. Handle memory pressure and app kills by restoring state.
- Respect multiple SIMs, intermittent notifications, and battery saver modes that may delay background work.

### Business and SME product patterns
- Many SME owners are not designers or analysts. Dashboards should answer "Am I making money, who owes me, what is low on stock" with plain numbers and clear next actions.
- Provide simple record keeping, invoices and receipts that can be shared via WhatsApp as images or PDFs.
- Allow multiple staff roles with limited permissions, and clear audit trails.
- Make onboarding fast. Import from spreadsheets or WhatsApp lists. Provide templates in local business contexts.

### Checklist
- [ ] Core flow tested on a throttled mid-range Android on slow 4G
- [ ] Works with intermittent connectivity, drafts saved, retries safe
- [ ] Payment states (pending, failed, double debit) all designed with recovery copy
- [ ] Phone, name and address inputs accept real Nigerian formats
- [ ] Page weight and JS within budget, fonts subset, images optimized
- [ ] Trust signals and support path visible
- [ ] Data cost considered: no autoplay, lightweight mode, compressed uploads
