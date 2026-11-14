# AD-013 — System UI/UX & Interaction Design

**Status:** Finalised — all 12 design sections approved; implementation and QA evidence pending  
**Date:** 11 November 2026  
**Related:** AD-001–AD-012

## Approved discussion roadmap (12 sections)

The following records the **12 approved design sections** and their consolidated scope. Implementation details must respect the relevant authoritative functional ADs; do not create ad hoc sub-workstreams.

| No. | Design area | Coverage |
| --- | --- | --- |
| 01 | Visual Design System | Typography, colours, spacing, borders, elevation, icons, anti-slop principles |
| 02 | Application Shell & Navigation | Sidebar, header, breadcrumbs, active navigation, user context, mobile navigation |
| 03 | Page Layout & Information Density | Page headers, content width, sections, whitespace, information hierarchy, responsive layouts |
| 04 | Buttons & Action Controls | Primary/secondary actions, icon buttons, placement, sizing, disabled states, action feedback |
| 05 | Forms & Data Entry | Inputs, dropdowns, autocomplete, dependent fields, validation, required fields, multi-row entry |
| 06 | Tables & Data Presentation | Financial tables, sorting, filtering, pagination, responsive cards, horizontal scrolling, numeric formatting |
| 07 | Loading, Feedback & System States | Skeleton loading, saving indicators, success/error messages, empty states, confirmation dialogs, progress |
| 08 | Dashboard & Financial Summaries | Financial information hierarchy, meaningful KPIs, summaries, dashboard presentation |
| 09 | Bida Peruntukan & Anggaran Interface | Program/ Aktiviti, Senarai Anggaran, shared OS wizard, Butiran Anggaran, inline editing, submission interface |
| 10 | Semakan & Kelulusan Interface | Review queues, submission details, financial comparisons, comments, return actions, finalisation controls |
| 11 | Analisis, Reporting & Administration | Analisis, financial reporting, Excel extraction interface, Kitaran management, users, roles, PTJ–Aktiviti Kewangan–OS permissions; no standalone Konfigurasi page |
| 12 | Accessibility, Responsiveness & Quality Assurance | Keyboard navigation, contrast, focus, mobile ergonomics, performance, consistency, UI regression |

**Design closure:** All 12 sections have been reviewed and approved. Preserve the functional architecture in AD-001–AD-012. Design approval does not imply a working Laravel implementation or completed QA.

## Approved foundations

1. **Visual direction: Clean Enterprise.** Light, restrained, readable, professional, information-dense; functional content takes priority over decoration.
2. **Navigation: Persistent, collapsible desktop sidebar; mobile navigation drawer.** Respect existing authorised module structure and roles; no unapproved module renaming.
3. **Desktop sidebar preference persists across page navigation.** Collapsed/expanded state is a user interface preference, not a role, permission or workflow state. Restore it on subsequent navigation; mobile navigation remains a drawer.
4. **Mandatory anti-AI-slop design principle.** Each UI element must earn its place by improving comprehension, task execution, feedback or accessibility. A generic dashboard aesthetic is not a design requirement.

## Mandatory anti-slop design rules

- Do not add decorative KPI cards, charts, gradients, badges, illustrations or icons without a demonstrated functional need.
- Do not use unnecessary card nesting, redundant containers, excessive rounding, shadows or oversized whitespace.
- Do not impose generic SaaS dashboard patterns on financial preparation, review or reporting workflows.
- Use restrained typographic hierarchy; avoid excessive bold weights and multiple competing accent colours.
- Do not invent filters, navigation levels, buttons, statuses or interactions simply to fill space.
- Base screen composition on actual user tasks, permissions, financial data and established terminology.
- Preserve useful information density and legibility on desktop and mobile; mobile is an intentional interaction design, not a compressed screenshot.
- Follow existing approved AD workflows, especially AD-004 wizard, AD-007 Program/ Aktiviti, AD-009 review and AD-010 Analisis/Excel. UI design cannot silently change business rules.
- Prefer direct tables, forms and clear contextual actions over decorative dashboards and repetitive cards.
- Audit proposed components by asking: What task does this serve? Is it redundant? Can a simpler component convey the same information? Does it slow down financial work?
- Use existing Lucide icon conventions; icons must be intelligible and have accessible names where icon-only.
- Keep the approved slash typography **Program/ Aktiviti**.


## Approved responsive data presentation

- **Hybrid responsive table strategy.** Desktop retains data tables. On mobile, management/listing tables may switch to compact cards showing essential fields, relevant actions and accessible additional details; detailed granular financial and calculation grids retain tabular column relationships with horizontal scrolling.
- Preserve financial precision, numeric alignment, source row identity, and meaningful column headers. Do not convert detailed financial grids to long cards merely to avoid scrolling.
- Select the presentation based on the actual workflow and dataset, not a universal mobile breakpoint transformation.

## Required contextual skeleton loading

- Provide skeleton placeholders for meaningful asynchronous initial content loads, module transitions, filtered data retrieval and wizard initialisation, shaped to match the actual content (table rows, form fields or summary content).
- Keep the application shell stable during content loading. Do not needlessly blank populated data during background refreshes; use local progress feedback where appropriate.
- Avoid skeleton flashes for near-instant responses. Use restrained animation and respect reduced-motion preferences.
- Replace skeletons with loaded content, empty states or explicit errors; never leave indefinite skeletons after failures.
- Skeletons do not substitute for actual performance optimisation, and loading states must not conceal unsaved edits or interfere with autosave.

