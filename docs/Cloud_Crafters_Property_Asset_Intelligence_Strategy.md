**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**PRODUCT STRATEGY / MARKET ENTRY / COMMERCIAL MODEL** 

**Make every managed property and asset accountable.** 

*A commercially grounded MVP and revenue strategy for Property & Asset  Intelligence* 

**CLOUD CRAFTERS TECHNOLOGIES** 

Decision report \| Nigeria \| 30 October 2026 

Prepared from the supplied brief and current public-source research. All pricing, financial scenarios and market-entry volumes  created in this report are explicitly labelled as hypotheses or planning assumptions. 

**THE RECOMMENDED STARTING POINT** 

Build a multi-client property and asset operations system for small and mid-sized facilities-management  providers. Use Nigeria’s new digital postcode as an optional, authorised location-reference layer. Sell operational  control and client-ready proof of work, not a postcode directory. 

**Executive summary** 

Cloud Crafters should pursue this opportunity conditionally, with a narrow first customer and a workflow-led MVP.  The most attractive opening is not general-purpose property management and not a national postcode lookup service.  It is the day-to-day operating layer used by facilities-management (FM) providers that manage several properties for  multiple business clients. 

These providers need to know which sites they service, which equipment sits at each site, who is responsible for it,  what condition it is in, which inspections or maintenance jobs are due, and whether work was completed with usable  evidence. A single connected workflow can replace a scattered mix of registers, chat messages, phone calls and  separate job records, but the frequency and financial impact of these workarounds must be proven through customer  interviews and paid pilots before major investment.

| Decision area  | Recommendation |
| :---- | :---- |
| **Initial buyer** | Owner, managing director, COO or head of operations at a  Nigerian FM service provider managing roughly 5–30 client  sites. |
| **Core job to be done** | Register sites and assets, assign responsibility, inspect  condition, raise and close maintenance work, and produce a  client-ready activity/SLA report. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 1   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Decision area  | Recommendation |
| :---- | :---- |
| **Product positioning** | A multi-client property and asset operations control system for  FM providers; geospatial location and postcode are supporting  capabilities, not the headline product. |
| **MVP boundary** | Property/site registry, asset register, map/GPS capture,  responsibility, mobile inspections, maintenance requests and  work orders, proof of completion, activity history and simple  portfolio reporting. |
| **Postcode policy** | Integrate only through an approved NIPOST   organisation/developer route and written commercial/data-use  terms. Keep the product useful without API access. |
| **Commercial model** | One-time onboarding and register digitisation plus monthly  SaaS subscriptions; custom integrations and advanced reports  as paid extensions. |
| **Investment posture** | A conditional go: validate 15–20 buyer/user conversations and  secure at least two paid pilots before expanding scope. Do not  make production postcode approval a launch dependency. |

**MOST IMPORTANT COMMERCIAL TEST** 

Will an FM provider pay at least about ₦150,000–₦200,000 per month, plus onboarding, to improve portfolio asset  visibility, maintenance execution and client reporting? Public competitor research cannot answer this. A paid  pilot must. 

**Report guide** 

1\. Recommended product positioning and entry strategy 

2\. Target customers, buying roles and priority segment 

3\. Problem analysis and the first valuable workflow 

4\. MVP scope and feature prioritisation 

5\. Data model and system architecture 

6\. NIPOST digital postcode integration and licensing 

7\. Competitive landscape and differentiation 

8\. Pricing, revenue model and financial scenarios 

9\. Market sizing approach and first-customer acquisition 

10\. Customer discovery and paid pilot plan 

11\. 90-day execution roadmap 

12\. Risk register, investment gates and long-term expansion 

13\. Appendices: discovery questions, schema summary, research notes and sources 

**1\. Recommended product positioning and entry strategy** 

**1.1 Positioning** 

Recommended product category: Property & Asset Operations Intelligence for facilities-management providers. The  product should give an operations manager a reliable, current view of every managed site and the assets inside it,  then connect that record to inspections, maintenance and accountable field execution.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 2   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**POSITIONING STATEMENT** 

Cloud Crafters helps Nigerian facilities-management providers manage multi-site property portfolios from one  place: know what is on each site, where it is, who owns the next action, what work is due and what evidence  proves that the work was completed. 

The 11-character digital postcode is useful because NIPOST describes it as a unique building-level location reference. It can reduce ambiguity when teams discuss a building or site, but it does not create the operational record, prove legal  ownership or guarantee that a property has been correctly surveyed. The product’s value must survive even if NIPOST access is delayed or unavailable. \[1\]\[2\]\[3\]\[6\] 

**1.2 Why this is the right starting point** 

 It connects a recurring operating workflow to recurring software use. Asset registers, inspections, work orders  and client reporting are used continuously, unlike a postcode lookup that may be occasional.  It has an identifiable economic buyer. The managing director, COO or head of FM can approve a tool that reduces  service failure, reporting effort and contract risk. 

 It provides a practical paid onboarding service. Cloud Crafters can import and clean a customer’s existing register, assign locations and responsibility, and configure inspections before charging for the SaaS workflow.  It allows a narrow product that can be built by a lean team. It avoids building tenancy accounting, rent  collections, estate access control, procurement, IoT or a general ERP. 

 It gives Cloud Crafters a credible reason to integrate the postcode: location verification and site identification  inside a property/asset workflow, rather than an artificial postcode feature bolted onto an unrelated app. 

**1.3 What the product is not** 

| Do not position as…  | Why not |
| :---- | :---- |
| **A postcode directory or address-lookup app** | NIPOST already provides the public postcode website and app.  Repackaging its lookup service creates licensing risk and little  defensibility. |
| **A title, ownership or occupancy verification platform** | NIPOST’s own terms say a postcode does not establish  ownership, title, occupancy, identity or legal status. These need  independent evidence and legal processes. \[6\] |
| **A full property-management/estate community suite** | Rent, lease, service-charge, resident, gate-pass and payment  workflows are already offered by Nigerian products. Building  these expands scope and moves into a crowded category. \[13\] \[14\]\[15\]\[16\] |
| **A full enterprise CMMS/EAM** | Advanced procurement, spare parts, meter readings, IoT,  complex work planning, predictive maintenance and enterprise asset hierarchies are expensive to deliver and support. Prove  the basic field workflow first. |

**2\. Target customers, buying roles and priority segment** 

**2.1 Segment assessment** 

The brief identifies five broad segments. The following comparison is a strategic assessment, not a measured market  survey. Scores are working hypotheses informed by product fit, publicly visible competitors and likely procurement  complexity; validate them with interviews.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 3   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Segment  | Pain / product fit  | Willingness to pay  | Access / sales cycle  | Priority |
| :---- | :---- | :---- | :---- | :---- |
| **FM companies serving  multiple client sites** | High: asset registers, field  work, SLA evidence and  client reporting align   tightly. | Medium–high if tied to  contract delivery and   service quality. | Medium: identifiable   decision-makers; moderate proof and change effort. | 1 — Beachhead |
| **Property management  firms** | Medium: property and  maintenance records fit;  rent, lease and service  charge systems often   dominate. | Medium, but price   pressure and feature   expectations are broader. | Medium: reachable, but  overlaps with established  local suites. | 2 — Selective |
| **Businesses with many  branches / warehouses** | High operational fit for  assets and sites; may   require integrations and  internal IT controls. | Potentially high. | Low–medium: longer   procurement, security   review and stakeholder  count. | 3 — Later lighthouse   accounts |
| **Developers / portfolio  owners** | Medium: asset/property  visibility matters, but   many also need sales,   tenancy or investment  reporting. | Medium–high for larger  portfolios. | Medium–low: decisions  may be project or owner  specific. | 4 — Partner / later |
| **Gated estates /   community managers** | Medium–high:   maintenance and asset  workflows are relevant. | Variable; estate-level   budgets can be   constrained. | High competition: Gate  Africa, Ledge, Hutstack and EstateOps already serve  adjacent needs. | 5 — Avoid first |

**2.2 Ideal customer profile** 

Start with owner-led or professionally managed FM companies in Lagos, Abuja and Port Harcourt that manage  multiple commercial, institutional or industrial sites for third-party clients. A useful first-fit profile is approximately 5– 30 active sites, 100–2,000 maintained assets, a field team of 5–40 people, and an operations manager who produces  reports for client organisations. These ranges are targeting assumptions, not published market averages. 

 They have recurring service contracts, not just one-off maintenance jobs. 

 They maintain registers or work logs in spreadsheets, paper files, chat groups or several disconnected tools.  They need to prove that inspections and maintenance work happened, on time, at the correct site.  The operations lead has authority to approve a pilot without a long enterprise procurement process.  They can supply one real asset register and a named pilot site for data migration. 

