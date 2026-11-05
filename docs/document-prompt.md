# Task: Establish the Security and Data Protection Documentation Foundation

## Objective

Establish a clear, comprehensive and enforceable security documentation foundation for the Cloud Crafters Property & Asset Intelligence platform.

We are adopting a six-document security documentation approach covering authentication, application security, database tenant isolation, environment configuration, threat modelling and data protection.

The purpose is to give our AI coding agents, developers and future contributors explicit security requirements they must follow when building, modifying, testing and deploying the application.

**This is a documentation and security requirements task. Do not redesign or change our existing application architecture. Do not implement a new authentication or security system as part of this task.**

---

## 1. First, inspect the existing project

Before creating anything, inspect the existing project and relevant documentation, including:

- PRD and functional requirements.
- Architecture documentation and architecture decision records.
- BMAD project documents, epics and stories.
- `AGENTS.md` and existing agent instructions.
- Existing Laravel and React/Inertia scaffold.
- Existing authentication implementation inherited from the Laravel starter kit.
- Database configuration and any existing migrations.
- Existing storage configuration.
- Existing API and authorisation decisions.
- Existing development, staging and production environment configuration.

Use the existing project documentation as the primary source of truth.

Do not invent implementation details or assume that a planned feature already exists.

Identify existing security requirements, decisions, implementation status and outstanding gaps before writing the documents.

If existing documentation conflicts, identify the conflict and recommend a resolution rather than silently choosing one.

## 2. Create these six documents

Create a dedicated security documentation directory consistent with the existing project structure.

Recommended documents:

### Document 1: `AUTH.md`

**Authentication and access management**

Document how users authenticate and how access is granted or revoked.

Cover:

- User registration and account creation.
- Invitation-based onboarding, where applicable.
- Login and logout.
- Password hashing and password reset.
- Session management and expiration.
- Account verification and recovery.
- Account suspension and deactivation.
- Organisation membership.
- Active organisation selection and context.
- Application roles and permissions.
- Role assignment and revocation.
- Access rules for organisation administrators, operations managers, supervisors and technicians.
- Authentication requirements for API endpoints.
- Authentication and authorisation for future integrations.

Clearly distinguish authentication from authorisation.

Do not assume that every user can register publicly. Document the intended onboarding model based on the existing PRD and architecture.

### Document 2: `SECURITY.md`

**Application security standards**

Define the security rules that all developers and AI coding agents must follow.

Cover:

- Secure coding principles.
- Server-side input validation.
- Output escaping and XSS prevention.
- SQL injection prevention.
- CSRF protection.
- Authentication and authorisation checks.
- API security.
- Rate limiting.
- File upload validation.
- Private file access.
- Secure error handling.
- Protection against mass assignment.
- Safe logging and audit events.
- Dependency and package security.
- Secret management.
- Secure HTTP and production configuration.
- Protection against insecure direct object references.
- Security testing requirements.

Include explicit prohibitions:

- Never hardcode credentials, API keys, passwords or secrets.
- Never expose private customer information in logs or error messages.
- Never rely exclusively on frontend checks for permissions.
- Never trust organisation IDs supplied by users without validating the authorised organisation context.
- Never expose private storage objects through public URLs.
- Never bypass established application authorisation or database isolation.
- Never weaken security controls simply to make a feature easier to implement.

Include a section titled **Mandatory Rules for AI Coding Agents** with clear, direct instructions.

### Document 3: `RLS-POLICIES.md`

**PostgreSQL Row-Level Security and tenant isolation**

This document is especially important because the application is a multi-tenant SaaS platform.

Document how organisation-level data isolation must work.

Our established hierarchy is:

Organisation → Client Account → Property → Zone → Asset.

Other related records include inspections, maintenance requests, work orders, technicians, assignments, attachments and audit events.

Cover:

- Tenant ownership and `organisation_id`.
- Which tables are tenant-scoped.
- Which tables are global/reference data, if any.
- PostgreSQL RLS requirements.
- Policy design for SELECT, INSERT, UPDATE and DELETE.
- How the active organisation context is established.
- How Laravel middleware and application services enforce tenant scope.
- How tenant context is established and cleared in background jobs.
- Connection pooling and session-variable handling.
- Database roles and permissions.
- How application database credentials interact with RLS.
- Administrative access and any approved exceptions.
- File and attachment tenant isolation.
- Tenant isolation in API endpoints and exports.