## Section 01 — Visual Design System (Approved; complete)

1. **Typography:** Inter; weights 400 (regular), 500 (medium), 600 (semibold). Avoid excessive bold.
2. **Colour:** neutral grey and white surfaces, muted blue primary accent; semantic green, amber and red only for meaningful states and actions.
3. **Spacing and density:** compact but comfortable, based on a 4 px scale; 8–16 px for most component spacing.
4. **Borders and corners:** subtle 1 px borders, approximately 6–8 px corner radius; avoid decorative outlines and excessive rounding.
5. **Shadows and elevation:** minimal; prefer borders. Reserve shadows for floating menus, dialogs and overlays.
6. **Icons:** Lucide, consistently 16–20 px; use for meaningful actions/information, not decoration.

These are approved design standards. Exact token values may be specified during implementation without silently changing the agreed visual direction or business workflows.

### 01.1 Breathing room and spacing discipline — clarified 14 November 2026

User-directed clarification of the existing Clean Enterprise and anti-AI-slop standards. **Compact must remain comfortable and deliberately composed.** Text, icons, fields and actions must have sufficient internal padding and separation to read as a finished interface; content pressed against a container border is a visual defect.

- Apply the shared 4 px spacing scale through reusable tokens/components. Keep page gutters, panel insets, table cells, form groups, alerts, dialogs and empty states consistent across screens. Do not repair each page with unrelated one-off spacing.
- Distinguish **internal padding**, gaps between related items, and separation between sections. Keep related labels/fields/help/errors together; give separate groups more room. Maintain a clear visual rhythm rather than distributing everything with equal gaps.
- Use comfortable panel insets (normally 16–24 px) and compact table-cell insets (normally 12–16 px horizontally, 8–12 px vertically) as implementation starting points on the existing scale. Choose shared responsive tokens after checking actual content; these are not a reason to enlarge every row or control.
- Empty states inside bordered tables/panels require explicit padding on all sides. Start with the surrounding content's horizontal inset (normally at least 16 px) and 16–24 px vertically. Align the message with the table/panel content edge; do not let an unstyled paragraph sit directly against the border or header divider. Do not impose a tall fixed-height placeholder.
- Preserve that inset in mobile layouts, wrapped text, validation/error states and expanded sections. Check long labels, multiline messages, dropdown values, action groups and focus outlines for clipping, crowding and collisions.
- Give icons and button labels balanced internal spacing and optical alignment. Keep adjacent controls distinguishable and usable without packing their hit areas together.
- Restraint and breathing room must coexist: no decorative cards, gratuitous icons, exaggerated blank areas, oversized typography, ornamental empty-state illustrations or generic SaaS embellishments to compensate for weak layout. Typography, alignment, spacing and task hierarchy provide the polish.

**Concrete failure example:** the local Kitaran ABM empty table showed “Tiada Kitaran ditemui.” almost flush with its left/bottom border. This fails the spacing standard even though the message is functionally correct. Use a properly padded shared empty-state treatment; no extra card, illustration or redundant action is needed. Recording this requirement does not claim the current CSS has been corrected.

## Section 02 — Application Shell & Navigation (Approved; complete)

1. **Sidebar organisation:** Group existing modules by function; show only modules authorised for the current user. Do not introduce unapproved routes or module renaming.
2. **Active navigation:** Use subtle background highlighting and a clear active indicator; automatically expand the sidebar group containing the active page.
3. **Application header:** Compact, containing system identity, active Kitaran context and user account access; avoid redundant controls.
4. **Breadcrumbs:** Show on nested pages when they aid orientation and navigation; omit where redundant.
5. **Mobile navigation:** Header-triggered drawer; close after navigation, without permanently consuming mobile workspace width.

**Previously approved and retained:** Persistent, collapsible desktop sidebar; remember expanded/collapsed preference across page navigation.

**Unified navigation for multiple roles:** A user with multiple authorised roles and Aktiviti Kewangan assignments sees one unified navigation shell with access to authorised modules; no separate role-specific application shells. Navigation visibility is not an authorisation boundary: server-side permission enforcement and the functional rules in AD-005/AD-009 remain authoritative.

## Section 03 — Page Layout & Information Density (Approved; complete)

1. **Workspace width:** Fluid main workspace using available horizontal space with consistent page padding.
2. **Form width:** Constrain ordinary forms to readable widths; allow detailed financial grids to use the full workspace.
3. **Page headers:** One clear page title, optional contextual description and relevant primary action; avoid duplicated headings.
4. **Section hierarchy:** Prefer headings, spacing and subtle dividers; use cards only for genuinely distinct content or interactions.
5. **Information density:** Compact but comfortable; prioritise relevant financial information and minimise unnecessary scrolling.
6. **Filter placement:** Place filters directly above the information they control, logically grouped, with Set Semula where applicable.

Preserve the previously approved hybrid responsive table strategy. These layout standards do not introduce or change any functional fields, filters, permissions, actions or business workflows.