**2.3 Buyer and user personas**

| Role  | What matters to them  | Buying / adoption signal |
| :---- | :---- | :---- |
| **Economic buyer: owner, MD, COO** | Contract retention, service quality, team  productivity, accountability, margin and  customer confidence. | A client has challenged a report, a new  multi-site contract is being onboarded, or an operational failure has become expensive. |
| **Operational champion: Head of FM /  Operations Manager** | One view across sites, open work orders,  overdue inspections, assets without  custodians and unresolved risks. | Currently compiles weekly/monthly status  manually or chases site teams for updates. |
| **Day-to-day user: site manager /   supervisor** | Fast site and asset lookup, inspection  checklists, assigning jobs and proving  closure. | Has to reconcile instructions from phone  calls, WhatsApp, email and paper logs. |
| **Field user: technician / contractor** | Simple mobile workflow, low typing burden, clear job details, photo evidence and ability  to work with poor network. | Missed jobs, repeat visits, unclear handoffs  or disputes about completion. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 4   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Role  | What matters to them  | Buying / adoption signal |
| :---- | :---- | :---- |
| **Client-side stakeholder: property owner /  client FM lead** | Reliable site status, maintenance history,  evidence, service-level reporting and  transparency. | Wants auditable monthly reporting but  should not need full access to the FM  provider’s internal workspace. |

**2.4 Buying triggers** 

 Winning a new FM contract or taking over a new cluster of sites. 

 Client audit, tender or renewal requires better asset records, inspection evidence or service-level reporting.  Loss, damage, repeated repair or unclear custody of generators, pumps, HVAC equipment, fire equipment or other critical assets. 

 A maintenance backlog or repeat breakdown has caused service disruption or unplanned spend.  An operations manager is spending too much time consolidating site reports and chasing technicians. 

**3\. Problem analysis and the first valuable workflow** 

**3.1 Strongest commercial problem to test** 

The brief’s central problem is fragmented visibility across geographically distributed property and physical assets. Of  the listed pain points, the strongest first commercial opportunity is the gap between a site/asset register and  accountable maintenance execution: the company cannot easily connect the correct property, asset, custodian,  inspection, work order, completion evidence and client report. 

| Problem area  | Commercial value  | MVP treatment |
| ----- | :---- | :---- |
| **Know which sites and assets exist and  where** | High. It is the base record for all operations  and field coordination. | Build a structured property/site and asset  register with map pins, GPS and optional  postcode. |
| **Know who is responsible for an asset or  job** | High. Clarifies handover and action  ownership. | Build custodian assignment, role-based  access and assignment history. |
| **Inspections, condition and maintenance  due dates** | High. Creates repeat usage and measurable  operating control. | Build templated inspections, condition  status, simple recurring due dates and  exceptions. |
| **Coordinate technicians and maintenance  contractors** | High. Turns a record into action.  | Build request-to-work-order, assignment,  status, due date and closure evidence. |
| **Historical maintenance and inspection  evidence** | High. Supports client queries, audits and  repeat fault analysis. | Build asset event timeline, attachments and  searchable activity history. |
| **Identify underutilised or neglected assets**  | Medium, later. Requires clean, consistent  register and time-series data. | Start with simple stale/overdue/unused  flags; defer advanced utilisation analytics. |
| **Portfolio-wide analytics**  | Medium–high after data capture is   consistent. | Start with a small dashboard and exportable reports; defer custom BI and benchmarking. |
| **Legal ownership / title verification**  | Not the initial product problem; postcode  does not prove title. | Out of scope. Reference customer-supplied  documents only if required; no title claims. |

**3.2 The workflow to own** 

1\. Onboard a client portfolio and create properties / sites with address, GPS point, site code and location notes. 2\. Import or register each physical asset under a site; assign a unique customer asset tag, category, status and  custodian. 

3\. Run a site inspection using a mobile checklist; capture condition, photo, timestamp and responsible inspector.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 5   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

4\. Raise a maintenance request; triage it into a work order with priority, owner, due date and asset/site context. 5\. Assign a technician or contractor; capture work started, parts/notes where relevant, proof of completion and  supervisor sign-off. 

6\. Update the asset history and site status automatically; include the completed work in a client-ready report. The product should make this workflow faster and more reliable than an operations manager’s current combination  of spreadsheets, chat and calls. Do not try to replace every existing system on day one. Start with the operational  register and field-work evidence; connect payments, procurement or accounting only when a real buyer requires it. 

**3.3 Value metrics** 

| Outcome  | Metric to baseline and improve |
| :---- | :---- |
| **More complete asset visibility**  | Share of in-scope assets with a valid site, category, tag, status  and custodian. |
| **Better location quality** | Share of sites with GPS point and human-readable address;  share with a postcode recorded and validated where  authorised. |
| **Faster response**  | Median time from request creation to assignment; from  assignment to first action. |
| **Maintenance discipline**  | On-time inspection rate; preventive tasks completed by due  date; overdue work-order count. |
| **Stronger proof of work**  | Share of closed work orders with required evidence and  supervisor sign-off. |
| **Less reporting effort**  | Hours spent compiling the monthly client report before versus  after rollout. |
| **Reduced repeat issues**  | Repeat work orders for same asset / failure category over a  defined comparison period. |

**4\. MVP scope and feature prioritisation** 

The MVP should be a responsive web application with mobile-first field screens and a simple installable PWA  experience. A separate native mobile app is not required for the first customer cohort. The MVP is about dependable  records, field adoption and a closed-loop work process, not a large menu of modules.

| Feature  | Priority  | MVP acceptance test |
| ----- | :---- | :---- |
| **Organisation, client portfolio and user roles**  | Must have  | One FM company can separate its own staff,  client accounts and site access. |
| **Property / facility registration**  | Must have  | Create site with name, site code, address, GPS  pin, property type, client, status and notes. |
| **Map view and geospatial search**  | Must have  | Display registered sites, filter by   client/region/status and open a site record. |
| **NIPOST digital postcode field and validation  adapter** | Must have as a data model; API integration  conditional | User can store an entered postcode and  validation status; authorised API call is optional and failure does not block saving a site. |
| **Asset register and categories**  | Must have | Register asset tag, name, type, serial/model, site, condition, status, acquisition/commissioning  date and notes. |
| **Asset-to-property relationship and custodian**  | Must have  | Asset belongs to a site; custodian and   responsibility changes are timestamped. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 6   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Feature  | Priority  | MVP acceptance test |
| :---- | :---- | :---- |
| **Inspections and condition reporting**  | Must have | Use at least 2–3 configurable checklist  templates; capture outcome, notes, photos and  inspector. |
| **Maintenance requests and work orders**  | Must have  | Convert request to assigned work order; track  priority, due date, status, notes and closure. |
| **Technician / contractor assignment**  | Must have  | Assign internal team member or approved  contractor; notify and record the handoff. |
| **Activity history and audit trail**  | Must have  | Critical changes and work events have actor,  action, timestamp and entity link. |
| **Basic dashboard and CSV/PDF report**  | Must have | Show open/overdue work, upcoming   inspections, asset counts by site and simple  period report. |
| **Spreadsheet import and onboarding tools**  | Must have  | Import properties/assets from a controlled CSV  template with validation and error report. |
| **Offline capture and sync queue**  | Should have for pilot | Allow inspection/work-order draft capture  when offline; sync when connected without  silently overwriting another user’s updates. |
| **Client read-only portal**  | Should have / phase 2  | A client sees only its sites and agreed reports,  not other clients or internal notes. |
| **Recurring PM rules and templates library**  | Phase 2  | Create recurring asset maintenance schedules  with exception and escalation logic. |
| **Contractor portal, vendor scorecards and  SLA rules**  | Phase 2  | External teams accept jobs and record status  against agreed SLAs. |
| **Parts, procurement, purchase orders and  inventory**  | Later  | Only after pilot evidence that this blocks  adoption or renewal. |
| **IoT / telemetry / predictive maintenance / AI  diagnosis**  | Later  | Requires clean history and hardware /  integration partnerships. |
| **Rent, lease, service-charge and resident  access control**  | Out of scope initially  | Avoid duplicating estate and rental suites. |
| **Title, ownership, identity or tenant   verification**  | Out of scope  | NIPOST postcode alone is not evidence of these  facts. |

