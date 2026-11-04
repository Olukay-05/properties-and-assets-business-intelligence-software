# Property & Asset Intelligence: MVP and Revenue Strategy

Cloud Crafter Technologies · Oct 30, 2026 · @Olukay Ogunnowo

## 1. Executive summary and recommended positioning

**Verdict: conditional go, with a different entry point from the one in the brief.** Build a narrow, validated product first. Do not build a postcode-led property platform.

**Why the brief's entry point is weak.** Property management software for Nigerian landlords and agents is already crowded and cheap (Porchplus, Ledge, Property360 and others), and a postcode is a field any of them can add. NIPOST's terms also bar derivative databases, bulk harvesting and resale, so the postcode cannot be a data moat. Published market size figures for the wider category conflict by a factor of nearly six and are not usable for sizing.

**Recommended entry point.** Sell a verified site and asset register with proof of visit to facilities management and maintenance contractors who look after 10 to 100 client sites. Their problem is concrete: proving that a technician attended the right location, and showing clients a trustworthy record. The postcode adds a verification step (format valid, assigned, matched to GPS), but the value is the register, the photo and GPS evidence, and client read-only links.

**Positioning.** Proof of visit and a trustworthy site register for multi-site maintenance teams, priced in naira by sites, not seats. Do not use NIPOST branding or call the product official.

**Commercial shape.** Four naira tiers from ₦20,000 to ₦150,000 a month plus an enterprise tier, with onboarding fees and paid digitisation. The illustrative year-one scenario is about ₦23.4m revenue and 40 customers by month 12, which is an assumption and not a forecast. At that level the business roughly covers a lean team and is not yet a standalone success, so services revenue must carry the first year.

**Technical shape.** Next.js, Supabase and PostGIS, as a mobile-first offline-capable web app. The NIPOST API is called server-side, optionally, and the product works fully without it.

**What happens next.** Run 12 or more discovery conversations and sell three to five paid pilots before building more than a prototype (sections 10 and 14). The next 90 days are laid out in section 11, from 2 November 2026 to 30 January 2027. The stop rule is firm: fewer than two pilot payments after 12 qualified conversations means pause or pivot.

**Biggest unknowns.** NIPOST commercial pricing and storage terms, the real size of the reachable FM pool, and whether buyers will pay ₦20,000 or more a month. Each has a test in this plan.

## 2. Target customer and buyer personas

Lead with facilities management and maintenance service companies that look after 10 to 100 client sites. Treat property management companies as a channel and a later segment, not the beachhead, because that market is already crowded and priced near zero.

I found no customer interview evidence, so the ranking below is analytical. It is the first hypothesis that section 10 must test, and the High, Medium and Low ratings are my judgement, not measured data.

| Segment | Operational pain | Willingness to pay | Accessibility | Competition | Verdict |
| --- | --- | --- | --- | --- | --- |
| Facilities management and maintenance contractors | High. They owe clients proof of every visit across many sites. | Medium to high. Client contracts can fund the tool (assumption). | Medium. Concentrated in Lagos, Abuja and Port Harcourt. | Medium. Mostly dollar priced CMMS tools (see section 8). | **Lead segment** |
| Multi-branch businesses with 5 to 50 sites (pharmacy chains, schools, microfinance banks, retail, fuel stations) | Medium to high. Branch condition and repairs are tracked on WhatsApp and spreadsheets. | Medium | Medium. Reachable through the FM firms that already serve them. | Low to medium. Generic multi-branch tools focus on stock and sales. | **Second wave** |
| Property management companies | Medium. Rent and service charge collection dominate the workflow. | Low to medium. Free tiers and ₦10,000 to ₦20,000 plans exist. | High | Very high. At least eight local products. | **Channel, not lead** |
| Real estate operators, developers, portfolio managers | Medium | High | Low. Long procurement cycles. | Medium. Listing portals are not management tools. | **Phase two enterprise** |
| Owners of distributed assets (generators, solar, towers, fleets) | High | Medium to high | Low to medium | Medium. CMMS vendors target them. | **A use case inside the lead segment** |