## Section 04 — Buttons & Action Controls (Approved; complete)

1. **Action hierarchy:** One clearly dominant primary action per context; visually quieter secondary and tertiary actions.
2. **Semantic colours:** Muted blue for primary actions, green for positive confirmation, amber for caution, red for destructive actions; use colours consistently and meaningfully.
3. **Labels and icons:** Labelled buttons for primary actions; compact Lucide icon-only controls for row actions, with accessible names and tooltips.
4. **Dimensions:** Consistent button heights and minimum widths; compact desktop presentation with adequate mobile touch targets.
5. **Placement:** Page-level actions near headings, form actions at the relevant footer and row actions beside their records.
6. **Interaction states:** Visible hover, focus, pressed, disabled and processing states; prevent duplicate submissions while processing.
7. **Destructive actions:** Clear record-specific confirmation for consequential deletion and irreversible actions; avoid unnecessary confirmation for routine actions.

These standards do not introduce new functions, change role permissions or override approved business workflows.

## Section 05 — Forms & Data Entry (Approved; complete)

1. **Form structure:** Group fields by logical purpose with concise section headings; avoid unnecessary nested containers.
2. **Labels and required fields:** Persistent labels above inputs; red asterisk for required fields; placeholders are not labels.
3. **Dropdowns and autocomplete:** Searchable selectors for long lists, clear selected and empty states, and existing database reference values.
4. **Dependent fields:** Update dependent choices when parent values change; clearly indicate unavailable and disabled choices.
5. **Financial input formatting:** Right-align numeric values; consistent currency formatting and appropriate decimal precision; no premature rounding of intermediate calculations.
6. **Validation:** Inline field errors, concise summary where useful, and preservation of user-entered values.
7. **Multi-row data entry:** Efficient add/edit/duplicate/remove consistent with AD-004; each Butiran Anggaran retains a distinct stable identity.
8. **Saving and navigation safety:** Clear save status, autosave where supported, manual save fallback and protection against unsaved-data loss.

These are shared interaction standards, not changes to AD-004 wizard persistence, AD-007 Program/ Aktiviti or financial calculation rules.

### 05.1 Monetary display contract — owner clarification, 15 November 2026

The owner reaffirmed tabular numbers and made the expected monetary presentation explicit: **`4,320.00`, not `4320`**.

- Use the existing Inter font with **tabular numerals** (`font-variant-numeric: tabular-nums`) and right alignment for numeric cells; align monetary column headers with their values. Do not substitute a monospace font for normal financial figures.
- Monetary amounts use **comma thousands grouping and a decimal point with two displayed decimal places**: `0.00`, `180.00`, `4,320.00`, `1,234,567.80`. Apply this consistently to monetary input presentation, reference KADAR, row previews, read-only values, totals and financial summaries as those interfaces are implemented.
- Keep quantities/counts, months, frequencies, identifiers and dates semantically distinct. They remain appropriately aligned/tabular numeric values; they are not automatically currency or forced to two monetary decimals.
- Display formatting is separate from source values and financial arithmetic. Preserve exact decimal source/reference values and calculations; do not coerce large decimals through binary floating point, truncate input precision, persist formatting commas as numeric data or make displayed two-decimal text the basis of subsequent calculations.
- Editing/focus/blur, paste, save/reopen, duplication and validation failures must round-trip the original exact values. Accept formatted monetary paste such as `4,320.00` through an explicit, tested normalisation boundary. Where a higher-precision rate/source/preview needs inspection, retain access to its exact value rather than silently replacing it with the display representation. This clarification does not approve a new financial rounding, entitlement or eligibility rule.
- Use one shared formatter/input presentation path across OS definitions. The next OS28000 implementation must also apply this contract to the existing OS25000, SBK and TASKA monetary cells; a correctly formatted new OS alone does not fulfil this shared commitment.

## Section 06 — Tables & Data Presentation (Approved; complete)

1. **Table structure:** Compact rows, distinguished headers and subtle horizontal dividers; avoid unnecessary gridlines and zebra striping.
2. **Column alignment:** Text left; numbers, financial amounts and percentages right; consistent decimal precision and tabular numerals.
3. **Sorting:** Controls only on sortable columns; clearly show active sort direction.
4. **Filtering and search:** Controls above the relevant table; preserve filters across pagination and sorting.
5. **Pagination:** Compact navigation with record counts and previous/next controls; avoid long page-number lists.
6. **Row actions and selection:** Consistent row action location; checkboxes only for already-supported bulk operations, without inventing new bulk capabilities.
7. **Responsive presentation:** Previously approved hybrid strategy: compact cards for suitable mobile listings; horizontal scrolling for granular financial tables.
8. **Table states:** Contextual skeleton rows, meaningful empty states and explicit error/retry feedback. Empty, loading and error content must retain deliberate cell/panel padding under Section 01.1; collapsed borders must not leave text touching an edge.

Column sets, business actions and financial data structures remain governed by the relevant approved functional ADs.

## Section 07 — Loading, Feedback & System States (Approved; complete)