**4.1 Non-negotiable MVP quality** 

 Fast data entry: asset/site import, reusable categories and templates; do not require field teams to type long  descriptions repeatedly. 

 Photo evidence: compress uploads, attach to the correct inspection/work order and preserve who captured it and  when. 

 Permission boundaries: staff should see only assigned client portfolios and sites; client users should see only the  agreed subset. 

 Network resilience: autosave forms, show sync state and prevent duplicate work orders after reconnecting.  Exportability: customers must be able to export their own properties, assets, inspections and work-order history  in usable formats. 

 Operational support: provide migration, training and first-month adoption support; an empty system is not a  successful implementation.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 7   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**5\. Data model and system architecture** 

**5.1 Recommended stack** 

| Layer  | Recommendation  | Reason / implementation note |
| :---- | :---- | :---- |
| **Application**  | Next.js \+ TypeScript | One responsive web app with   admin/operations interfaces and mobile first field workflows. PWA first; avoid  separate native apps until pilot demand  requires them. |
| **Database and auth**  | Neon \+ PostgreSQL | Managed Postgres, authentication, storage  and row-level security reduce infrastructure work for a lean team. Production should use a paid plan, backups and monitoring. \[22\] |
| **Geospatial**  | PostGIS in a dedicated extensions schema | Store GPS points, spatial indexes and query  by proximity/region. Neon documents  PostGIS support and recommends installing  in a dedicated schema rather than public.  \[23\] |
| **Maps**  | MapLibre GL JS \+ paid commercial tile  provider | MapLibre renders vector tiles in the  browser; it does not itself supply the tiles.  Choose a provider with a commercial plan  and usage limits. \[24\]\[25\] |
| **NIPOST**  | Authorised API/SDK adapter | Keep all NIPOST calls server-side, behind a  connector and feature flag. Do not expose  API secrets in the browser. |
| **Payments**  | Paystack for invoice checkout and   supported recurring authorisations | Paystack documents recurring plans, but  Nigerian subscriptions use card and direct  debit; failed subscription charges are not  retried. Keep manual bank-transfer/invoice  reconciliation as an alternative. \[20\]\[21\] |
| **Hosting**  | Vercel Pro or a Node-compatible managed  host | Vercel Pro is a reasonable starting   deployment option; Next.js can also run on  any Node.js host that supports the app’s  required features. Check region and data transfer obligations. \[24\]\[26\] |
| **Messaging**  | Email first; WhatsApp/SMS through  approved provider in phase 2 | Avoid adding notification cost and template  approval complexity before core workflow  adoption is proven. |

**5.2 Logical data model** 

Use an organisation-level tenant key on all customer-owned records and enforce tenant separation in the database.  Model the customer’s clients separately from Cloud Crafters’ platform tenants: an FM company is one Cloud Crafters  organisation; its managed property owners or corporate clients are portfolio accounts inside that organisation.

| Entity  | Essential fields  | Relationship / purpose |
| :---- | :---- | :---- |
| **organisations**  | id, name, legal\_name, plan\_id, status, created\_at  | Tenant boundary for the paying FM company or  direct multi-site operator. |
| **users / profiles**  | id, name, email, phone, auth\_user\_id, status  | Authenticated people; avoid duplicating identity  data unnecessarily. |
| **memberships**  | org\_id, user\_id, role, client\_scope, status  | Many-to-many user membership, roles and  site/client scope. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 8   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Entity  | Essential fields  | Relationship / purpose |
| :---- | :---- | ----- |
| **client\_accounts**  | id, org\_id, client\_name, reference, contact,  contract\_start/end | The property owner / business customer for whom  an FM provider works. |
| **properties / sites** | id, org\_id, client\_id, site\_code, name, address\_text,  state, city, geom, location\_source, postcode\_value,  validation\_state, status | A physical building, facility, campus, branch or  managed site; geom uses PostGIS Point (SRID 4326). |
| **assets** | id, org\_id, asset\_tag, category\_id, name,   manufacturer, model, serial\_no, status, condition,  installed\_on, criticality | Physical equipment or maintainable asset,  uniquely tagged within an organisation. |
| **asset\_locations**  | asset\_id, property\_id, location\_detail, valid\_from,  valid\_to | Time-aware site/room/zone relationship; supports  relocation without destroying history. |
| **asset\_custodians**  | asset\_id, user\_id/contractor\_id, assigned\_at,  released\_at, handover\_note  | Tracks responsibility and handover history. |
| **inspection\_templates / template\_items**  | org\_id, name, version, item\_text, answer\_type,  required\_flag  | Reusable inspection/checklist definitions. |
| **inspection\_runs / responses** | site\_id, asset\_id, template\_version, inspector\_id,  started\_at, completed\_at, result, response\_json,  media\_refs | Stores each inspection and answer/evidence. |
| **maintenance\_requests**  | site\_id, asset\_id, requester\_id, description, priority,  status, created\_at  | Initial issue or service request. |
| **work\_orders** | request\_id, site\_id, asset\_id,   assigned\_user\_id/contractor\_id, priority, due\_at,  status, opened\_at, closed\_at, closure\_code | Work execution record with full asset/site context. |
| **work\_order\_events**  | work\_order\_id, actor\_id, event\_type, note,  timestamp, media\_refs | Immutable timeline for assignment, status change,  update and sign-off. |
| **contractors** | org\_id, name, trade, contacts, insurance\_expiry,  status | Approved external service providers; detailed  vendor compliance can wait. |
| **postcode\_checks**  | property\_id, source, checked\_at, result\_code,  request\_id, permitted\_payload\_ref, retention\_until | Separate record for authorised NIPOST validations; only retain response fields and duration permitted  by written terms. |
| **audit\_events**  | org\_id, actor\_id, entity\_type, entity\_id, action,  old/new metadata, timestamp, ip/device context | Audits important data changes and access events;  protect from tenant tampering. |
| **subscriptions / usage\_events**  | org\_id, plan, limits, billing\_status, provider\_ref,  event\_type, quantity, period  | Billing, feature limits and usage metering. |

**5.3 Relationships and geospatial rules** 

 One organisation has many client accounts, users, properties, assets, work orders and subscriptions.  One client account has many properties. One property has many assets. Asset relocation should be recorded  through a dated asset\_locations relationship rather than overwriting its previous location.  Assets can have many custodians over time, but only one current accountable custodian unless the workflow  explicitly supports shared responsibility. 

 Inspection runs and work orders should link to a property and optionally to an asset. A work order can exist for a  site issue that has no registered asset. 

 Store GPS geometry supplied by the customer or captured by the field user separately from the postcode value.  Record source, accuracy (when available), who verified it and when. 

 Create a GIST index for the geometry column. Use proximity search and map filtering against the customer’s own  site points; do not clone the national postcode database.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 9 

**5.4 Multi-tenancy, permissions and security**   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Control  | Minimum implementation |
| :---- | :---- |
| **Tenant isolation**  | org\_id on all tenant-owned tables; PostgreSQL RLS policies for  select/insert/update/delete. Never rely only on frontend filtering. |
| **Role permissions** | Platform admin; organisation owner; operations manager; client  portfolio/site manager; technician; contractor; client read-only.  Enforce scope server-side. |
| **Secrets** | NIPOST, Paystack and map keys stored server-side in   environment/secret manager; restrict and rotate keys. Never ship  service-role or privileged keys to browser code. |
| **Auditability** | Append-only activity events for asset custody, location changes,  approvals, work-order state and data exports. Restrict deletion of  audit evidence. |
| **Data security** | HTTPS, managed database encryption, secure backups, least  privilege, MFA for privileged users, short-lived signed media URLs,  monitoring and incident response. |
| **Privacy** | Data minimisation, purpose limitation, retention rules, privacy  notice, customer data-processing agreement and data-subject  request process. The customer will usually be controller and Cloud  Crafters processor, depending on the actual facts. |
| **Portability** | Customer export and controlled offboarding; NIPOST-derived data  deletion/retention must follow the NIPOST contract, separately from customer-owned operational records. |

Legal note: the Nigeria Data Protection Act 2023 requires appropriate technical and organisational safeguards and  provides a 72-hour supervisory notification clock for a controller where a personal-data breach is likely to create risk  to individuals. A software provider should contractually notify its customer-controller promptly enough for the  customer to meet its legal deadline. Confirm the exact controller/processor roles, registration category, DPCO  requirements and cross-border transfer basis with Nigerian privacy counsel or a licensed DPCO before launch. \[9\]\[10\] 

**6\. NIPOST digital postcode integration and licensing** 

**6.1 What is publicly confirmed**