**Why property managers are the wrong first target.** Ledge lists a free tier for 5 units and paid plans from ₦5,000 to ₦50,000 a month ([Ledge](https://ledgeapp.co/)). Porchplus lists a free plan, a ₦15,000 monthly plan for 6 to 50 units, and already lists inspection booking with photo checklists, maintenance tracking and asset tracking ([Porchplus](https://www.porchplus.com/)). Property360 is free for the first property ([Property360](https://www.property360.africa/)). A postcode field is a one-week feature for any of them, so it is not a moat.

**Why facilities management firms look better.** Their product is the service visit, and their clients want evidence of it. One report describes an FM provider managing more than 50 MTN locations across Lagos, West and Northern Nigeria ([BusinessDay](https://businessday.ng/?p=98779)), the kind of multi-site contract where site-level proof matters. The established CMMS tools charge per user in dollars. UpKeep lists $24 and $55 per user per month ([Facilio summary of UpKeep pricing](https://facilio.com/blog/upkeep-pricing/)). At the official rate of about ₦1,332 per dollar ([CBN rate reports, October 2026](https://techeconomy.ng/dollar-to-naira-exchange-rate-today-october-12-2026)), that is roughly ₦32,000 and ₦73,000 per user per month. A 10 person maintenance team would pay ₦320,000 or more monthly, which a small Nigerian contractor is unlikely to accept.

**Sizing caution.** I did not find a credible count of Nigerian firms with 10 or more sites. Published facility management market sizes range from USD 2.5 billion ([Ken Research](https://www.kenresearch.com/nigeria-facility-management-and-outsourcing-market)) to USD 14.35 billion for 2024 ([BlueWeave](https://www.blueweaveconsulting.com/press-release/nigeria-facility-management-market-size-booming-to-reach-usd-24-72-billion-by-2031)), so they cannot size this product. Only 1,240,965 of Nigeria's 39.65 million MSMEs were small or medium enterprises in 2020 ([SMEDAN and NBS survey via The Guardian](https://guardian.ng/?p=1640106)), and most of those have one site. The realistic target pool is small, and discovery has to count it directly.

| Persona | Who they are | What they care about | Likely purchasing triggers (assumptions to test) |
| --- | --- | --- | --- |
| Economic buyer | Founder or managing director of an FM or maintenance firm with 10 to 80 staff | Keeping client contracts, margin, getting paid against service evidence | A client asks for monthly site reports. A visit is disputed. The firm passes about 20 sites. A tender asks for digital reporting. |
| Daily champion | Operations manager or head of maintenance | Dispatch, follow up, a clean report without a Monday of spreadsheet work | Staff turnover with records on personal phones. Reports built by hand each month. |
| Field users | Technicians and supervisors on mid-range Android phones | Few taps, works with weak data, no extra typing | Management mandate with a simple app. |
| Client viewer | Facility officer at the bank, school or estate that receives reports | Proof the work happened, one place to log a request | A read-only portal link in the contractor's report. This is the distribution loop. |
| Payer | Office admin or finance officer | Bank transfer or card, a receipt | Paystack checkout or direct debit. |

## 3. Evidence-backed problem analysis

The strongest commercial problem is not "where is the asset". It is "can I prove, to a client or owner, that a site was visited, inspected and fixed". Location knowledge matters, but mostly as a supporting fact for that proof.

**What the evidence does and does not show.** The independent evidence for these problems is thin. A CMMS vendor's Nigeria guide says teams still rely on spreadsheets, paper logs and WhatsApp updates, and ties maintenance cost to power instability and sites spread across Lagos, Port Harcourt and Kano ([Fogwing](https://www.fogwing.io/guides/best-cmms-software-in-nigeria/)). That is a vendor selling the category, so it supports plausibility, not severity. NIPOST cites estimated annual losses of N50bn to N80bn from misdelivery ([Built on Nigeria's Postcode](https://pitch.postcode.gov.ng/)), but that is a delivery problem, not an asset operations problem. Real severity and cost have to come from the interviews and pilot in section 10.

| Problem | Evidence found | Commercial strength | In the MVP? |
| --- | --- | --- | --- |
| Proof of visits, inspections and condition over time (records history) | Vendor claims about paper and WhatsApp records. No independent data. | Strongest. It ties to client retention and getting paid. | Yes, core |
| Coordinating technicians and agents | Competitors list tickets and vendor assignment ([Porchplus](https://www.porchplus.com/)). It is a commodity feature. | Strong, but not differentiating | Yes, basic assignment and status |
| Accurate site and asset register | Needed for everything else. No independent evidence of cost. | Medium alone, high as the foundation | Yes, core |
| Portfolio and client reports | A monthly report is a likely deliverable for contractors. This is an assumption to test. | Medium to strong. The buyer sees it directly. | Yes, one exportable report |
| Custodianship and responsibility | Low build cost, one field and a history. | Medium | Yes, minimal |
| Knowing precise location | The NIPOST postcode and a GPS pin cover most of it. | Medium. A postcode is a shared key for reports and other systems. | Yes, as verification layer |
| Maintenance schedules (preventive) | Core to CMMS products, but more complex to build and tune. | Strong | Phase two. MVP has recurring inspections only. |
| Finding neglected or underused assets | Needs utilisation data nobody has yet. | Weak at first | Phase three. MVP flags overdue inspections only. |

**One limit to design around.** NIPOST's terms say a postcode does not by itself establish ownership, title, occupancy or identity ([Terms](https://postcode.gov.ng/terms), clause 5.5). The product must never present a postcode as proof of title, and the registry should record custodianship as the customer's own assertion.

## 4. Product concept and scoped MVP

The product is a verified site and asset register with proof of visit. A contractor or multi-site operator registers every site and asset once, sends technicians to them, and gets back dated, geo-checked, photographed records it can hand to a client. I will call it the product here; naming is a later decision.

**Positioning line (to test with buyers):** know where every site and asset is, prove every visit, and report it by postcode.

**The core loop the MVP must make work**

1. Register a site: name, client, address description, GPS pin, optional postcode, site type, contact.
2. Register assets at the site: category, tag or QR code, serial, position in the site, status, custodian.
3. Create work: a recurring inspection from a checklist template, or an ad hoc maintenance request.
4. Assign it to a technician, who opens a mobile web page or installed web app.
5. Complete the visit: the app records distance from the site pin, the technician fills the checklist, adds photos and a condition rating, then closes the job or raises a work order.
6. Review and report: a supervisor approves, the client can open a read-only link, and one monthly report summarises visits and open issues.

**Design rules that keep it buildable by a lean team**

- One mobile-first web app, installable as a web app. No native apps in the MVP.
- Offline tolerance only where field work needs it: inspection submissions and job status changes queue on the phone and sync later. Everything else needs a connection.
- Pricing is by sites and assets, never by technician seat, so adding field staff does not raise the bill.
- The NIPOST postcode is optional and asynchronous. Registration never waits on it (section 7).
- A read-only client link is part of the first release. It is the product's distribution loop and the buyer's proof.

**Deliberately left out of the MVP**

- Rent collection, tenant management, listings and accounting. That is where Ledge, Porchplus and others already compete, and payments add compliance burden.
- Spare parts inventory, procurement, preventive maintenance by meter or runtime, and IoT sensors.
- Native mobile apps, route optimisation and AI features. AI-drafted report narratives are a sensible phase two addition because Cloud Crafter already works that way, but they are not needed to prove demand.

**Build effort (estimate, not measured).** With AI-assisted development on the stack in section 6, a founder-led team should reach a pilot-ready build in six to eight weeks if scope holds to the list above. The largest risks to that estimate are offline sync and the report layout, so build them as thin versions first.

## 5. Feature prioritisation matrix

Fourteen features make the MVP cut. Six are phase two, and four are long term. Effort is a rough size for a lean team: S is days, M is one to two weeks, L is more than two weeks.

| Feature | Priority | Effort | Why this priority |
| --- | --- | --- | --- |
| Site and facility registration | MVP | S | Foundation for everything else. |
| Geospatial mapping: GPS pin, map view, PostGIS distance checks | MVP | M | Powers proof of visit and location filters. |
| Postcode capture, offline format check, background level 1 verification | MVP | M | Differentiator, but optional so it cannot block use. |
| Asset registration and site-to-asset link | MVP | S | Core register. |
| Asset categories (fixed starter list) and status | MVP | S | Custom categories can wait. |
| Custodian and responsible person per asset, with history | MVP | S | Cheap to build, answers an explicit buyer question. |
| Inspections from checklist templates, photos, condition rating | MVP | M | The proof-of-visit core. |
| Maintenance requests and work orders (priority, status, close) | MVP | M | Needed to turn findings into action. |
| Technician and field agent assignment | MVP | S | Basic assignment only. |
| Map view, list filters and a simple operations dashboard | MVP | M | Location-based search is the visible benefit of the geo layer. |
| Activity history and one monthly client report (PDF) | MVP | M | The buyer's proof. Custom report builder is later. |
| Read-only client link | MVP | S | Distribution loop. |
| Offline queue for inspections and job status | MVP | M | Field reality. Keep it thin. |
| Subscription limits and Paystack billing | MVP | M | Pilots can be invoiced by hand, but billing must exist by launch. |
| Preventive maintenance scheduling beyond recurring inspections | Phase 2 | L | Strong demand in CMMS, but complex to tune. |
| Contractor directory, rates and service levels | Phase 2 | M | Wait until real contractor data exists. |
| NIPOST level 2 and above enrichment, business alias linking | Phase 2 | M | Depends on unpublished pricing and terms. |
| WhatsApp notifications through an approved provider | Phase 2 | M | Use click-to-share links in the MVP. WhatsApp API needs provider onboarding. |
| AI-drafted report narrative and photo condition notes | Phase 2 | M | Useful, not needed to prove demand. |
| API, accounting export and webhooks | Phase 2 | M | Paid custom integrations come first. |
| Portfolio analytics and utilisation reporting | Long term | L | Needs months of clean data. |
| Site selection and geographic expansion intelligence | Long term | L | Needs many customers and a data licence conversation. |
| Sensor and IoT integration | Long term | L | Hardware cost and support burden. |
| Native mobile apps | Long term | L | Revisit if the web app limits field adoption. |

**The cut line.** If the build slips, drop in this order: the offline queue (fall back to retry on reconnect), the operations dashboard (keep the list filters), and Paystack billing (invoice pilots by hand). Do not drop proof of visit, the monthly report or the client link, because those three are the reason a contractor pays.

## 6. Data architecture and system design

Use one PostgreSQL database with a shared schema, an organisation\_id on every tenant row, and row level security as the isolation layer. The proposed stack is sound for this product. The main changes are to keep the NIPOST API server-side only, and to design for hosting that may have to move.

| Layer | Choice | Notes |
| --- | --- | --- |
| App | Next.js and TypeScript on Vercel | Installable web app. An IndexedDB queue (for example with Dexie) holds offline inspections. |
| Database and auth | Supabase PostgreSQL, Supabase Auth | Row level security, storage policies, scheduled jobs. Keep schema in plain SQL migrations so the database can move. |
| Geospatial | PostGIS, geography(Point, 4326) with GIST indexes | Radius search, nearest site, point-in-polygon by LGA, distance checks on arrival. |
| Maps | MapLibre GL JS with vector tiles from a provider or self-hosted PMTiles | I did not verify tile pricing or imagery licences. Do not reuse imagery you have no right to. A technician's device GPS pin matters more than the basemap. |
| NIPOST | Server-side calls only, through a queue with rate limiting and a circuit breaker | API key never reaches the browser. Details in section 7. |
| Payments | Paystack subscriptions and webhooks | Local card fee is 1.5% plus ₦100, capped at ₦2,000 ([Paystack](https://paystack.com/pricing)). |
| Files | Supabase Storage, photos resized on the phone | Resize to about 1,600 pixels before upload to survive weak data. |
| Monitoring | Error tracking and uptime checks from day one | Pilot customers judge reliability, not features. |

**Core entities.** Every row below also carries organisation\_id, created\_at, updated\_at and created\_by unless noted.

| Entity | Essential fields | Relationships |
| --- | --- | --- |
| organisations | name, plan, status, country | Parent of everything |
| users, memberships | user id, role (owner, admin, supervisor, technician, client viewer), status | A user can belong to several organisations |
| clients | name, contact | An organisation serves many clients. Sites belong to a client. |
| sites (properties and facilities) | name, client, site type, address description, landmark note, current location, postcode string, postcode status, status | Has zones, assets, inspections, work orders |
| locations | geom (point), accuracy in metres, source (device GPS, map pin, imported), captured by, captured at | History of coordinates. A site points to its current one. |
| site\_zones | site, name, type (floor, room, plant room) | Gives assets a position inside a site |
| asset\_categories | name, parent, default checklist | System defaults plus organisation additions |
| assets | site, zone, category, tag or QR code, name, make, model, serial, status, condition rating, installed on, warranty until, current custodian | Belongs to one site. Has custody history, inspections, work orders. |
| asset\_custody | asset, custodian, assigned by, start, end, note | One row per handover, so responsibility has history |
| inspection\_templates, inspection\_schedules | checklist items, interval in days, next due, assignee | A schedule creates inspections |
| inspections | site, asset (optional), inspector, started and completed time, arrival location, distance to site pin, responses, overall condition, review status | Can raise work orders |
| work\_orders, work\_order\_events | title, priority, status, assignee (user or contractor), due, source inspection, cost estimate and actual | Events hold status changes and comments |
| contractors | name, trade, phone, status | Assignable to work orders |
| attachments | entity type and id, storage path, captured time and location | Photos for inspections and work orders |
| postcode\_verifications | site, postcode, format valid, API level, result, distance to pin, requested at, responded at, request id, expiry | Many per site. Stores outcomes, not a copy of NIPOST data. |
| client\_share\_links | scope (client or site), hashed token, expiry | Read-only access with no database login |
| audit\_logs | actor, action, entity, before, after, time, source | Append only |
| plans, subscriptions, usage\_records | limits, Paystack customer and subscription codes, period end, metric, value | Drive limits and billing |

**Multi-tenancy and permissions.** A row level security policy checks that the row's organisation\_id is one the signed-in user belongs to, then checks role. Technicians see only jobs assigned to them and the sites those jobs belong to. Client viewers never get a database login: they open a signed link that a server route turns into a scoped read. Storage paths start with the organisation id and carry matching policies. The service key lives only on the server, for the NIPOST calls, Paystack webhooks and report generation.

**Auditability.** A database trigger writes to the append-only audit table on every insert, update and delete of core records. Approved inspections are immutable. A correction creates a new version that links to the original. This is what makes the records usable as evidence in a dispute.

**Privacy and compliance.** The product holds personal data: technician and custodian names, phone numbers, photos and sometimes owner names. Under the Nigeria Data Protection Act 2023 and the 2025 implementation directive, controllers and processors of major importance must register with the regulator, appoint a data protection officer and file annual compliance audit returns ([TNP summary](https://tnp.com.ng/understanding-the-compliance-obligations-of-data-controllers-and-processors-under-the-nigeria-data-protection-act/)). The qualifying thresholds have been unclear ([PwC Nigeria](https://pwc.com/ng/en/assets/pdf/regulatory-alert-august-2023.pdf)), so get a data protection compliance organisation to confirm whether Cloud Crafter qualifies before launch. Treat customers as controllers and Cloud Crafter as their processor, and sign a data processing agreement with each. Do not store NIPOST level 2 and above personal fields unless a customer record needs them.

**Hosting risk.** NIPOST's own platform drew criticism for storing data outside Nigeria. The National Digital Cloud Policy approved on 17 August 2026 has sovereignty provisions that still await presidential approval ([Space in Africa](https://spaceinafrica.com/2026/10/03/inside-nigerias-digital-postcode-the-gis-and-satellite-data-behind-11-characters/)). I am not aware of a Nigerian region at Supabase or Vercel, and you should confirm current region lists. Choose the nearest region, keep the database standard Postgres, and keep a documented path to move to Nigerian hosting if a large customer or the policy requires it.

## 7. Official NIPOST postcode integration and licensing

The postcode is usable as a validation and enrichment layer, not as the data foundation of the product. The API exists and has a free validation tier, but commercial pricing is unpublished and NIPOST's terms forbid building a derivative database from its data.

| Question | What official sources say | Evidence |
| --- | --- | --- |
| Format | 11 characters in five segments: state (2 letters), LGA (2 digits), district (3 characters), area (2 letters), building (2 digits). Example: FC 02 A09 DB 09. The code belongs to the building, not its occupants. | Verified: [NIPOST postcode site](https://postcode.gov.ng/), [FAQ](https://postcode.gov.ng/faq), [Space in Africa](https://spaceinafrica.com/2026/10/03/inside-nigerias-digital-postcode-the-gis-and-satellite-data-behind-11-characters/) |
| Access route | Create an organisation account on the NIPOST dashboard, or submit the integration form (organisation type, expected volume, API, SDK, bulk or government corridor). | Verified: [postcode.gov.ng](https://postcode.gov.ng/), [integration form](https://postcode.gov.ng/integration) |
| Development access | Free through the NIPOST developer dashboard while building. | Verified: [Built on Nigeria's Postcode](https://pitch.postcode.gov.ng/) |
| Commercial pricing | Not published. NIPOST and the Ministry will confirm it later. Terms say fees are quoted in naira under a service agreement. | Verified as unpublished: [pitch site](https://pitch.postcode.gov.ng/), [Terms](https://postcode.gov.ng/terms) clause 10 |
| Lookup levels | Level 1 confirms a code is assigned and is free. Levels 2 and above return address, building use and more, and consume paid credits. | Secondary: [NaijaTechGuide](https://www.naijatechguide.com/nigeria-digital-alphanumeric-postcode.html), [community client library](https://pypi.org/project/ng-postcode/0.3.4/) |
| Live keys and limits | One developer write-up reports a KYB check for live keys, per-key rate limits (HTTP 429 on excess) and a restricted top level. I could not open NIPOST's own docs site, so treat this as unconfirmed. | Unverified: [nigeriapostcodes.com summary](https://nigeriapostcodes.com/new-postcode/postcode-api-for-developers/) |
| SDKs | Web and mobile widgets (Flutter, Android, iOS, React Native) and bulk dataset access. Unofficial MIT client libraries exist for Python, Rust, JavaScript and Java. | Secondary: [NaijaTechGuide](https://www.naijatechguide.com/nigeria-digital-alphanumeric-postcode.html), [ng-postcode](https://pypi.org/project/ng-postcode/0.3.4/) |
| Approval timeline | Not published anywhere I could find. | Open question |

**Rules that bind Cloud Crafter.** The [Acceptable Use Policy](https://postcode.gov.ng/acceptable-use) prohibits retaining NDAPS data beyond the authorised period, systematically extracting or compiling substantial portions of the database, reselling or sublicensing API access, and using the API to build a service that materially replicates NDAPS. The [Terms](https://postcode.gov.ng/terms) bar derivative databases (clause 7.3), require deletion of NDAPS data on termination if asked (16.4), and say a postcode does not by itself establish ownership, title or occupancy (5.5). Enterprise users may have to sign a data processing agreement (9.4). I found no published caching clause, so the table below is a conservative design position, not a reading of NIPOST's permission.

| Action | Position | Basis |
| --- | --- | --- |
| Store the postcode string a customer enters for its own property | Allowed in principle. It is the customer's record, not a copy of the NDAPS database. | Assumption, confirm in writing |
| Store the verification result (status, level, timestamp) | Allowed in principle. Keep it as an audit fact. | Assumption, confirm in writing |
| Cache API responses | Short expiry only, for example 24 hours, until the service agreement states a term. | Assumption |
| Persist level 2 and above address details | Only where the customer record needs them. Treat as personal data under the Nigeria Data Protection Act. | Terms 9.3 |
| Show the postcode inside a customer's own workspace | Allowed. Never publish a searchable directory of other people's buildings. | AUP 3.3, 3.4 |
| Bulk lookups to pre-seed the database, crawling the finder, mirroring | Prohibited. Do not do it. | AUP 3.8 |

**How postcode, GPS, address and field checks fit together.** NIPOST's own records can be wrong or unverified, and the public lookup returns a point, not a building outline. So the postcode never overrides the customer's own evidence. Each property carries four independent facts: the postcode string, a GPS pin captured on site, a written address description, and a field confirmation. A verification ladder then records how much is proven:

1. Entered: typed by a user, not checked.
2. Format valid: passes the offline segment check. This proves shape only, not assignment.
3. Assigned: the free level 1 lookup confirms the code exists.
4. Location matched: the agent's GPS pin falls within a set distance of NIPOST's point. The threshold (50 metres in dense urban areas is a starting guess) needs testing on real buildings.
5. Field confirmed: an agent on site confirms the building, with a photo.

A mismatch creates a review task. It never silently rewrites the customer's coordinates.

**Working without the API.** The postcode is optional enrichment. Registration must never block on it. Format validation runs offline, failed lookups queue for retry with backoff, and the record shows "pending verification". The product is fully usable on GPS pin plus address description alone.

**Actions this week.** Register an organisation account, submit the integration form, and send NIPOST a written list of questions: caching and retention terms, storing level 2 and above fields, whether a property management SaaS counts as an authorised commercial use, credit pricing, uptime commitments, and the production approval timeline. NIPOST names "proptech companies" and property inspections as a target use case on its [programme page](https://pitch.postcode.gov.ng/), which helps the request. That page also lists a showcase session on 21 November, with applications open at its 7 October publication. Confirm the current status before planning around it.

## 8. Competitive landscape and differentiation

Nigerian property management software is crowded and mostly built around rent collection, while the maintenance tools that fit facilities work are foreign and priced per user in dollars. The gap is narrow, specific and unproven. A postcode alone does not defend it.

| Competitor | Type and target | Relevant features (from their own sites) | Public pricing |
| --- | --- | --- | --- |
| [Porchplus](https://www.porchplus.com/) | Nigerian and West African landlords and property managers | Rent, leases, maintenance tickets, inspection booking with photo checklists, asset tracking per unit, task assignment, role access | Free to 5 units, ₦15,000 a month for 6 to 50 units, enterprise custom with API |
| [Ledge](https://ledgeapp.co/) | Lagos property managers, agencies and gated estates | Rent on Paystack, Lagos lease notices, tenant portal with maintenance tickets and photo proof, owner statements, estate service charge and gate passes | Free to 5 units, then ₦5,000 to ₦50,000 a month in five tiers. Every feature free until 2 February 2027 for sign-ups. |
| [OurProperty NG](https://ourproperty.ng/) | Nigerian agents, landlords, property managers | Rent, invoices, tenants, maintenance | A third-party listing shows free, ₦15,000 and ₦99,000 a month ([SoftwareFinder](https://softwarefinder.com/property-management-software/ourpropertyng)). Listing date unknown. |
| [Property360](https://www.property360.africa/) | Landlords, tenants and agents | Leases, e-signing, payments | Free for the first property |
| [Estate Vault](https://www.estatevault.net/), [Hutstack](https://www.hutstack.com/), [PropertyREM](https://propertyrem.ng/) | Landlords and property managers | Property, tenant and lease records. PropertyREM lists maintenance with artisan bidding. | Not found |
| Estate platforms such as VENCO and Vendr.ng | Gated communities and estate managers | Service charge, utility vending, visitor access ([Techloy](https://www.techloy.com/nigerian-proptech-venco-bags-670k-pre-seed-to.md)) | Not found |
| Foreign CMMS tools (UpKeep, MaintainX, Fiix, Fogwing and others) | Maintenance teams, mostly industrial and facilities | Work orders, preventive maintenance, assets, mobile apps | UpKeep lists $24 and $55 per user per month ([Facilio](https://facilio.com/blog/upkeep-pricing/)). Other sources list different starting prices, so confirm on each vendor's page. |
| Spreadsheets and WhatsApp groups | Everyone | Free and familiar | Staff time |

The five CMMS tools named in [Fogwing's Nigeria guide](https://www.fogwing.io/guides/best-cmms-software-in-nigeria/) are Fogwing, ManWinWin, Tractian, Daramyx and Fiix. The guide is written by one of them and I did not check where each is based, so read it as a list of what shows up in the market, not an independent ranking.

**What is verified.** Local tools are rent-led and cheap, and Porchplus already lists inspections and asset tracking, so "inspections and assets" is not a gap for landlords. The CMMS tools that cover facilities work are priced per user in dollars. I found no postcode-enabled property or asset product, only developer libraries ([ng-postcode](https://pypi.org/project/ng-postcode/0.3.4/)). NIPOST itself pitches property and field service use cases and shows its app verifying assets with a postcode ([NIPOST](https://postcode.gov.ng/)), so it can move toward this space.

**What is not verified.** Whether FM contractors feel the proof-of-visit problem strongly enough to pay, whether the foreign tools fail them on price or on fit, and how many such firms exist. The postcode launched four weeks ago, so absence of competitors is weak evidence and fast followers are likely.

| Differentiator | Real or claimed | Defensibility |
| --- | --- | --- |
| Priced in naira by sites and assets, with free technician seats | Real, if confirmed in pilots | Medium. A rival can copy pricing, but per-user vendors would have to restructure. |
| Proof of visit: distance to site pin, photos, timestamp, supervisor approval | Real | Low to medium alone. It becomes stronger with accumulated records and client links. |
| Postcode verification ladder and reports keyed by postcode | Real | Low. Any developer can integrate the same API. |
| Read-only client links | Real | Medium. Each report sent to a client is a distribution event. |
| Built for Nigerian field conditions (offline tolerance, WhatsApp sharing, weak data) | Real if executed | Medium. It rests on execution and local support. |
| "Official", "first" or "NIPOST partner" | Do not claim | NIPOST's [acceptable use policy](https://postcode.gov.ng/acceptable-use) bars using its branding without written permission and bars impersonation. |

The honest conclusion is that the defensible asset is the accumulated, verified site and visit history and the client relationships around it, not the postcode integration. Plan to win on fit and speed of onboarding for a narrow segment.

## 9. Pricing hypotheses and revenue projections

At the proposed prices, 40 customers by month 12 produce about ₦23.4 million in year-one revenue (roughly $17,600 at ₦1,332 per dollar) and a ₦29.8 million annual run rate. That is a credible product line for Cloud Crafter, but not yet a sustainable standalone business. Every number below is an assumption to test in pilots, not market data.

**Recurring tiers (monthly, naira).** Limits count sites and assets. Technician accounts are free so adding field staff never raises the bill.

| Tier | Price a month | Sites | Assets | Office users | Includes |
| --- | --- | --- | --- | --- | --- |
| Starter | ₦20,000 | 10 | 150 | 3 | Register, inspections, work orders, monthly report |
| Growth | ₦60,000 | 40 | 600 | 10 | Client read-only links, postcode verification, custom checklists |
| Business | ₦150,000 | 120 | 2,000 | 30 | Branded reports, portfolio dashboard, export, priority support |
| Enterprise | From ₦350,000, quoted | 300 and above | Custom | Custom | Integrations, data processing agreement, service levels |

Extra sites cost ₦1,500 each on Growth. Annual prepayment gets two months free (10 months billed). The price per site falls from ₦2,000 on Starter to ₦1,250 on Business. For comparison, a 10 person team on UpKeep's $24 per user plan would pay about ₦319,680 a month at the official rate ([Facilio](https://facilio.com/blog/upkeep-pricing/)), while Porchplus charges ₦15,000 for up to 50 residential units ([Porchplus](https://www.porchplus.com/)). The pricing sits between the two, which is the hypothesis: contractors will pay more than landlords for proof of service and less than dollar CMMS prices.

**One-time and service revenue**

| Stream | Price hypothesis | Cost and margin notes |
| --- | --- | --- |
| Paid pilot | ₦75,000 flat for 90 days, up to 15 sites, includes onboarding | Founder time. Its job is validation, not margin. |
| Onboarding | Starter ₦50,000, Growth ₦150,000, Business ₦350,000 | Setup, training, checklist templates. Mostly founder time. |
| Register digitisation (agent visits, GPS pin, photos, asset tags) | ₦15,000 per site | Estimated cost about ₦10,200 per site (agent at ₦20,000 a day, 3 sites a day, transport, tags). Margin about 32%. Agent cost is an assumption. |
| Data migration from spreadsheets | Quoted, ₦250,000 average assumed | Mostly cleaning and import scripts. |
| Custom integrations and workflow automation | ₦300,000 to ₦1,500,000 a project | Accounting export, WhatsApp provider setup, client systems. |
| Advanced reporting and portfolio analytics | In Business tier. Later a ₦30,000 monthly add-on. | Not modelled, which keeps the projection conservative. |

**Cost and margin assumptions.** Platform costs of ₦250,000 a month at 12 customers and ₦300,000 at 40 (hosting, database, storage, maps, monitoring) are my estimates. I did not verify vendor prices. Paystack's local fee is 1.5% plus ₦100, capped at ₦2,000 ([Paystack](https://paystack.com/pricing)), about ₦960 a month on the blended plan mix. Support and storage are assumed at ₦7,000 a customer a month. NIPOST credit costs are unknown and excluded, because lookups run once per site and are cached.

**Illustrative acquisition scenario.** Plan mix is 40% Starter, 40% Growth, 20% Business, giving ₦62,000 average monthly revenue per customer. No paying customers before month 4, because the first three months cover discovery, build and pilots.

| Month | Paying customers | Recurring revenue (₦) | Recurring gross margin |
| --- | --- | --- | --- |
| 4 | 5 | 310,000 | Not meaningful, below break-even |
| 6 | 12 | 744,000 | About 54% |
| 8 | 20 | 1,240,000 | About 65% (estimate) |
| 10 | 30 | 1,860,000 | About 70% (estimate) |
| 12 | 40 | 2,480,000 | About 75% |

| Year-one revenue stream | Amount (₦) |
| --- | --- |
| Recurring subscriptions, months 4 to 12 | 11,842,000 |
| Onboarding (37 customers at ₦150,000) | 5,550,000 |
| Pilot fees (3 at ₦75,000) | 225,000 |
| Digitisation and migration (16 customers at ₦250,000) | 4,000,000 |
| Custom integrations (3 projects at ₦600,000) | 1,800,000 |
| **Total** | **23,417,000** |

The margin figures for months 8 and 10 interpolate between the computed month 6 and month 12 values. Break-even on recurring revenue comes at about 10 customers against ₦600,000 of monthly fixed costs (infrastructure, tools, part-time support), excluding the founder's pay.

**The critical reading.** At ₦29.8 million annual run rate the product does not replace a services business. Reaching ₦3 to ₦5 million monthly recurring revenue needs roughly 50 to 80 customers at this average, or a higher average from Business and Enterprise accounts. Services revenue (onboarding, digitisation) is 50% of year one, which is healthy for cash but should shrink as a share over time. Decide in discovery whether the realistic customer pool supports that count.

**Securing the first customers without advertising**

1. Start with five to eight discovery conversations with FM and maintenance firm owners from your own network and LinkedIn, offering the paid pilot at the end of each call.
2. Offer the digitisation service first. A contractor that pays to have 15 sites registered and photographed has already committed to the register.
3. Send every pilot's monthly report with a client read-only link. The client contact who opens it is the next lead.
4. Ask each pilot for two introductions to firms they work beside, such as subcontractors or competitors in other cities.
5. Use the NIPOST programme as a credibility channel: the integration team's [form](https://postcode.gov.ng/integration) and its showcase series put builders in front of organisations that may adopt them ([Built on Nigeria's Postcode](https://pitch.postcode.gov.ng/)). Confirm current application status first.
6. Cross-sell to existing Cloud Crafter SME clients that run several branches.

## 10. Customer discovery and paid pilot strategy

Validate before building more than a clickable prototype. Run 12 to 15 discovery conversations in three weeks, and close eight of them with a paid pilot offer. The test that matters is whether anyone pays, not whether they say the idea is good.

**Who to talk to**

| Group | Target count | Where to find them |
| --- | --- | --- |
| Owners or operations heads of FM and maintenance firms with 10 to 100 sites | 8 | Own network, LinkedIn, referrals from existing Cloud Crafter clients |
| Multi-branch operators with 5 to 50 sites (pharmacy, school, microfinance, retail) | 3 | Existing clients and their peers |
| Property management companies with commercial portfolios | 2 | Own network. Used to test the channel hypothesis. |
| Client-side facility officers at banks, schools or estates | 2 | Introductions from the contractors |

**Discovery questions.** Ask about the last time something happened, not about the future. Do not describe the product until the last ten minutes.

| Area | Questions |
| --- | --- |
| Current process | Walk me through the last site visit from request to report. Who did what, in which tool? How many sites and assets do you look after today? |
| Tools and workarounds | Show me the spreadsheet, form or WhatsApp group you use. Who maintains it? What happens when that person leaves? |
| Frequency and cost | How many visits were disputed or repeated last quarter? How many hours does the monthly client report take? Has a contract been lost or a payment delayed over missing evidence? |
| Coordination | How do technicians learn about a job? How do you know they arrived? How do you chase an overdue job? |
| Records | When did you last need an old inspection record, and how long did it take to find? |
| Willingness to pay | What do you pay today for any software or tracking? Who approves a spend of ₦60,000 a month? What would make it an easy yes? |
| Adoption barriers | Which phones do your technicians use? How is data coverage at your sites? Would they use an app? What would your clients need to see? |
| Integrations | What accounting, WhatsApp or client portals must it work with? |
| Commitment test | Would you pay ₦75,000 for a 90 day pilot on 15 of your sites starting in January? If not, what is missing? |

**Paid pilot design.** Price it at ₦75,000 for 90 days and up to 15 sites, with onboarding and one round of digitisation included. Measure a baseline in the first week from the customer's own records, and again at day 90.

| Indicator | Baseline source | Pilot target (hypothesis) |
| --- | --- | --- |
| Hours to produce the monthly client report | Customer estimate and a timed run | Cut by at least 50% |
| Visits with verified arrival and photo evidence | Count from existing records | At least 90% of pilot visits |
| Share of pilot assets with a known, verified location | Spot audit of 20 assets | At least 95% |
| Disputed or repeated visits | Customer log | Fewer than at baseline |
| Time to find a past inspection record | Timed test | Under one minute |
| Technician adoption (jobs closed in the app) | None | At least 80% by week six |
| Willingness to continue at list price | Interview at day 75 | Signed order for a paid tier |

**Evidence thresholds**

| Decision | Evidence required |
| --- | --- |
| Proceed | At least 5 of 12 discovery targets name the problem unprompted and describe a recent cost. At least 3 pay a pilot fee. At least 2 of those give a written intent to subscribe at ₦40,000 a month or more. |
| Change direction | Interest is strong but concentrated in another segment (for example property managers or multi-branch operators), or buyers want rent collection or accounting first. Re-scope the MVP for that segment and repeat the pilot test. |
| Abandon or pause | Fewer than 2 pilot payments after 12 qualified conversations, or buyers cite price below ₦20,000 a month as the only workable level. Cap further spend on the product and keep the work as a custom project offer. |

These thresholds are my starting point. Set final numbers before the first call so the results cannot be argued after the fact.

## 11. 90-day execution roadmap

The plan runs from Monday 2 November 2026 to Friday 30 January 2027, with phases that overlap on purpose. It assumes one founder plus AI-assisted development. The launch must not depend on NIPOST production approval, because NIPOST has published no approval timeline. Treat anything from 4 to 8 weeks as plausible (my assumption) and design the pilots to run on the free level 1 lookup, offline format checks and GPS alone.

| Phase | Days and dates | Deliverables | Dependencies | Main risks | Exit criteria |
| --- | --- | --- | --- | --- | --- |
| 1. Research, discovery, feasibility | Days 1 to 21 (2 to 22 Nov) | 12 to 15 discovery calls logged. Organisation account and integration form submitted to NIPOST on day 1. Written NIPOST questions sent (section 7). Clickable prototype of inspection and report flow. Technical spike: PostGIS distance check, offline queue, NIPOST level 1 call in the sandbox. | Founder network access. NIPOST dashboard sign-up. | Calls reveal no pain. NIPOST ignores questions. Spike shows offline sync is harder than planned. | At least 5 of 12 name the problem unprompted. 3 pilot commitments verbally agreed. Spike works on a mid-range Android phone. Otherwise apply the change or pause rule in section 10. |
| 2. Architecture and MVP build | Days 15 to 56 (16 Nov to 27 Dec) | Schema and row level security. Sites, assets, inspections, work orders, report PDF, client link. Postcode capture with offline check and background verification. Subscription limits. Audit trail. | Phase 1 go signal. Pilot sites list from customers. Hosting region decision. | Scope creep. Offline sync overruns. Year-end holidays slow customer feedback. | Pilot-ready build deployed. Security test of tenant isolation passes. Core loop works end to end on real phones. |
| 3. Pilot onboarding and testing | Days 50 to 77 (21 Dec to 17 Jan) | 3 to 5 paid pilots live. Digitisation of 10 to 15 sites each. Baseline and day-30 metrics. Weekly feedback calls. | Phase 2 build. Signed pilot agreements and ₦75,000 fees. Field agents hired for digitisation. | Holiday period reduces site activity. Technicians do not adopt. Poor data coverage at sites. | At least 3 pilots active with weekly use. Adoption at or above 60% of jobs by week three. At least 1 customer asks to continue at list price. |
| 4. Commercial launch and acquisition | Days 70 to 90 (11 to 30 Jan) | Paystack billing live. Pricing page and onboarding checklist. Case study from the best pilot. Referral asks from pilots. 10 qualified conversations in the pipeline. | Pilot results. Billing tested with real naira payments. Data processing agreement template. | Billing failures. Pilots do not convert. Support load exceeds founder time. | At least 2 pilots converted to paid plans. 5 new qualified proposals out. Break-even path stated in customers (about 10). |

**NIPOST dependencies by phase.** Day 1: submit the organisation registration and integration form. Phases 1 and 2: build and test on free development access. Phase 3: pilots use level 1 verification only. Phase 4: if production terms arrive and are workable, add level 2 and above for customers who need address detail. If terms are not workable or not published, ship without them and say so in sales materials.

**Key sequencing rules**

1. Book the first discovery calls in the first week. Everything else depends on them.
2. Do not start the full build until at least three buyers have agreed to a pilot in principle.
3. Hire or contract field agents for digitisation before pilots start, or the founder becomes the bottleneck.
4. Hold a go, change or stop review on day 21 and again on day 77 against the thresholds in section 10.

## 12. Risk register and mitigation

The largest risks are commercial, not technical: a narrow customer pool, low willingness to pay, and a platform owner (NIPOST) whose commercial terms are still unpublished. Likelihood and impact are my judgement.

| Risk | Likelihood | Impact | Mitigation | Early warning sign |
| --- | --- | --- | --- | --- |
| Too few FM and multi-site firms to support 50 to 80 customers | Medium | High | Count the pool in discovery. Add multi-branch operators as a second segment. Keep services revenue healthy. | Fewer than 40 reachable firms found after 3 weeks |
| Buyers do not pay more than ₦20,000 a month | Medium | High | Test price in every call. Sell digitisation as the paid entry point. Fall back to project work. | Price objections below ₦20,000 |
| NIPOST commercial pricing makes levels 2 and above uneconomic | Medium | Medium | Build on level 1 and GPS. Make enrichment optional and priced as pass-through. | Published credit prices above value to customers |
| NIPOST terms restrict storage or caching more than assumed | Medium | Medium | Store only the customer's own postcode string and verification result. Get written answers (section 7). | Written reply restricts persistence |
| NIPOST API outages or rate limits | Medium | Low | Optional enrichment, queue with retries, offline format check, circuit breaker. | Rising error rates in monitoring |
| NIPOST or a rival adds asset and field service features | Medium | High | Move fast on a narrow segment. Build switching cost through verified history and client links. | A rival launch in the same segment |
| Postcode data accuracy gaps (unverified records, missing buildings, wrong points) | High | Medium | Never override customer GPS. Use the verification ladder and field confirmation. | High mismatch rate in pilots |
| Data protection non-compliance | Medium | High | Confirm registration duty with a compliance organisation. Sign data processing agreements. Minimise stored personal data. Keep audit logs. | Customer security questionnaires you cannot answer |
| Hosting outside Nigeria draws customer or policy objections | Medium | Medium | Standard Postgres, documented migration path, clear disclosure. | An enterprise lead requires Nigerian hosting |
| Technicians do not adopt the app | Medium | High | Mobile-first design, offline queue, WhatsApp-friendly links, supervisor-led rollout, free technician seats. | Under 60% of jobs closed in the app by week three |
| Founder bandwidth: solo founder running services, VA work and this product | High | High | Fixed weekly hours for the product. Contract field agents. Cap pilots at five. Stop rule in section 10. | Slipping weekly deliverables |
| Naira volatility raises foreign hosting costs | Medium | Low to medium | Price annually where possible. Review pricing every quarter. Reserve a cost buffer. | Official rate moves more than 10% |
| Payment failures and churn from card declines | Medium | Medium | Offer bank transfer and invoicing for larger accounts. Use Paystack retries. | Failed charges above 10% |
| Misuse of the postcode brand or implied endorsement | Low | High | Never use NIPOST branding or the word official without written permission ([acceptable use policy](https://postcode.gov.ng/acceptable-use)). | Marketing copy review finds claims |
| Dependence on one pilot customer for case study and revenue | Medium | Medium | Run at least three pilots in parallel across two segments. | One account above 40% of revenue |

## 13. Long-term product expansion

Only expansions that reuse the same verified register and visit history, and that can be sold as extra recurring revenue to existing customers, are worth pursuing early. Expansions that need new data, hardware or a different buyer should wait.

| Opportunity | Builds on | Extra recurring revenue? | Added scope | Timing |
| --- | --- | --- | --- | --- |
| Portfolio-wide property intelligence (condition scores, overdue and risk views across all sites) | Inspection and work order history | Yes, as the Business tier or a ₦30,000 monthly add-on | Low. Mostly queries and views. | Phase two |
| Inspection and maintenance automation (recurring schedules, AI-drafted report narrative, photo condition notes) | Checklists and photos | Yes, as a usage or add-on fee | Medium | Phase two |
| Location-based contractor management (rates, ratings, service levels, nearest available technician) | Technician and contractor records, PostGIS | Yes, per-contractor or Business tier | Medium | Phase two, after real contractor data exists |
| Geographic asset distribution analysis (where assets and faults cluster) | Site coordinates, postcode hierarchy | Partly, inside reporting | Low | Phase two to three |
| Facilities and infrastructure management for larger operators (preventive maintenance, parts, procurement) | Work orders and assets | Yes, Enterprise tier | High. Competes head-on with mature CMMS. | Phase three, only if demand is proven |
| Asset utilisation and performance reporting | Months of clean usage data | Yes | High. Needs data customers do not yet collect. | Long term |
| Site selection and geographic expansion intelligence | Aggregated, anonymised portfolio data across customers | Possibly, as a data product | High. Needs scale, consent, and likely a data licence discussion with NIPOST. | Long term |
| Verified location layer for other businesses (customers and branches through an API) | Verified site register | Possible API revenue | Medium. Overlaps NIPOST's own offering and its restrictions on replicating NDAPS. | Avoid until NIPOST terms are clear |

**Which ones add revenue without widening the MVP.** Portfolio intelligence, report automation and contractor management reuse existing tables and screens, so they can be sold as higher tiers or add-ons without new infrastructure. Site selection and a verified location API are the most tempting and the riskiest. They need scale and depend on NIPOST's terms, and NIPOST's acceptable use policy bars building a service that materially replicates NDAPS.

## 14. Go/no-go criteria and sources

These gates decide how much money and time go into building. Thresholds are my proposals and should be adjusted once the first discovery calls show the real shape of the market.

| Gate | Evidence required | Decision |
| --- | --- | --- |
| Gate 1: end of discovery (week 4) | At least 12 qualified conversations. At least 5 name site or asset visibility, proof of visit or contractor control as a problem without prompting. At least 40 reachable target firms identified. | Pass: build the MVP. Fail: re-run with the multi-branch segment, or pause. |
| Gate 2: willingness to pay (week 4 to 6) | At least 3 paid pilots (₦75,000 each) and at least 2 written intents at ₦40,000 a month or more. | Pass: commit full build time. Fewer than 2 pilot payments after 12 qualified conversations: abandon or pivot. |
| Gate 3: pilot usage (week 10) | At least 3 pilots live. At least 70% of pilot jobs closed with photo and GPS proof. Each pilot customer uses it weekly. | Pass: open paid launch. Fail: fix workflow before selling. |
| Gate 4: conversion (day 90) | At least 3 pilots convert to paid plans, monthly recurring revenue of at least ₦150,000, and no single customer above 40% of revenue. | Pass: continue and hire contract help. Marginal: extend two months. Fail: stay a services business with the toolkit as an internal asset. |
| NIPOST gate (parallel) | Written answers on storage, caching, commercial pricing and production access. | Unfavourable answers change the design (postcode becomes an optional field), not the product. |

**Change direction if:** buyers value the proof-of-visit register but not the postcode (keep the product, drop the postcode positioning), or property managers pay while FM firms do not (switch segment, accept the lower price point), or buyers want only digitisation (run it as a service line first).

**Abandon if:** fewer than two pilot payments after 12 qualified conversations, or two consecutive pilots churn for reasons that are about the concept rather than the execution.

### Sources

Pages actually opened during this research. Statements about NIPOST pricing, production access and rate limits that appear only in secondary sources are labelled unverified in section 7.

**Official NIPOST and postcode programme**

- [Nigeria Digital Postcode, home](https://postcode.gov.ng/)
- [Frequently asked questions](https://postcode.gov.ng/faq)
- [Terms of use](https://postcode.gov.ng/terms)
- [Acceptable use policy](https://postcode.gov.ng/acceptable-use)
- [Privacy policy](https://postcode.gov.ng/privacy)
- [Integration programme](https://postcode.gov.ng/integration)
- [Innovation page](https://postcode.gov.ng/innovation)
- [Programme pitch site](https://pitch.postcode.gov.ng/)

**Secondary and community sources on the postcode**

- [Space in Africa, inside the digital postcode (3 Oct 2026)](https://spaceinafrica.com/2026/10/03/inside-nigerias-digital-postcode-the-gis-and-satellite-data-behind-11-characters/)
- [Naija Tech Guide, digital alphanumeric postcode](https://www.naijatechguide.com/nigeria-digital-alphanumeric-postcode.html)
- [ng-postcode community library (PyPI)](https://pypi.org/project/ng-postcode/0.3.4/)

**Competitors and pricing**

- [Porchplus](https://www.porchplus.com/), [pricing](https://www.porchplus.com/pricing) and [compliance](https://www.porchplus.com/compliance)
- [Ledge](https://ledgeapp.co/)
- [Property360](https://www.property360.africa/)
- [Fogwing, CMMS software](https://www.fogwing.io/cmms-software/) and [best CMMS in Nigeria](https://www.fogwing.io/guides/best-cmms-software-in-nigeria/)
- [Facilio, UpKeep pricing overview](https://facilio.com/blog/upkeep-pricing/)

**Payments**

- [Paystack pricing](https://paystack.com/pricing)

Other sources cited in earlier sections (market size reports, NDPA and GAID, National Digital Cloud Policy, CBN exchange rate) are linked where they are used. Customer demand, willingness to pay and all revenue figures remain assumptions until discovery tests them.