1. **Skeleton loading:** Contextual skeletons match the actual content layout; application shell remains visible and stable.
2. **Loading transitions:** Avoid skeleton flashes on quick requests; preserve existing content during background refreshes with local loading indicators.
3. **Saving feedback:** Distinguish *Belum Disimpan*, *Menyimpan*, *Disimpan* and *Gagal Disimpan*; never report success until persistence is confirmed.
4. **Success notifications:** Brief, unobtrusive feedback for completed actions; avoid routine success dialogs.
5. **Error handling:** Understandable errors, appropriate recovery/retry actions, and preservation of entered data.
6. **Empty states:** Distinguish truly empty datasets from filtered-out results; show relevant next action only when permitted. Apply Section 01.1 padding and alignment to each state; concise text alone is sufficient when no additional guidance or action is needed.
7. **Confirmation and progress:** Confirm consequential actions, show processing and prevent duplicate submissions; no unnecessary routine confirmations.
8. **Motion and accessibility:** Restrained transitions, visible focus, accessible status announcements and reduced-motion support.

These standards complement previously approved contextual skeleton loading and saving principles, without changing business logic.

## Section 08 — Dashboard & Financial Summaries (Approved; complete)

1. **Information hierarchy:** Prioritise financial position, records requiring attention and relevant workflow progress; no decorative metrics.
2. **Financial amounts:** Display Jumlah Anggaran Semasa and Jumlah Muktamad separately; never add them together (AD-010).
3. **Summary presentation:** Compact blocks or direct figures where helpful; avoid unnecessary KPI card grids.
4. **Status presentation:** Meaningful text statuses with restrained semantic colour, never colour alone.
5. **Role relevance:** Display information appropriate to authorised roles and Aktiviti Kewangan assignments without changing permissions.
6. **Traceability:** Where supported, enable navigation from summaries to corresponding detailed records while preserving context.
7. **Charts:** Only for meaningful trends/comparisons better communicated visually than in tables; no decorative charts.
8. **Responsive and loading:** Preserve financial meaning on mobile; contextual skeletons, meaningful empty states and explicit errors.

## Cross-cutting anti-slop checklist (20 rules; adopted as design quality guidance)

This checklist applies across all 12 sections, without creating another workstream or changing previously approved functional architecture. Its intent is to evaluate real interfaces, not to mandate decorative patterns.

### Typography & copy

1. Remove filler badges that do not convey meaningful status or information.
2. Remove redundant labels and repeated microcopy, while retaining labels needed for accessibility and clarity.
3. Avoid arbitrary italic emphasis in headings.
4. Reserve monospace for technical content (code, IDs, timestamps) where beneficial; use Inter with tabular numerals for ordinary financial figures rather than automatically making all currency values monospace.
5. Use concrete, task-specific headings and labels; preserve approved Malay terms.
6. Balance headline wrapping where it improves readability; avoid awkward orphan words, without imposing marketing-style hero typography.
7. Use tabular numerals for aligned and dynamically changing amounts, counters and financial metrics.

### Layout, hierarchy & colour

8. Use approved design tokens and components consistently; no arbitrary ad hoc values.
9. Show actual product UI and real workflow evidence when presenting the system; avoid generic stock/abstract illustrations.
10. Give the primary task or interactive financial workspace the available visual priority.
11. Group with proximity and spacing before introducing unnecessary bordered/shadowed containers; keep data-dense forms and tables compact and readable.
12. Clearly distinguish the primary contextual action from secondary actions without multiplying competing CTAs.
13. Use one intentional muted-blue primary accent with restrained semantic colours; no generic gradients.
14. Ensure sufficient contrast for primary and secondary text, including disabled/error/status contexts.

### Geometry, polish & motion

15. Use the approved Lucide icon system with consistent stroke, sizing and optical alignment.
16. Remove ornamental gradients, glows, floating objects and other irrelevant embellishments.
17. Use visually concentric nested radii, accounting for padding and border thickness; do not apply a rigid arithmetic rule when it conflicts with component geometry.
18. Apply optical adjustments where needed to make icons and text appear visually centred.
19. Provide contextual skeletons and truthful loading/disabled/saving feedback; avoid unnecessary shimmer and flashes.
20. Use purposeful micro-interactions for action feedback, transitions and state changes; respect reduced-motion preferences.

**Interpretation safeguards:** These rules do not require oversized whitespace, new marketing sections, extra cards, or decorative motion. Preserve established workflow density, approved typography and financial precision. Assess them against actual Sistem ABM screens during Section 12 quality assurance.

## Section 09 — Bida Peruntukan & Anggaran Interface (Approved; noted verification items deferred)

Design these interfaces from the approved architecture; an actual Laravel screen or source implementation has **not** been established in the accessible repository. Do not claim to have audited working screens. Preserve AD-004/AD-007 canonical model, independently identified source rows and approved workflow rules.

### 09.1 Program/ Aktiviti (Approved)

Use a **single-page form** with four existing AD-007 sections: **A. Objektif; B. Maklumat Program/ Aktiviti; C. Pelaksanaan; D. Jemputan/Urus Setia & Peserta.** Provide compact section navigation to jump among sections without enforcing a four-step progression. Use the Section 01–07 visual, form, validation and save-state standards. The shared OS wizard remains separate. This is a presentation decision only: no change to fields, OS selection, dependencies, participant calculations, permissions or persistence.

### 09.2 Senarai Anggaran (Approved; column-level verification pending)