| Question  | What public official sources currently say |
| :---- | :---- |
| **Launch / format** | The Federal Government announced nationwide launch on 1 October  2026\. The system uses GIS and an 11-character alphanumeric postcode  for addressable buildings across Nigeria. \[2\] |
| **Unit of identification**  | A code identifies a building, not the person living or working there. It is a location reference, not the full human-readable address. \[3\] |
| **Coverage / missing records** | If a building is missing, the public FAQ says a request can be submitted  with current location, address or a Google Maps link for review and  assignment. Do not assume complete coverage on day one. \[3\] |
| **Organisation integration** | The official site says organisations can create a developer account, obtain an API key and integrate through API, web/mobile SDKs or bulk access.  The integration form asks for organisation, type, website, country,  contact, expected volume, integration mode and intended use. \[1\]\[4\] |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 10   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Question  | What public official sources currently say |
| :---- | :---- |
| **Commercial usage** | The Acceptable Use Policy allows authorised commercial applications  subject to applicable agreements, but prohibits unauthorised resale,  redistribution, commercial exploitation or creation of a substitute  postcode service. Prior written authorisation is required for those  activities. \[5\] |
| **Fees** | Terms say premium API access, bulk processing and enterprise services  may attract fees; exact fees, usage limits, payment terms, tax and refunds sit in a separate agreement/pricing schedule. Fees are generally quoted  in naira excluding applicable taxes unless stated otherwise. \[6\] |
| **Retention** | The AUP prohibits retaining NDAPS data beyond the period authorised.  On termination, terms require the user to stop using NDAPS data unless  further retention/use is expressly permitted or required by law; NIPOST  may require deletion or return. \[5\]\[6\] |
| **Service reliability** | NIPOST terms say the service may be changed, restricted, suspended or  discontinued and is provided as available; availability and accuracy are  not guaranteed. The product must have a fallback. \[6\] |

**6.2 What is not publicly established** 

As of 30 October 2026, the public pages reviewed do not expose an unauthenticated technical API reference that lets  an independent developer verify endpoint paths, authentication details, request/response schemas, SDK package  names, sandbox availability, pagination, rate limits, service-level commitments or precise commercial caching rules.  The public integration page directs organisations to a developer dashboard or the integration team. Do not write  implementation estimates or licence assumptions around unpublished details. 

 Exact pricing per request, per organisation, per lookup or per bulk dataset. 

 Whether postcode values and associated coordinates can be persisted indefinitely in a customer’s operational  register, or only cached for a specified time. 

 Whether displaying the postcode and any NIPOST-sourced address/coordinates to Cloud Crafters’ customer users  is allowed under a standard API plan. 

 Whether Cloud Crafters can act as a software vendor processing lookups on behalf of multiple unrelated customer organisations under one developer account or needs separate client registrations/approvals.  Production approval steps, due diligence, expected review time, usage quotas, rate limits, uptime/SLA, support  and change-notice policy. 

 Whether use of the postcode logo/branding or the phrase “NIPOST verified” requires separate written brand  approval. 

**6.3 Access and approval route** 

1\. Create a standard Cloud Crafters organisation/developer account through the official dashboard link on  postcode.gov.ng. Confirm the account type and organisation registration requirements. 

2\. Submit the official integration interest form describing the Property & Asset Intelligence use case, expected  monthly lookups, number of customer organisations, whether lookups are user-entered or bulk, and how data  will be displayed and stored. 

3\. Request API/SDK documentation and a sandbox or test key. Ask whether one Cloud Crafters account may support  multiple customers and how tenant-specific authorisation should work. 

4\. Ask for the applicable API service agreement, pricing schedule, data-use terms, DPA, retention policy, rate limits,  security requirements and production-approval checklist. 

5\. Get written approval for the actual commercial model: customer-facing postcode validation inside a paid SaaS;  whether any result fields can be stored; permitted cache duration; display and export; subcontracting; offboarding and deletion.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 11   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

6\. Complete technical integration behind a server-side adapter and feature flag. Run a controlled pilot only when the  required commercial use is authorised. 

The separate NIPOST innovation-hub programme had its own eligibility requirements (including an established hub  and a minimum active startup portfolio) and its application window closed on 13 October 2026\. It should not be  confused with the standard organisation/developer-account route. \[7\] 

**6.4 Data and UX design** 

| Situation  | Product behaviour |
| :---- | :---- |
| **New property record**  | Allow user to enter address text, site code and GPS pin manually.  Mark each field source and verification state. |
| **Authorised NIPOST lookup available** | Send request through the server-side adapter. Show returned  postcode and supported confirmation result; explain what was  validated and at what time. |
| **API unavailable / rate limited** | Allow save using address text and GPS coordinates. Queue an  optional re-check; show “not checked” rather than presenting stale  data as current. |
| **Building not found**  | Let user save an unverified location and explain that the building  may need to be submitted through NIPOST’s official request process. |
| **Field verification** | Let authorised staff confirm that the pin corresponds to the physical site, recording verifier, timestamp and optionally photo/evidence.  Keep this separate from NIPOST validation. |
| **Customer export / termination** | Export customer-owned asset/site data. Treat NIPOST-derived fields  according to the signed licence; do not assume they can be  redistributed or retained after termination. |

**CRITICAL LEGAL DISTINCTION** 

Never label a building “ownership verified”, “title verified”, “tenant verified” or “identity verified” merely because a postcode was returned. NIPOST expressly says postcode data does not itself establish those facts. \[6\] 

**7\. Competitive landscape and differentiation** 

Public competitor pages show that the adjacent categories are already being served. They do not prove market share,  adoption, retention, or customer satisfaction. Several competitors do not publish prices. This review compares their  publicly described positioning and features as of 30 October 2026\.

| Competitor  | Publicly described offer  | Implication for Cloud Crafters |
| :---- | :---- | :---- |
| **Gate Africa** | Estate/community management, access  management, resident communication and  payment features. Public price: ₦200,000/year  Basic (up to 200 residents) and ₦650,000/year  Standard (up to 5,000 residents). \[12\] | Competes for estate-manager budgets and  shows a low local annual price anchor.  Differentiate toward FM service providers and  cross-client operational workflows, not  resident/gate functions. |
| **Ledge** | Nigerian estate and property-manager suite  covering rent, service charge, household  register, gate passes, tenant/lease workflows,  owner statements, Paystack and maintenance  tickets. Its site advertises an all-features/no unit-limit offer through 2 February 2027\. \[13\] | Strong local alternative for estate/property  administration; free promotional pricing raises  short-term buyer price sensitivity. Do not build  rent/lease/levy features to compete. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 12   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Competitor  | Publicly described offer  | Implication for Cloud Crafters |
| :---- | :---- | :---- |
| **Hutstack** | Nigerian property operations platform for  property managers, developers, facility teams,  landlords and investors; includes lease/tenant,  rent/accounting, maintenance/owners, and  estate/community products. \[14\] | Direct adjacent product. Cloud Crafters needs a  sharper FM-provider use case, portfolio-level  maintenance evidence, and proof of better field execution rather than a broad all-in-one claim. |
| **EstateOps** | Positions as an estate operating system with  asset templates, preventive maintenance, work  orders, vendors, residents, dues and managed  engineering services. It publicly reports 180  templates, 14 estate systems mapped and one  estate partner signed; these are vendor self reported. \[15\] | Direct competition for estate asset/maintenance workflows, with AI and managed-service  positioning. Avoid assuming first-mover  advantage; differentiate on multi-client FM  contractor workflows and simpler adoption. |
| **RateMe CMMS / Watchtower** | Lagos software vendor describes CMMS  workflows for work requests, work orders,  preventive maintenance, assets/locations and  reports; Watchtower tracks device/SIM stock,  technician custody, site installs and service  renewals. \[16\] | Closest local functional competitor for CMMS  and custody workflows. Cloud Crafters must  validate what it does better or more simply; do  not claim there is no local asset/CMMS product. |
| **CaretakerPro** | Nigerian property-management offering with  tenant dashboards, rent workflows,   maintenance requests, vendor performance  and role-based access. \[17\] | Competes in landlord/estate workflows, but  public detail on cross-client FM work-order  evidence is limited. Treat as a competitor to  monitor. |
| **MaintainX** | International maintenance software with  published Essential pricing of   US\$20/user/month billed annually and  Premium at US\$65/user/month billed annually;  includes work orders, PM, parts, analytics and  API at higher tiers. \[18\] | Useful functional and pricing benchmark, but  not a directly comparable Nigeria-localised  offering. Local value must come from   onboarding, low-friction field workflows, NGN  pricing and context. |

**7.1 Market structure and prospect sources** 

A third-party FM market page lists active providers and operators including Alpha Mead Group, Global PFI, Broll  Property Group, Greenkey, Solid Rock, Total Facilities Management, UPDC FM, Eko Maintenance, Gwill FM, James  Cubitt, Eliezer Group and others. Treat this as a prospecting seed list, not an official registry or a verified count of  addressable SME buyers. It contains large operators as well as potential mid-market targets. \[11\] 