Explicitly distinguish:

1. Application roles, such as organisation administrator, operations manager, supervisor and technician.
2. PostgreSQL database roles, which control database-level access.

Do not confuse role-based application permissions with PostgreSQL RLS.

**Critical security requirement:** The application database role must not have privileges that allow it to bypass the RLS policies intended to protect tenant data.

Include a table identifying the expected tenant ownership and access considerations for each important data entity.

Also document the required security tests:

- Organisation A cannot retrieve Organisation B's records.
- Organisation A cannot update or delete Organisation B's records.
- Changing an ID in a URL or API request cannot bypass tenant isolation.
- Raw SQL access under the application database role respects RLS.
- Background jobs cannot leak data between organisations.
- File downloads and signed URLs cannot expose another organisation's files.
- Tenant context is not accidentally retained between requests or pooled connections.

Do not claim these protections are implemented unless verified in the actual code and database.

### Document 4: `ENV.md`

**Environment variables and secrets management**

Document the environment configuration required by the application.

Cover:

- Local development.
- Staging.
- Production.
- Database configuration.
- Application encryption key.
- Authentication/session configuration.
- Object storage configuration.
- RustFS local storage.
- Cloudflare R2 production storage.
- Queue configuration.
- Cache configuration.
- Mail configuration, if applicable.
- Monitoring and error reporting.
- External API credentials, when introduced.

For each variable, document:

- Variable name.
- Purpose.
- Whether it is required or optional.
- Which environments use it.
- Whether it contains sensitive information.
- Where its value should be stored.
- Whether it is safe to expose to frontend code.

**Never put real credentials, secrets, tokens or passwords in this document.**

Document secret rotation, access restrictions and safe handling procedures.

The `.env.example` file must contain placeholders only, never real credentials.

### Document 5: `THREAT-MODEL.md`

**Security threat model**

Identify what the platform must protect, who might attack it, how attacks could happen and what the consequences could be.

Use a practical threat-modelling approach suitable for an early-stage SaaS platform.

Cover:

**Assets to protect:**
- Customer and organisation records.
- Property and asset information.
- Inspection records.
- Maintenance requests and work orders.
- Technician information.
- Photographs and uploaded documents.
- Location information.
- Authentication credentials and sessions.
- API credentials.
- Database records.
- Backups and exports.

**Potential threat actors:**
- Unauthorised external users.
- Malicious or compromised authenticated users.
- Employees accessing information beyond their permissions.
- Compromised administrator accounts.
- Malicious insiders.
- Attackers exploiting application vulnerabilities.
- Compromised third-party services or credentials.

**Important threat scenarios:**
- Cross-tenant data exposure.
- Account takeover.
- Stolen or reused passwords.
- Privilege escalation.
- Insecure API access.
- Private document or photograph exposure.
- Malicious file uploads.
- SQL injection and other common application attacks.
- Insecure background jobs.
- Exposed secrets.
- Data leakage through reports and exports.
- Backup compromise.
- Unauthorised access to technician location information.

For each significant threat, document:

- What could happen.
- The asset affected.
- Likely attack path.
- Potential business impact.
- Existing controls, if verified.
- Required additional controls.
- How the control should be tested.
- Current status and outstanding work.

Prioritise risks based on their potential impact and likelihood.

Do not present hypothetical controls as existing protections.

### Document 6: `DATA-PROTECTION.md`

**Data protection, privacy and retention**

Document how the platform handles personal, operational and customer information.

Cover:

- Categories of information collected.
- Personal data and business data.
- Technician and employee information.
- Customer contact details.
- Property and asset records.
- Photographs and documents.
- Location information.
- Purpose of processing.
- Access and disclosure rules.
- Data minimisation.
- Data retention and deletion.
- Backups and retention limitations.
- Data exports.
- Data subject access and deletion requests.
- Customer data ownership.
- Controller and processor responsibilities.
- Data processing agreements.
- Third-party data processors.
- Cross-border data transfers.
- Security incident and breach handling.

Address relevant Nigerian Data Protection Act (NDPA) considerations without making unsupported claims about legal compliance.

Clearly identify matters requiring legal or data protection professional review.