Use a compact, full-width financial listing rather than dashboard-style cards. Place the existing cascading filters directly above the records in their AD-004 order: **Aktiviti Kewangan → PTJ → Tempat Bertugas → Status Anggaran**; provide one text-search input and **Set Semula**. Preserve the statuses **Belum Mula**, **Belum Lengkap** and **Lengkap**. Align RM amounts to the right using tabular numerals; incomplete records must not inflate totals. Use compact Lucide row actions with accessible labels and open the **same shared OS wizard**. On mobile, use compact management record cards; granular financial tables may scroll horizontally. Apply contextual skeletons and retain existing content during refresh where possible. **Final columns and actions must be checked against the canonical Anggaran model**; illustrative sample rows and values are not requirements. No new statuses, calculations, permissions or actions are authorised by this design decision.

### 09.3 Shared OS Wizard (Revised 13 November 2026 — spreadsheet-first default)

**Superseding decision:** Every Anggaran (OS) wizard **starts with the spreadsheet-like grid presentation as its common default**. Do not pre-assign OS to the previous three editor designs. Review OS **one by one** after the common spreadsheet-like experience is established. Only an individually reviewed OS may subsequently be considered for an alternative presentation where its actual input requirements justify it; **no such reassignment is approved by this decision**.

The previously discussed alternative presentation patterns remain **options for later OS-by-OS review**, not initial OS assignments:

- **Spreadsheet-like Grid — default for every OS:** inline source-row entry/editing, suitable controls for relevant OS-specific fields and rate references.
- **Structured Form — possible later alternative:** for an OS found better suited to a compact form after review.
- **Component-based Editor — possible later alternative:** for an OS found better suited to distinct component entry after review.

Keep **one canonical shared OS wizard** and its stable Butiran Anggaran identities, save states, completeness, permissions and navigation. OS-specific fields, validation and approved AD-008 calculations are independent of presentation. The change concerns the **entry interface only**, not the canonical financial model or data migration. Do not create a new end-user layout selector or administration module merely to support future presentation assignments. This decision does **not** require showing all 30 current Excel output columns at once; display applicable OS input fields while preserving the approved Excel output contract.

### 09.4 Butiran Anggaran (Approved; presentation default revised 13 November 2026)

**Begin with spreadsheet-like inline editing for every OS**, with OS-specific relevant input fields and automatic/user-entered Kadar behaviour following their approved references. Prefer direct inline editing rather than requiring a dialog for each row. Support familiar Tab/Shift+Tab navigation; use arrow-key navigation only where it does not interfere with text or dropdown editing. Do not create blank records merely on Enter. An expanded record editor or another presentation pattern may be considered **per OS only after review**, not pre-assigned.

An alternative presentation, if later approved, may change **only user experience and presentation**, never how canonical financial data is stored, read, calculated or interpreted. Prioritise simple, meaningful entry with minimal unnecessary clicks and repetition.

Across all patterns, support the established add/edit/duplicate/remove lifecycle. Each saved source Butiran Anggaran has a stable unique Butiran ID; duplication creates a distinct new source record rather than reusing identity. Preserve input on validation failure, display errors beside the affected fields/rows, calculate only through approved OS-specific rules, and do not inflate totals with incomplete rows. Use truthful save status and protect unsaved work during navigation. Persisted deletion requires appropriate confirmation; unsaved drafts can be discarded without implying a persisted deletion. OS-to-pattern remapping must require **no financial-data migration** and must not change stored records or the canonical data contract. These are interaction standards, not new calculation or permission rules.

### 09.5 Submission Interface (Approved)

Embed submission readiness and the **Hantar Bidaan** action within the existing **Bidaan workspace**; do **not** create a separate submission page or wizard. The experience follows **Prepare → Check → Submit**: show a compact readiness summary with the current **Jumlah Anggaran Semasa** (never combine with Jumlah Muktamad), completeness and only genuinely blocking issues under existing eligibility rules. Link actionable issues directly to their relevant Anggaran/Butiran. When eligible, show **one concise confirmation dialog** describing the activity-level submission scope and the read-only nature of the submitted snapshot. Show the resulting status only after the server confirms persistence; prevent duplicate submissions while processing and preserve unsent work on failure. Preserve the established submission window, roles, parallel review routing, immutable submitted snapshot and Superadmin reopening rules; no per-OS submission or new completeness criteria.

**Section 09 (09.1–09.5) approved**, with the OS-to-editor mapping and final Senarai Anggaran columns deferred for verification against the canonical model.


## Section 10 — Semakan & Kelulusan Interface (In discussion)

### 10.1 Unified Review Workspace (Approved)

Use **one shared review workspace** for **Penyelia Bidaan** and **Ketua Bidaan**, with the same authorised financial details, OS breakdown and review history. The visible, enabled actions depend on the user's effective role, assigned Aktiviti Kewangan and the Bidaan's current workflow state; **server-side authorisation remains authoritative**. Avoid separate role-specific page implementations and duplicated financial data views.

Preserve AD-009's **parallel review routing**. Both reviewing roles retain their existing authority to return the entire Bidaan for correction; **only an authorised Ketua Bidaan** can finalise under the existing eligibility rules. Do not change snapshot immutability or Superadmin-only reopening authority. The example queue status labels, record amounts and OS figures in the conceptual UI are illustrative and do not establish new workflow statuses.