**7.2 Defensible differentiation to test** 

 Multi-client by design: one FM provider manages many client accounts and site portfolios without data leakage  between clients. 

 One linked record: property/site location physical asset custodian inspection work order completion → → → → → evidence client report. → 

 Field usability for Nigerian operating conditions: short forms, low-bandwidth behaviour, offline drafts, photo  compression and simple technician roles. 

 Location confidence: GPS pin, readable address, field verification and an optional NIPOST postcode validation,  each with its own source and status. 

 Implementation-led adoption: spreadsheet import, onsite/remote register digitisation, template setup and staff  onboarding rather than self-serve signup alone. 

 Client-facing reporting: simple monthly packs showing inspections due/completed, work orders by status, SLA  evidence and asset condition. 

None of these is yet a proven competitive gap. EstateOps and RateMe already publicly describe parts of the  asset/maintenance workflow, and Hutstack has maintenance workflows. Validate the specific underserved job by  asking buyers to demonstrate their current process and compare shortlisted systems in live demos.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 13   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**8\. Pricing, revenue model and financial scenarios** 

**8.1 Pricing principles** 

 Price the operating value, not the postcode lookup. The buyer pays for site/asset visibility, inspection discipline,  work-order execution and reporting. 

 Use a base subscription plus limits across active properties/sites, registered assets and named operational users.  Allow free request-only or client read-only users within reason. 

 Charge separately for onboarding and data cleanup. The migration burden is real work and should not be hidden  inside a low monthly subscription. 

 Price field surveys and physical asset tagging separately from SaaS. These costs vary with site spread, asset count,  access and data quality. 

 Offer annual prepayment with a modest discount, but retain monthly billing to lower buying friction. Avoid  unlimited support/custom development on standard tiers. 

**8.2 Proposed NGN tiers (hypotheses)** 

| Plan  | Monthly subscription  | Suggested limits  | Included |
| :---- | :---- | ----- | :---- |
| **Starter**  | ₦85,000  | Up to 5 sites; 250 assets; 5 full users | Site and asset register, map,   custodians, basic inspections, work orders, standard dashboard and  CSV export. |
| **Growth**  | ₦190,000  | Up to 20 sites; 1,000 assets; 15 full  users | Starter plus configurable   inspection templates,   priority/assignment workflow,  activity history, client-ready   monthly report and import   support. |
| **Portfolio**  | ₦375,000  | Up to 50 sites; 3,000 assets; 30 full  users | Growth plus client portfolio   scoping, advanced report filters,  recurring maintenance schedules,  client read-only reporting and  priority support. |
| **Enterprise**  | From ₦650,000; quote  | Above 50 sites / 3,000 assets or  custom security / integrations | Custom onboarding, integration,  negotiated SLA, advanced roles,  data export/API and dedicated  account management. |

Limits are starting hypotheses. The pilot will reveal whether customers think in properties, sites, asset counts, users or active work orders. For FM providers, “managed sites” and “registered assets” are likely more aligned with value than  per-seat-only pricing. Keep a fair upgrade path and do not charge a technician simply to report a fault. 

**8.3 One-time revenue and optional services**

| Service  | Initial price hypothesis  | Scope / pricing basis |
| :---- | :---- | :---- |
| **Starter onboarding**  | ₦250,000 | Organisation setup, up to 5 sites, standard  import template, role setup and one remote  training. |
| **Growth onboarding**  | ₦500,000 | Up to 20 sites, CSV cleanup, asset categories, 2–3 inspection templates and manager/technician  training. |
| **Portfolio onboarding**  | ₦1,000,000+ | Portfolio/client segmentation, multiple-site  imports, migration checks, reporting   configuration and rollout support. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 14   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Service  | Initial price hypothesis  | Scope / pricing basis |
| :---- | :---- | :---- |
| **Asset-register digitisation**  | ₦1,500–₦3,000 per asset record for assisted  clean-up; field capture quoted separately | Hypothesis only. Adjust by data completeness,  required verification, tag/label work and travel. Quote site visits by team-day plus materials. |
| **Custom integration / workflow automation**  | ₦1,500,000–₦5,000,000+ one-off | Scope and fixed-price after technical discovery;  avoid bespoke integrations before subscription  contract. |
| **Advanced reporting / portfolio analytics**  | ₦50,000–₦150,000 per month add-on or  included in Portfolio | Only after core events and asset data are  consistent. |

The price bands above are Cloud Crafters pricing hypotheses, not quoted industry rates. Public comparables include  Gate Africa’s ₦200,000/year and ₦650,000/year estate plans, but its resident/access-management scope is materially  different. MaintainX publishes US\$20 and US\$65 per user/month annual-billing tiers internationally. These anchors  indicate very different product scopes and cannot be compared as like-for-like price points. \[12\]\[18\] 

**8.4 Illustrative recurring revenue scenarios** 

| Scenario  | Plan mix  | Monthly recurring   revenue | Annualised recurring  revenue | One-off onboarding at  proposed fees |
| :---- | :---- | :---- | :---- | :---- |
| **12 customers**  | 3 Starter \+ 6 Growth \+ 3  Portfolio  | ₦2,520,000  | ₦30,240,000  | ₦6,750,000 |
| **20 customers**  | 5 Starter \+ 10 Growth \+ 5  Portfolio  | ₦4,200,000  | ₦50,400,000  | ₦11,250,000 |

Calculation: monthly revenue \= Starter count × ₦85,000 \+ Growth count × ₦190,000 \+ Portfolio count × ₦375,000.  Annualised recurring revenue \= monthly revenue × 12\. Onboarding is one-time and is not ARR. These are scenario  outputs, not forecasts or a statement of contracted demand. 

**8.5 Gross margin and early cost structure** 

SaaS gross margin can become attractive after onboarding is standardised, but early pilots will be service-heavy. Use  these planning ranges until actual cloud, support, map, messaging and NIPOST costs are measured: 

| Revenue stream  | Target gross margin once repeatable  | What can erode it |
| :---- | :---- | :---- |
| **SaaS subscription**  | 75–85% | High-touch support, custom reporting,  excessive media storage, map/API usage and poor plan limits. |
| **Onboarding / data migration**  | 35–55% | Messy spreadsheets, on-site visits, duplicate  assets, inconsistent tags and repeated  cleansing. |
| **Physical field survey / tagging**  | 20–40% | Travel, access constraints, field labour, asset complexity, retakes and equipment/material costs. |
| **Custom integration**  | 35–55% | Unbounded scope, third-party API changes,  bespoke testing and ongoing support  obligations. |

These margin ranges are assumptions, not measured results. Set change-control rules, define what is included in  support, bill field work separately and charge for bespoke requirements. Review gross margin by customer after each  pilot, including founder/implementation hours where possible.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 15   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**8.6 Initial platform cost envelope** 

Published starting prices provide a modest infrastructure baseline: Neon starts at US\$25/month, Vercel Pro  has a US\$20/month platform fee with included usage credit, and MapTiler Flex is listed at US\$30/month with included  map sessions and API requests. A production setup could therefore start around US\$75/month before additional usage, email/SMS, storage growth, logging, monitoring, domain, support, NIPOST fees or tax. Confirm plan terms and regional availability at purchase. \[22\]\[24\]\[25\]\[26\] 

Illustrative 90-day incremental cash budget: ₦5.4m–₦11.5m, excluding founder salary, company overhead, taxes and  any large-scale physical tagging programme. This is a planning envelope, not a vendor quote. 

| Cost line  | Planning range  | Basis |
| :---- | :---- | :---- |
| **Engineering / implementation support**  | ₦2.5m–₦5.0m  | Founder-led build with limited external  engineering support over three months. |
| **Product design, QA and field testing**  | ₦0.8m–₦1.5m  | Short design/QA engagement and test  devices / field sessions. |
| **Pilot onboarding and travel**  | ₦0.6m–₦1.5m  | Data import, training and a small number of customer-site visits. |
| **Cloud, maps and basic tools**  | ₦0.2m–₦0.5m | Early production infra and monitoring; USD  subscriptions excluded from FX conversion  precision. |
| **Legal, privacy and API due diligence**  | ₦0.5m–₦1.5m  | Contract review, DPA/privacy advice and  NIPOST commercial clarification. |
| **Contingency**  | ₦0.8m–₦1.5m  | Unexpected integration, migration or testing costs. |
| **Total**  | ₦5.4m–₦11.5m  | Scenario only; revise after team plan and  supplier quotations. |

