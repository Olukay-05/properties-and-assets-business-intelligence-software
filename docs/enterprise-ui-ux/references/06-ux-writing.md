# 06 UX Writing

Interface copy is part of the interaction. Bad copy breaks good design.

## 1. Principles

1. **Clear:** one idea per sentence, common words, no jargon, no internal terms.
2. **Concise:** every word earns its place. Front-load the important word.
3. **Useful:** tells the user what to do or what happens next.
4. **Human:** speak like a helpful person, not a system or a brochure.
5. **Consistent:** same term for the same thing everywhere. Keep a glossary.
6. **Inclusive:** no idioms, slang or cultural assumptions that exclude. Neutral on gender, ability and age.

Reading level target: about grade 6 to 8 for general consumer products. Many Nigerian users read English as a second or third language, so avoid idioms and long compound sentences.

## 2. Voice and tone

- **Voice** is the brand personality, stable everywhere. Define it in three or four traits with do and don't examples (for example "Direct, warm, practical. Not playful about money.").
- **Tone** adapts to the moment. Celebrate small wins lightly. Be calm and plain in errors. Be serious around money, health, legal and security.
- Do not use humor in error states, payments, or when the user is stuck or worried.

## 3. Capitalization and punctuation

- Sentence case for buttons, headings, labels and menus.
- No trailing periods on buttons, labels, headings or single-sentence tooltips. Use periods in multi-sentence text.
- Use the Oxford comma consistently if the brand style says so, and apply the rule everywhere.
- Avoid exclamation marks except for real celebration, and then only one.
- Avoid ellipses except for "in progress" labels or truncated text.
- Do not use dashes as punctuation in running UI text. Use a period, colon or comma instead.
- Avoid all caps, except short abbreviations.

## 4. Buttons and links

- Verb plus object: "Save changes", "Send invoice", "Add item". Specific beats generic.
- Avoid "Submit", "OK", "Yes", "No", "Click here". Use the action name.
- Primary and secondary labels must differ in meaning clearly ("Save draft", "Publish").
- Destructive buttons name the action and object: "Delete project".
- Cancel and dismiss: "Cancel" for abandoning an action, "Close" for dismissing information, "Not now" for deferring.
- Links describe the destination: "View payment history".
- Loading labels: present participle ("Saving…" with a real ellipsis character, or "Saving"), then revert.

## 5. Labels, placeholders and help text

- Labels are short nouns or noun phrases, always visible.
- Placeholders show an example format, not instructions ("e.g. 0803 123 4567").
- Help text sits under the field and answers the question the user will have ("We will only use this to send receipts.").
- Mark optional fields "(optional)". Explain why you need sensitive data.
- Use the user's terms. Test labels with real users when unsure.

## 6. Error messages

Formula: **what happened**, **why** (if it helps), **what to do next**. Keep the user's data.

| Weak | Better |
|---|---|
| Invalid input | Enter a phone number with 11 digits, like 08031234567 |
| Error 500 | We could not save your changes. Check your connection and try again. Your edits are still here. |
| Payment failed | Your bank declined the payment. Try another card or pay by bank transfer. |
| Password invalid | Use at least 8 characters, with one number. |
| You entered the wrong email | We could not find an account with that email. Check the spelling or create an account. |

Rules:
- Never blame the user. Describe the system or the situation.
- Do not use technical codes alone. Add a short reference code at the end if support needs it.
- Be specific about the field and the fix. Place the message next to the problem.
- Distinguish user fixable errors from system errors. For system errors say we are working on it, offer retry, and provide an alternative.
- Do not use "Oops", "Whoops", or cute phrases for serious failures.

## 7. Empty states

Each empty state answers: what is this place, why is it empty, what can I do now.

- First use: "No invoices yet. Create your first invoice to get paid faster." with a primary button "Create invoice".
- No results: "No results for 'cement'. Try a different word or clear filters." with "Clear filters".
- Cleared: "You are all caught up." No action needed.
- Error or no access: explain and offer the path to access.

## 8. Confirmations, success and warnings

- Success: state what happened in the past tense, add the next step if useful. "Invoice sent to Ada. View invoice".
- Confirmation dialogs: title is the question or action, body names the object and consequence, buttons repeat the action and the safe choice.
  - Title: "Delete 'Q3 report'?"
  - Body: "This removes the report and its comments. You cannot undo this."
  - Buttons: "Delete report" and "Keep report".
- Warnings explain the risk and the choice. Do not warn about trivial things, warning fatigue is real.

## 9. Onboarding and instructional copy

- Lead with the benefit, then the step. One instruction per sentence. Number steps.
- Use tooltips for short clarifications only. Anything essential goes inline.
- Avoid "simply", "just", "easy". They shame users who struggle.

## 10. Notifications and emails

- Subject or title states the event and the object. Preview text adds value.
- Say who, what, when, and the one action. Do not bury the action.
- Respect frequency. Offer granular control. Transactional messages are factual and short.
- For SMS and WhatsApp, keep messages short, plain, and include the business name and a clear action.

## 11. Numbers, dates, currency, units

- Format by locale. For Nigeria: NGN with the ₦ symbol before the amount, comma thousands separators, two decimals when kobo matters ("₦12,500.00"), otherwise none ("₦12,500"). Use `Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' })`.
- Dates: unambiguous format. Prefer "3 Nov 2026" or locale format DD/MM/YYYY, never mix MM/DD and DD/MM. Use relative time ("5 minutes ago") for recent events with the exact time available on hover and for screen readers.
- Time: show the time zone when users span zones. Use 12 or 24 hour per locale.
- Phone numbers: accept local (0803...) and international (+234 803...) formats, normalize on save, display in a readable grouped format.
- Units and abbreviations: spell out on first use. Use consistent units.
- Percentages and large numbers: compact notation (12.4K, 3.2M) only when exact values are available elsewhere.

## 12. Inclusive and accessible writing

- Avoid ableist and violent metaphors ("blind spot", "kill the process" in user copy, "crazy").
- Avoid directional-only instructions ("click the button on the right"). Describe by name.
- Link text and button names must make sense alone.
- Write alt text and captions as part of the design.
- Use gender-neutral language and avoid assumptions about names, families and addresses.

## 13. Localization

- Never concatenate strings to build sentences. Use full-sentence templates with placeholders and ICU plural rules.
- Allow 30 to 40 percent extra text length for many languages. Avoid fixed width buttons.
- Keep copy in resource files, not in components, even if there is one language today.
- For Nigerian contexts, plan for English first with possible Pidgin, Yoruba, Igbo and Hausa. Do not machine translate without native review.

## 14. Copy deck format for specs

For each screen, list every string with an ID, location, text, state and notes:

| ID | Location | State | Text | Notes |
|---|---|---|---|---|
| inv.empty.title | Invoice list | Empty | No invoices yet | H2 |
| inv.empty.body | Invoice list | Empty | Create your first invoice to get paid faster. | |
| inv.empty.cta | Invoice list | Empty | Create invoice | Primary button |

## 15. Copy QA checklist

- [ ] Same term used for the same thing throughout
- [ ] Buttons are verb plus object, no "OK" or "Submit"
- [ ] Errors say what happened and how to fix it, without blame
- [ ] Every empty state has an explanation and a next step
- [ ] No jargon, no idioms, no filler
- [ ] Sentence case, no stray punctuation
- [ ] Numbers, dates, currency formatted for locale
- [ ] Strings externalized and length-tolerant