### 10.2 Collaborative Review Threads (Approved)

Adopt a lightweight GitHub Issues-inspired **shared discussion timeline**, without developer issue-management features. A review thread begins at one canonical parent: **Bidaan**, **Anggaran (OS)** or **Butiran Anggaran**; the parent is the starting point of that thread's chain of replies. Allow multiple threads on the same parent. Show the parent context and a direct link to the corresponding record; use the stable canonical Butiran ID rather than a visible grid row number.

Both **Ketua Aktiviti** and **Penyelia Aktiviti** may initiate and reply to threads and mark them **Terbuka** or **Ditutup**. **Penyedia Bidaan** and **Penyedia Anggaran** can read and reply where their existing record access permits; they cannot close threads. Show author, role, timestamp, discussion entries and auditable close/reopen events. Reviewer dashboards display counts and links for open threads, not comment counts. Keep thread actions distinct from formal Kembalikan/Muktamadkan actions. Closing all threads does **not** itself finalise the Bidaan; replies do not modify submitted snapshots.

Keep the UI compact: a discussion timeline, clear anchored-record context, reply composer and reviewer-controlled close/reopen. Use Laravel/PostgreSQL, indexed lookups and pagination rather than live sockets or extra infrastructure, based on approximately **300 Bidaan per cycle**. These are approved experience and lightweight architecture constraints, not implemented screens.

### 10.3 Review Page Layout (Approved)

Use **two tabs** within the single unified review workspace:

1. **Anggaran & Butiran** — the primary financial examination view, with direct indicators and links to review threads anchored to the relevant Bidaan, Anggaran or Butiran Anggaran.
2. **Semakan** — the consolidated review-thread view for the current Bidaan, including **Terbuka** and **Ditutup** threads.

**Semakan** is the approved tab label; do not use **Perbincangan** as its name. Do not duplicate thread data or introduce a separate workflow for each tab. When a user opens a review indicator beside a financial record, display the associated thread in a **contextual side panel on desktop** or **full-screen panel on mobile**, without forcing unnecessary tab switching. Preserve the selected record and return context.

The review page may display the existing Bidaan summary and count of open threads, without combining Jumlah Anggaran Semasa with Jumlah Muktamad. Keep formal return/finalisation controls separate from comments and thread closure.

### 10.4 Formal Review Actions (Approved)

Place **formal Bidaan decisions** in a clearly separated action area at the bottom of the shared review workspace, distinct from thread replies, closing/reopening review threads and ordinary comments.

- **Kembalikan untuk Pembetulan:** available to authorised **Penyelia Aktiviti** and **Ketua Aktiviti** for the entire Bidaan, with a **mandatory reason** and explicit confirmation. Preserve existing return and correction/resubmission rules.
- **Muktamadkan Bidaan:** available only to authorised **Ketua Aktiviti**, with explicit confirmation and all existing AD-009 eligibility, current-version and server-side permission checks. Do not require separate Penyelia sign-off.
- Prevent duplicate submissions while processing; retain user input and display actionable errors on failure. Reflect a changed status only after the server confirms the transition.
- Thread status **Ditutup** is not equivalent to formal approval, and closing all threads does not automatically finalise the Bidaan. Do not introduce new review outcomes or eligibility rules through the UI.

**Section 10 (10.1–10.4) approved.** Final review queue columns and filters must be verified against the canonical model during implementation, not invented in this design document.

## Section 11 — Analisis, Reporting & Administration (Approved; complete)

### 11.1 Analisis Workspace (Approved — reconciled with AD-010)

Use a **table-first Analisis workspace** with expandable OS rows, not a dashboard of charts or decorative KPI cards. This approval confirms the presentation approach and **does not reopen AD-010's already-finalised analysis requirements**:

- Use exactly the approved cascading filter chain **Aktiviti Kewangan → PTJ → OS**. Kitaran ABM and Fasa/Pusingan are contextual; **Tempat Bertugas** and an additional **Status Bidaan** filter are not part of this Analisis chain.
- Show only two financial amount cards: **Jumlah Anggaran Semasa** and **Jumlah Muktamad**, separately, never added together.
- The analysis table has **OS rows**, eligible **complete/verified historical expenditure years** as columns, a switchable right-side ABM amount column (**Anggaran Semasa** by default or **Jumlah Muktamad**), and a **% difference** against a user-selected eligible historical year. Use AD-010's approved percentage formula and zero/missing denominator handling.
- Expand an OS **directly to Butiran Perbelanjaan**, retaining relevant Kod Objek and descriptions; do **not** insert a separate Kod Objek navigation tier.
- Historical expenditure is a **separate, read-only analytical reference** for ordinary authorised users, **imported and maintained by Superadmin** from the approved Excel source. Only verified complete years are eligible. Its source is at **PTJ level**, not Tempat Bertugas, and imports/corrections cannot mutate canonical Bidaan/Anggaran or immutable snapshots.
- **Do not add Ekstrak Excel to Analisis**. Official extraction is a separate interface governed by AD-010.
- Illustrative amounts, OS codes, extra filter fields and drilldown levels from preliminary mockups are **not approved requirements** where they differ from AD-010.