**8.7 Paystack billing considerations** 

Paystack’s published Nigerian local transaction pricing is 1.5% \+ ₦100, capped at ₦2,000 per local transaction, with the  ₦100 waived below ₦2,500. Its Subscriptions API supports recurring plans, but for Nigeria the documented recurring  channels are cards and Direct Debit; failed subscription charges are not retried automatically. Offer bank-transfer  invoices and manual reconciliation as a practical fallback, with clear payment reminders and dunning. \[20\]\[21\] 

**9\. Market sizing approach and first-customer acquisition** 

**9.1 What the public market evidence can and cannot tell us** 

The broad facilities-management services market is large, but public market estimates are not a reliable SaaS market  size. Mordor Intelligence reports a Nigeria FM market estimate of US\$27.76bn for 2025 and US\$31.04bn for 2026 using  its proprietary framework. This is a services-spend estimate, not spend on asset-register or work-order software; its  definitions and assumptions should be inspected before using it in an investor deck. \[11\] 

A PwC 2024 MSME survey reports that the NBS 2020 count was 39.65m MSMEs, with micro enterprises representing  96.9% and SMEs 3.1%. This is broad business context, not a count of FM providers or multi-site asset operators. The  survey interviewed 567 MSMEs and discusses digitisation, but it does not establish willingness to pay for this product.  \[19\]

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 16   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**MARKET SIZING CONCLUSION** 

Do not use the headline FM services market as the SaaS TAM. Public evidence does not yet establish the number of Nigerian FM companies with the site count, asset intensity, budget and software readiness required. Use an  account-based beachhead model and validate the prospect list manually. 

**9.2 Account-based beachhead model (assumptions, not market facts)** 

Build a first working list of 60 qualified organisations across Lagos, Abuja and Port Harcourt. This is an initial sales  coverage target, not an estimate of the total market. Prioritise independent FM service providers managing multiple  client sites, then select multi-location operators whose procurement is accessible. Use public company websites,  business directories, FM sector networks and referrals to qualify each account. 

| Illustrative account capture  | Paying accounts  | Blended monthly revenue /  customer | Annualised recurring revenue |
| :---- | :---- | :---- | ----- |
| **5% of 60-account list**  | 3  | ₦190,000  | ₦6,840,000 |
| **10% of 60-account list**  | 6  | ₦190,000  | ₦13,680,000 |
| **20% of 60-account list**  | 12  | ₦190,000  | ₦27,360,000 |
| **30% of 60-account list**  | 18  | ₦190,000  | ₦41,040,000 |

The ₦190,000 blended monthly contract value is a simplifying assumption anchored on the proposed Growth tier.  Actual mix may be higher or lower. At 10–20% capture, the list supports only a small initial business; additional  verticals or geographies would be needed for larger scale. This is a practical beachhead revenue model, not  TAM/SAM/SOM in the conventional national-market sense. 

**9.3 Prospecting and first 10 customers without paid advertising** 

 Use a named-account approach. Start with 30–60 FM providers and operators, qualify site count and maintenance  workflow, and approach the MD/COO/Head of FM directly. 

 Ask for a 45-minute workflow review, not a generic product demo. Request one anonymised asset register, one  inspection form and one monthly client report before the meeting. 

 Offer a paid portfolio/site-register diagnostic: map current sources, identify data gaps and show the cost of  manually producing the report. Credit part of the diagnostic fee toward onboarding if the customer proceeds.  Get one pilot site live quickly and invite the buyer to a weekly review. Turn before/after workflow measures into a case study with customer approval. 

 Build referral relationships with independent FM consultants, maintenance contractors, property accountants,  equipment suppliers and FM associations. Pay referral fees only under a written transparent policy.  Use direct email, LinkedIn and WhatsApp outreach with a specific pain hypothesis and a short screen recording;  avoid broad social campaigns until the positioning is validated. 

 Ask early customers for introductions to two peer FM firms after measurable results. Keep the customer’s data  private and anonymised in demos. 

**10\. Customer discovery and paid pilot plan** 

**10.1 Discovery sample** 

Conduct 15–20 structured conversations across at least 8 organisations before completing the MVP. In each  organisation, speak to the economic buyer and at least one operational or field user. Avoid interviewing only founders or technology-friendly contacts; the people compiling site reports and closing maintenance jobs must be included.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 17   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Interview group  | Target count  | What to learn |
| ----- | :---- | :---- |
| **FM owner / MD / COO / Head of Operations**  | 6–8 | Business impact, client contract pressures,  buying authority, budget and purchasing  triggers. |
| **FM manager / site supervisor**  | 5–7 | Actual site register, inspection, work-order  and reporting workflow; time spent; failure  points. |
| **Technician / contractor**  | 3–5  | Phone/network constraints, evidence  capture, adoption concerns and job handoff. |
| **Client-side property / facilities lead**  | 2–3  | What reports, audit evidence and service level proof they expect from providers. |

**10.2 Discovery questions** 

1\. Walk me through how you take over a new site or FM contract. What information must be captured before the  team can operate? 

2\. Where is the current list of managed properties and facilities kept? Who updates it, and when was it last checked? 3\. How do you register each physical asset, assign an ID, record its location and know who is responsible for it? 4\. What happens when a client reports a fault? Show me the last real example from the first message to closure. 5\. How are inspections scheduled, performed and followed up when a defect is found? 

6\. How do technicians receive jobs and send evidence that the work was completed? 

7\. How do you handle a job that is overdue, repeated or disputed by the client? 

8\. How long does it take to produce the last monthly report for a client? Which parts require manual chasing or  consolidation? 

9\. What is the financial or contractual impact of a missed inspection, delayed repair, lost asset or poor report? Give a  recent example and approximate cost. 

10\. Which software, spreadsheets, chat groups, paper forms or accounting tools are used today? What is working well enough that you would not replace it? 

11\. What has the company already tried to digitise this workflow? Why did staff adopt it or abandon it? 12\. Who signs off on a new tool? Which budget would it come from, and what security or procurement checks are  required? 

13\. What would make a new system difficult for technicians or site teams to use? Consider connectivity, devices,  training and language. 

14\. Would you pay to digitise one portfolio and run the inspection/work-order workflow for six weeks? What result  would justify renewing? 

15\. What price range would be acceptable if the system reduced report preparation time, improved job closure  evidence and gave you one current asset register? What do you currently spend instead? 

Do not lead with “Would you use this app?” or ask buyers to praise a concept. Ask them to demonstrate a past event,  show the current artefacts and quantify the time/cost. Record the evidence separately from the user’s interpretation. 

**10.3 Paid pilot design**

| Pilot element  | Recommended design |
| :---- | :---- |
| **Duration**  | 6 weeks: 1 week baseline and setup, 4 weeks of live use, 1 week  results and renewal decision. |
| **Scope**  | 1–3 client sites, 100–300 assets, 1 operations manager, 2–4 site  supervisors and 5–15 technicians / contractors. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 18   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Pilot element  | Recommended design |
| :---- | :---- |
| **Commercial terms** | Paid pilot fee of ₦350,000–₦750,000, scoped by data condition and  field capture. Credit up to 50% of pilot fee against annual  prepayment if converted within 30 days. |
| **Onboarding** | Import assets/properties, map sites, configure 2–3 inspection  templates, create roles, train users and agree work-order closure  evidence. |
| **Baseline** | Capture current register completeness, weekly maintenance  requests, time to assign/close jobs, on-time inspections, proof-of work coverage and report-preparation hours. |
| **Post-pilot**  | Compare the same measures, interview buyer and users, calculate  implementation/support effort, and present a conversion proposal. |

**10.4 Pilot success measures** 

| Measure  | Suggested target after 4 live weeks |
| :---- | :---- |
| **In-scope asset records**  | At least 85% of the agreed pilot assets have a site, asset tag, category, condition/status and current custodian. |
| **Site location quality**  | At least 90% of pilot sites have a readable address and usable GPS  pin; postcode is an optional separately reported field. |
| **Workflow adoption**  | At least 75% of pilot work requests are entered or transcribed into  the system and receive an assigned owner. |
| **Evidence quality**  | At least 70% of work orders closed in the pilot include the agreed  evidence and closure notes. |
| **Inspection discipline**  | At least 80% of scheduled pilot inspections are recorded by the due  date, with exceptions visible. |
| **Reporting effort**  | Monthly report preparation time falls by at least 30% against the  measured baseline. |
| **Commercial validation** | Buyer agrees to a recurring subscription at or above   ₦150,000/month, or explicitly identifies a paid configuration that  would justify the lower tier. |