Do not invent retention periods, legal bases or contractual commitments. Mark decisions requiring confirmation as outstanding.

---

## 3. Integrate the documents into our AI agent workflow

The documents must become part of the project's actual development process, not merely reference material.

Update `AGENTS.md` or the appropriate existing agent instruction file to require agents to:

1. Read the relevant security documentation before implementing authentication, user permissions, tenant-scoped data, APIs, uploads, exports or other security-sensitive functionality.
2. Follow the mandatory rules in `security.md`.
3. Respect the tenant isolation requirements in `rls-policies.md`.
4. Never introduce secrets into source code, documentation, commits or frontend bundles.
5. Include relevant security tests when implementing or modifying security-sensitive functionality.
6. Update security documentation when an implementation decision or security requirement changes.
7. Report security assumptions, gaps and unverified controls rather than silently ignoring them.

Avoid duplicating the full content of the six documents inside `AGENTS.md`. Link to them and explain when they must be consulted.

## 4. Connect the documents to our existing BMAD workflow

Review the existing BMAD documentation structure and integrate the security documents appropriately.

Where useful:

- Reference security requirements from the PRD.
- Reference tenant isolation requirements from the architecture documentation.
- Link relevant security requirements from Epic 1 and its stories.
- Identify security-related acceptance criteria that future implementation stories must satisfy.

Do not rewrite the PRD, epics or existing stories unnecessarily.

Do not change frozen story requirements without identifying the proposed change and its reason.

## 5. Important architectural constraints

All recommendations must respect our existing architecture.

Do not introduce a new technology stack or replace any established decision.

Our current architecture includes:

- Laravel backend.
- React and TypeScript.
- Inertia.js.
- PostgreSQL and PostGIS using Neon.
- Laravel authentication and application services.
- PostgreSQL Row-Level Security for tenant isolation.
- Laravel queues and scheduler.
- RustFS for local S3-compatible object storage.
- Cloudflare R2 for production object storage.
- Versioned REST API.
- Modular monolith architecture.
- Docusaurus for the independently deployed documentation website.

Do not introduce microservices, a new authentication provider, a new database, a different storage provider or additional infrastructure as part of this task.

## 6. Accuracy and documentation standards

This is a critical requirement.

Every security document must distinguish between:

- **Requirement:** What the system must do.
- **Decision:** What has been agreed architecturally.
- **Implemented:** What currently exists in the code.
- **Verified:** What has passed an appropriate test or inspection.
- **Outstanding:** What still needs to be implemented, tested or decided.

Use these distinctions consistently.

Do not claim that RLS is enforced merely because an RLS policy is described in documentation.

Do not claim that private storage works merely because a bucket is configured as private.

Do not claim authentication or permissions are secure without inspecting the implementation.

Do not invent environment variables, database tables, roles, endpoints or security controls. Verify them or label them as proposed.

Use clear, practical English. Avoid unnecessarily complex security jargon.

## 7. Scope restrictions

This task is primarily about creating and integrating security documentation.

Do not:

- Rebuild the authentication system.
- Implement the entire RLS architecture.
- Introduce new roles or permissions without documenting the proposal first.
- Change database schema unnecessarily.
- Provision production infrastructure.
- Introduce new security vendors or paid services.
- Add unnecessary packages.
- Expand the MVP.
- Modify unrelated application features.
- Mark unimplemented security requirements as complete.

If you identify an immediate, critical security vulnerability in the existing scaffold, report it separately with a recommended fix. Do not make unrelated code changes without approval.

## 8. Final deliverables and completion report

When finished, provide a concise report containing:

1. The six documents created, with their exact paths.
2. Existing files updated, including `AGENTS.md` and any BMAD references.
3. Security requirements already covered by existing architecture or implementation.
4. Important security gaps identified.
5. Requirements that remain unimplemented.
6. Decisions that require my approval.
7. Any conflicts discovered in the existing documentation.
8. Confirmation that no architectural changes or unrelated feature implementations were made.

Also provide a prioritised list of the security implementation work that should be addressed before real customer data is introduced.

**Definition of done:** We have six coherent, project-specific security documents, our AI coding agents are instructed to follow them, they agree with our existing architecture and BMAD documentation, and they accurately distinguish documented requirements from implemented and verified security controls.

Do not proceed to a separate security implementation phase without reporting the findings and receiving approval.