### 11.2 Pelaporan & Ekstrak Excel Interface (Approved)

Use a compact, **single-page** official Excel extraction interface, **separate from Analisis**, governed strictly by AD-010.

- Preserve exactly three existing export scopes: **Keseluruhan Data**, **Bidaan Lengkap**, **OS Terpilih**.
- **Keseluruhan Data** requires no manual selection. **Bidaan Lengkap** permits selection of **one or more Bidaan**; **OS Terpilih** permits **one or more OS**.
- Use a **searchable multi-select dropdown with checkboxes** for Bidaan and OS selections, showing the **number selected**. Populate from actual authorised records/catalogue, not illustrative placeholder values.
- Provide a clear **Ekstrak Excel** action with generation/loading feedback, duplicate-submit protection, retained selections and actionable errors on failure. Enforce authorisation and financial versioning server-side.
- The complete workbook uses the two approved worksheets: **Gaji & Emolumen** (OS11000–OS13000, official column template still pending) and **Perbelanjaan** (OS14000–OS29000/OS42000, current approved 30-column granular structure under AD-008's later revision). Selected OS exports contain only selected OS in applicable worksheet format.
- Do not invent additional export filters, worksheet-presence rules, filenames, calculation rules or Program/ Aktiviti export selections. This is a **UI presentation decision**, not an amendment to AD-010.

### 11.3 Pentadbiran — Approved

Pentadbiran contains exactly three management areas: **Kitaran ABM**, **Pengguna & Peranan**, and **Pengurusan Aktiviti Kewangan** (PTJ–Aktiviti Kewangan–OS). **Do not create a standalone Konfigurasi page.** Existing technical, operational and financial configuration responsibilities remain governed by their authoritative architecture documents, particularly AD-008 and AD-012.

**Approved effective access model:**
- Superadmin manages the centrally authorised **PTJ × Aktiviti Kewangan × OS** combinations. An Aktiviti Kewangan may be available to a PTJ while only a **subset of OS** is permitted. Checking an Aktiviti Kewangan does **not** implicitly grant every OS.
- Assign **Pengguna × Peranan × Aktiviti Kewangan × PTJ** scope. Multiple roles and multiple scoped assignments may coexist, but cannot be cross-combined to manufacture an unassigned role–activity–PTJ tuple.
- For ordinary users, effective access is the intersection of **role-scoped user assignments** with the centrally permitted **PTJ–Aktiviti Kewangan–OS** relationships, plus existing role-specific actions. Neither a user role nor an activity assignment bypasses the PTJ/OS boundary.
- Example: **Penyedia Bidaan** assigned to **SMK PTJ A** for **PPN** can access PPN only for SMK PTJ A and only the OS centrally permitted to that SMK PTJ under PPN; cannot access other PTJ, other activities or disallowed OS.
- Maintain OS permissions **primarily at PTJ–Aktiviti Kewangan level**, not duplicated for every individual user. Changing central PTJ–activity OS permissions updates the effective authorisation of affected users, subject to normal server-side enforcement.
- Apply permissions to record queries, form choices, direct URLs, API endpoints and Excel exports; client-side visibility alone is insufficient. Preserve already-established Superadmin exceptional administration rights.

**Approved UI direction:**
- Compact **table-like expandable rows/cards** for each PTJ and each Pengguna, with clear parent–child checkbox hierarchy and selected counts.
- PTJ row: check permitted Aktiviti Kewangan and the **individual OS under each activity**. Pengguna row: check roles and their **specific Aktiviti Kewangan + PTJ** scope. Use searchable lists and responsive presentation.
- Do not make checking a parent activity automatically check all OS; distinguish enabling the parent assignment from granular child permissions.
- Show truthful save/error feedback and avoid silently claiming permission changes before server confirmation. Detailed save mechanics must follow the approved AD-013 feedback and server-validation conventions.

### 11.3.1 Pengurusan Kitaran ABM — Approved UI

This UI implements the existing rules in **AD-006 — Kitaran ABM & Administration**; AD-006 remains authoritative for operational semantics.

- Use a **table-first, expandable Kitaran row** layout. Each Kitaran is identified by its unique **Tahun Kitaran**; the create form requires only that year, with no redundant mandatory name.
- Expand a Kitaran to display **compact Fasa records**. Each record shows **Jenis Fasa** (Bidaan Awal or automatically numbered Pindaan), **Tarikh Mula**, **Tarikh Tamat**, **Skop Pembukaan**, and **Dibuka / Ditutup**.
- Within the expanded Kitaran, provide **Tambah Fasa** and contextual **Pinda**, **Lanjutkan**, **Buka**, **Tutup**, or **Buka Semula** controls as valid for the record. Do not display contradictory status actions simultaneously. Changes require server confirmation and are audited.
- The Fasa form uses only AD-006 fields: **Jenis Fasa, Tarikh Mula, Tarikh Tamat, Skop Pembukaan** (global, PTJ, Aktiviti Kewangan or a specific combination). Fasa dates control when users may enter bidding and may fall outside or span the Kitaran year (e.g. Kitaran 2028 opens for bidding in 2026). Bidaan and Anggaran dates remain restricted to the Kitaran year. A new Fasa begins **Ditutup** and is not opened automatically when its start date arrives.
- Apply **AD-013 Section 04 button conventions consistently**: Lucide icon + text for primary/consequential actions (e.g. Tambah Kitaran, Tambah Fasa, Buka, Tutup, Lanjutkan, Buka Semula), icon-only compact row edit action with tooltip and accessible name, and chevron for row expansion. Use consistent sizes, spacing and semantic colors.
- Preserve AD-006's selective Fasa scope, multiple Pindaan, no invented exclusive-active-Fasa/overlap restriction, single Bidaan identity across amendments, historical snapshot protection, audited changes, and deletion only of an empty Kitaran.
- No additional **Tetingkap Penghantaran** field, page, or UI label. Use the approved **Fasa**, **Skop Pembukaan**, and **Dibuka / Ditutup** concepts.
- Example years, dates, statuses and scope assignments in the UI discussion are **illustrative only**; live screens must use database records.

**Kitaran ABM interface approved.** The approved PTJ/user permission matrices and Kitaran interface complete Section 11.3. There is no standalone Konfigurasi page. No functional implementation is claimed.

## Section 12 — Accessibility, Responsiveness & Quality Assurance (Approved; complete)

Section 12 is a **system-wide design and acceptance standard**, not a new workflow, module, financial rule, or claim of completed application testing.

### 12.1 Accessibility

- All menus, expandable rows, forms, tables, permission matrices, search/multi-select dropdowns and financial editing controls must support meaningful keyboard operation.
- Provide visible focus, accessible names for icon-only controls, persistent field labels, status announcements and actionable field-level validation messages. Do not rely on colour alone to convey meaning.
- Target **WCAG 2.2 AA** contrast and relevant accessibility requirements; respect reduced-motion preferences and maintain legible, operable interactive states.
- Apply these standards to the existing AD-013 Section 04 buttons and Section 05 forms without inventing additional actions or business fields.

### 12.2 Responsiveness

- Preserve the approved **hybrid responsive data presentation**: desktop tables; compact mobile cards where suitable for management/listing records; horizontally scrollable tables for granular Butiran Anggaran and financial grids where column relationships must be preserved.
- Maintain financial precision, tabular alignment, essential record context and available authorised actions at every supported viewport.
- Mobile navigation uses the approved drawer; touch targets and focus/interaction affordances must be usable without unnecessarily reducing financial information density.
- No universal card conversion or new fixed breakpoint requirement is introduced.

### 12.3 Quality assurance acceptance categories

1. **Visual consistency:** approved Inter typography, spacing, restrained colours, Lucide icon conventions, button hierarchy and reusable components.
2. **Functional integrity:** UI follows AD-001–AD-012 authority, status transitions, roles, PTJ/Aktiviti Kewangan/OS scope and approved submission/review flows.
3. **Financial accuracy:** correct amounts, decimal precision, totals, immutable snapshots, historical comparison and official export semantics. Verify Section 05.1 monetary presentation (including `4,320.00`), tabular alignment, grouped-paste/edit round-trips and preservation of exact source/calculation values.
4. **Responsive behaviour:** desktop/tablet/mobile navigation, controls, filters, forms and data presentation retain meaning and operability.
5. **Accessibility:** keyboard, focus, assistive-technology semantics, contrast, touch interaction and reduced-motion behaviour.
6. **Performance and regression:** stable loading, no unnecessary skeleton/arrow flashes, duplicate actions, lost edits, broken navigation or stale/false save confirmations.

**Required spacing/state review:** Inspect actual populated, zero-record, filtered-no-results, loading, error/validation and expanded states wherever applicable, on desktop and mobile. Review border-to-content insets, alignment, wrapping, control gaps and section separation. A populated screenshot or passing overflow assertion does not establish empty-state quality. Record unexercised states explicitly; fix visual failures before claiming UI completion. Apply the anti-slop checklist during this review, without adding decoration or sacrificing useful financial density.

Each major implemented interface must be checked against the applicable categories before declaring it **UI-complete**. Record and repair failures without altering approved functional rules to accommodate a visual implementation. **No QA tests or application implementation are certified by this document.**

## Consolidation and authority boundaries

- **All Sections 01–12 are approved** as design specifications. This finalisation does not certify application implementation, data migration, security verification, accessibility conformance or production readiness.
- **AD-009 is authoritative for the later-approved parallel Penyelia Aktiviti/Ketua Aktiviti review and Ketua-only Muktamadkan flow**, notwithstanding older sequential-review descriptions in AD-005/AD-006. Do not reintroduce a required Penyelia sign-off or older Lulus/Tolak/Kemukakan workflow via UI.
- **AD-006** controls Kitaran/Fasa rules; **AD-010** controls Analisis, historical expenditure and Excel; **AD-008** remains draft for OS-specific rates, formulas and official source mappings. Do not treat unresolved financial policy as approved by AD-013.
- Detailed implementation tokens, exact screen columns and component measurements must be verified against actual source and authoritative data; they are **implementation refinements**, not pending design-section approvals.
- No new standalone Konfigurasi page, Tetingkap Penghantaran concept, unapproved filters, additional export sheets or extra workflow statuses are introduced.
- The repository's architecture documentation is not evidence of a functional Laravel application. Implementation, testing and release gates remain outstanding.