Targets should be adjusted to the customer’s baseline and operating context before the pilot starts. They are not public industry benchmarks. A pilot that “uses the app” but does not improve a measured result is not validation. 

**10.5 Proceed, pivot or stop**

| Decision  | Evidence threshold |
| :---- | :---- |
| **Proceed with the FM-provider product** | At least 2 paid pilots from independent organisations; 1 or more  customers converts to a recurring plan; at least 3 target accounts  share the same core workflow; field users can complete tasks  without daily founder intervention; measured report or execution  improvement is demonstrated. |
| **Pivot within property operations** | Buyers value property/asset digitisation but maintenance execution  is not their budget priority; investigate paid asset-register service,  site audit and compliance reporting as the paid entry product. |
| **Pivot to adjacent operator segment** | FM companies have low willingness to pay or already use an  effective CMMS; multi-branch businesses show stronger budget and  faster proof of value. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 19   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Decision  | Evidence threshold |
| :---- | :---- |
| **Stop / pause major build** | After 15–20 credible interviews and at least 5 qualified offers, no  buyer will pay for a pilot; buyers require a broad estate/rent/ERP  suite; adoption depends on expensive field labour that cannot be  priced profitably; or NIPOST terms make the postcode use case   commercially unworkable and no non-postcode workflow has  independent value. |

**11\. 90-day execution roadmap** 

The 90 days should produce evidence of demand and a usable paid-pilot product, not a feature-complete platform.  NIPOST approval is a parallel dependency, not the critical path for core asset and maintenance workflows. 

| Phase / timing  | Deliverables  | Dependencies and risks  | Exit criteria |
| :---- | :---- | :---- | :---- |
| **1\. Discovery & feasibility  Days 1–15** | 15–20 interviews scheduled; current  workflow maps; 60-account prospect  list; clickable workflow prototype;  NIPOST access request; data/privacy  checklist; scope locked. | Access to FM decision-makers and  real operating documents. NIPOST  public docs do not provide full  developer detail. | At least 5 buyers confirm the same  costly workflow; 3 credible pilot  candidates; one real register and  report available for prototype testing. |
| **2\. MVP build  Days 16–55** | Tenant/org setup; client portfolio and  site register; map and GPS capture;  asset register; custodian; inspections;  requests/work orders; activity   history; basic dashboards; CSV  import; role/RLS testing. | Scope discipline, data model, reliable  hosting and permission design.  Postcode adapter can be stubbed  while approval is pending. | One end-to-end workflow runs on a  test portfolio; tenant-isolation tests  pass; imports reconcile; pilot-ready   mobile screens work on target  phones. |
| **3\. Paid pilot onboarding  Days 56–75** | Onboard 1–2 customers; migrate  agreed records; train users; baseline  metrics; weekly adoption review; fix  high-impact bugs; test API only if  authorised. | Customer data quality, staff adoption, device/network constraints and  NIPOST approval if postcode   validation is in pilot scope. | At least 70% of agreed tasks flow  through product; pilot measures  available; no unresolved critical  permission/security issue. |
| **4\. Commercial launch  Days 76–90** | Results review; subscription   conversion; case study approved by  customer; onboarding playbook; tier  and usage limits refined; outbound  account list; release checklist. | Customer willingness to pay;   measured value; support load and  renewal proposal. | At least 1–2 paying recurring   customers or a clear evidence-backed pivot; subscription and onboarding  unit economics understood. |

**11.1 Critical path and scope discipline** 

 Days 1–15: request NIPOST documentation, commercial terms and test credentials early; do not wait for them to  finalise the asset workflow. 

 Days 16–55: develop the postcode integration interface behind a feature flag. Complete manual address/GPS entry  and validation-state UX first. 

 Days 56–75: if production approval is not available, keep postcode validation disabled or use user-supplied  postcode values with an explicit “not validated by NIPOST” label. Never simulate an API result.  No native apps, AI prediction, procurement modules, rent accounting, public address search or bulk national data  sync during the first 90 days. 

 Use one real customer portfolio for design decisions. Do not build an enterprise feature for a prospect who has not committed to a paid pilot.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 20   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**12\. Risk register, investment gates and longer-term opportunities** 

**12.1 Risk register** 

| Risk  | Likelihood / impact  | Mitigation |
| ----- | :---- | :---- |
| **NIPOST API/commercial terms delay or  restrict use**  | High / High | Build fallback-first; request written commercial permission before use; isolate API adapter and  cache policy; do not make launch dependent on postcode. |
| **Postcode coverage or location data   incomplete / changing**  | Medium–High / High | Allow unverified site entry; collect GPS and  readable address; field verification; show  confidence/source/timestamp; support  corrections. |
| **Buyer pain is not budgeted / willingness to  pay weak**  | High / High  | Paid pilot before full build; quantify current  cost; stop if no conversion evidence. |
| **Local competition already covers asset /  maintenance functions**  | High / High | Focus on multi-client FM-provider workflow;  benchmark in live demos; differentiate only  where customer evidence supports it. |
| **Implementation and data cleaning consume  margin**  | High / Medium–High | Standard CSV templates; charge onboarding;  set asset/site scope; record hours; make  physical survey a separate service. |
| **Technicians do not adopt the app**  | Medium–High / High | Mobile-first; short forms; offline drafts;  minimal required fields; supervisor review;  WhatsApp intake only after core adoption. |
| **Customer/client data leak across tenants**  | Low–Medium / Critical | RLS, tenant isolation tests, least privilege,  signed media URLs, audit logging, security  review and incident playbook. |
| **NDPA registration, DPA or cross-border  transfer non-compliance**  | Medium / High | Map roles and data flows; engage Nigerian  privacy counsel/DPCO; execute DPA; check  NDPC registration status and transfer   mechanism before production. |
| **Customisation and support creep**  | High / Medium | Productised tiers, documented scope, paid  change requests, support hours and quarterly  feature prioritisation. |
| **NGN inflation / FX impacts imported services**  | High / Medium | Price in NGN with annual review; track USD  infra and API costs; include usage limits and  fair price-change notice. |
| **Weak connectivity at some sites**  | Medium / Medium | PWA/offline draft support, retry queue,  compress media, clear sync status and conflict  resolution. |

**12.2 Investment gates**

| Gate  | Minimum evidence |
| :---- | :---- |
| **Before substantial MVP spend** | 15–20 buyer/user interviews across at least 8 firms; five  demonstrated examples of repeat asset/work-order/reporting pain;  three pilot candidates; one accessible source asset register. |
| **Before extending beyond core MVP** | At least two paid pilots or one paid pilot plus a second signed  commercial commitment; users completing core work without daily founder rescue. |
| **Before commercial postcode launch** | Written NIPOST authorisation for paid SaaS use, customer display,  storage/cache, support, sublicensing/subcontracting (if applicable),  price, rate limits and termination/deletion. |

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 21   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

| Gate  | Minimum evidence |
| :---- | :---- |
| **Before public launch** | No unresolved critical tenant-isolation/security issue; clear  terms/privacy/DPA; export/offboarding process; support and  incident playbooks; measured onboarding effort and gross margin. |

**OVERALL RECOMMENDATION: CONDITIONAL GO** 

Invest in customer discovery and a small, paid-pilot MVP. Do not fund a broad geospatial platform or make  postcode API access the reason to build. Release further development spend only when real FM buyers pay for the asset-to-work-order-to-report workflow. 

**12.3 Long-term expansion opportunities** 

| Opportunity  | Commercial fit  | Timing |
| :---- | :---- | :---- |
| **Client SLA and portfolio reporting** | Natural paid add-on: client-specific reports,  missed-SLA alerts, completion proof and  downloadable audit packs. | First expansion after core adoption. |
| **Preventive maintenance schedules and  standard templates** | Adds repeat usage and reduces emergency-only workflow; templates can be organised by  equipment class and property type. | Phase 2\. |
| **Contractor portal and performance  scorecards** | Lets FM firms coordinate external vendors and  monitor response, completion, repeat defects  and documentation. | Phase 2\. |
| **Asset lifecycle / replacement planning** | Maintenance history and age/condition can  support renewal budgets and replacement  recommendations. | After data quality and history mature. |
| **Geographic portfolio analytics** | Site clusters, service coverage, technician  allocation and incident hotspots; useful for  larger portfolios. | After sufficient geospatial density. |
| **Compliance inspections and evidence packs** | Potential recurring revenue in sectors with  scheduled checks and audit records; needs  sector-specific validation. | Choose one vertical based on pilot demand. |
| **IoT integration and predictive maintenance** | Can increase contract value but requires  sensors, connectivity, data history and  specialist support. | Later; only with a paying partner. |
| **Site selection and location intelligence** | A separate adjacent product opportunity, but  needs authoritative third-party datasets and  clear decision workflows. | Do not include in initial roadmap. |

**Appendix A. First 30 days: action checklist** 

1\. Build a list of 60 named prospects and qualify whether each is an FM service provider, portfolio manager or multi site operator. 

2\. Book 15–20 interviews; prioritise owners/COOs/Heads of FM and the people compiling site reports. 3\. Collect anonymised examples of a property register, asset list, inspection form, work order and monthly client  report. 

4\. Ask at least five prospects to quantify report-preparation time, missed/late work and repeat maintenance costs. 5\. Present the prototype to three buyers; ask them to nominate one real pilot site and provide a data export.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 22   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

6\. Submit the NIPOST organisation integration request and ask for documentation, pricing, service limits and written  SaaS-use permissions. 

7\. Confirm privacy obligations, DPA, data retention, controller/processor roles and any NDPC registration category  with qualified counsel/DPCO. 

8\. Lock the MVP around the workflow in Section 3.2; create explicit out-of-scope list and change-request policy. 9\. Offer a paid 6-week pilot; do not offer free bespoke implementation as the main validation signal. 10\. Review the evidence weekly and stop or pivot if the purchase trigger is not repeatable. 

**Appendix B. Core acceptance tests for pilot readiness** 

 A user from Organisation A cannot read, modify, export or infer any row belonging to Organisation B, including  through object storage URLs and search endpoints. 

 A site can be saved with address and GPS when NIPOST is down or no postcode exists.  An asset can be moved to another site without losing prior location history. 

 An asset can be assigned to a custodian and the previous custodian/handover remains in the history.  An inspection can generate a follow-up request or work order while retaining the original inspection record.  A technician can update a job, attach evidence and submit for supervisor closure; all key events have actor and  timestamp. 

 A manager can filter overdue work and inspections by client, site, priority and time period.  A client read-only user can access only the sites and reports explicitly assigned to that client.  A customer can export its properties, assets, inspection records and work orders in usable CSV/PDF formats.  All third-party API results are tagged with source, date and permission/retention status; no unapproved bulk  extraction or postcode database replication exists. 

**Appendix C. Research notes and limitations** 

 Research cut-off: 30 October 2026\. The Nigeria National Digital Alphanumeric Postcode System is very new  (launched 1 October 2026); public documentation and commercial terms may evolve quickly.  No live API credentials or authenticated developer documentation were available for independent technical  testing in this research. The report therefore describes public access routes and terms, not a tested API  integration. 

 No primary customer interviews were conducted for this report. Customer pain, budget, buyer priorities and  conversion thresholds are hypotheses for discovery. 

 Competitor analysis relies on public vendor pages and descriptions, not hands-on product tests, customer  references, audited user counts or verified market share. 

 Public market-size figures relate to facility-management services, not property/asset SaaS revenue. The account based model is deliberately labelled as a planning scenario, not a national TAM estimate. 

 Pricing, margins, implementation budget and revenue scenarios are Cloud Crafters planning assumptions. Obtain  supplier quotes, validate willingness to pay and measure actual support/hosting costs during pilots.  Legal analysis is an operational summary, not legal advice. Obtain written NIPOST permission and advice from  qualified Nigerian privacy and technology counsel before processing production customer data or marketing  postcode validation. 

**Appendix D. Sources** 

Primary sources are listed first. Vendor pages are primary sources for their own stated features and prices, not independent  evidence of performance or adoption. Source content and availability were reviewed on 30 October 2026\. 

**\[1\] NIPOST National Digital Postcode System homepage.** Nigeria Postal Service; Current; launched 1 Oct 2026\. Open source — Official overview; organisation/developer route and integration modes.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 23   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**\[2\] Federal Government Launches National Digital Alphanumeric Postcode System.** Federal Ministry of  Communications, Innovation and Digital Economy; 1 October 2026\. Open source — Official launch release; GIS, 11- character code and intended uses. 

**\[3\] Frequently asked questions.** NIPOST Postcode; Current. Open source — Building-level identity, missing-building  request route and address distinction. 

**\[4\] Build with the national postcode system / Integration team.** NIPOST Postcode; Current. Open source —  Organisation registration and integration interest form. 

**\[5\] Acceptable Use Policy.** NIPOST Postcode; Current. Open source — Permitted use, API abuse, retention,  commercial exploitation, data protection and security restrictions. 

**\[6\] Terms and Conditions.** NIPOST Postcode; Current. Open source — Licence, API terms, non-guarantees, fees,  personal data responsibilities and termination. 

**\[7\] For Innovation Hubs.** NIPOST Postcode; Application window October 2026\. Open source — Separate hub programme eligibility and closing date. 

**\[8\] FG Launches “Build on Nigeria’s Postcode” to Showcase Digital Solutions.** Federal Ministry of  Communications, Innovation and Digital Economy; 21 October 2026\. Open source — Official builder showcase  announcement. 

**\[9\] Nigeria Data Protection Act, 2023\.** Nigeria Data Protection Commission; 2023; current official Act page. Open  source — Primary legislation; data protection, security and breach notification. 

**\[10\] Guidance Notice: Registration of Data Controllers and Data Processors of Major Importance.** Nigeria Data  Protection Commission; 2025/2026 publication. Open source — NDPC registration guidance; confirm current  interpretation with counsel/DPCO. 

**\[11\] Nigeria Facility Management Market \- Statistics, Report Analysis.** Mordor Intelligence; Current 2026 page.  Open source — Third-party proprietary services-market estimate and list of sector companies; not SaaS TAM. 

**\[12\] Pricing.** Gate Africa; Current 2026 page. Open source — Published estate-management plan pricing and feature  limits. 

**\[13\] Property management built for Nigeria.** Ledge; Current 2026 page. Open source — Public estate/property  manager features and promotional free offer. 

**\[14\] The operating system for modern property teams.** Hutstack; Current 2026 page. Open source — Public  positioning and property/estate operations features. 

**\[15\] The operating system for Nigeria’s estates / Investor page.** EstateOps; Current 2026 page. Open source —  Vendor-stated asset and maintenance features plus self-reported traction. 

**\[16\] RateMe CMMS and Watchtower product pages.** RateMe Ltd; Current 2026 page. Open source — Local CMMS,  assets, custody and maintenance product descriptions. 

**\[17\] CaretakerPro.** Caretakers NG; Current 2026 page. Open source — Nigerian property management workflow and  role descriptions. 

**\[18\] Pricing.** MaintainX; Current 2026 page. Open source — International CMMS pricing benchmark. 

**\[19\] MSME Survey 2024\.** PwC Nigeria; July 2024\. Open source — SME context; survey and NBS 2020 MSME population  cited in the report. 

**\[20\] Transactions pricing.** Paystack Support; Edited 15 September 2026\. Open source — Current Nigeria transaction  pricing. 

**\[21\] Subscriptions API documentation.** Paystack Developers; Current. Open source — Recurring billing channels and failed-charge retry behaviour. 

**\[22\] Pricing & Fees.** Neon; Current 2026 page. Open source — Managed database/auth/storage plan baseline. **\[23\] PostGIS: Geo queries.** Neon Docs; Current. Open source — PostGIS enablement and schema guidance. **\[24\] Vercel Pro Plan.** Vercel Docs; Updated 3 February 2026\. Open source — Pro plan platform price and usage credit.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 24   
**CLOUD CRAFTERS TECHNOLOGIES \| PRODUCT & COMMERCIAL STRATEGY** 

**\[25\] Flexible pricing for maps, data processing & storage.** MapTiler; Current 2026 page. Open source —  Commercial tile and map service plan/usage pricing. 

**\[26\] Deploying Next.js.** Next.js Documentation; Updated 25 March 2026\. Open source — Supported deployment  options. 

**\[27\] Introduction \- MapLibre GL JS.** MapLibre; Current. Open source — Open-source map renderer and vector tile  integration model. 

**Final recommendation** 

Cloud Crafters should proceed to a focused validation and paid-pilot stage, not a full platform build. The opportunity is credible if a specific class of FM provider will pay to connect the site register, asset custody, inspections, work orders  and client reporting. The postcode system is timely enabling infrastructure, but it is not the product’s core moat and  cannot be treated as freely reusable national data. 

Invest only when multiple FM buyers confirm the same recurring workflow, at least two commit to paid pilots, and  pilots measurably improve asset-register completeness, work-order evidence or client reporting. Otherwise, change  the entry point or stop.

CONFIDENTIAL WORKING STRATEGY • 30 OCTOBER 2026 • PAGE 25 