# AD-013 — System UI/UX & Interaction Design

**Status:** Draft — approved foundations recorded; screen-level design discussion ongoing  
**Date:** 11 November 2026  
**Related:** AD-001–AD-012

## Approved discussion roadmap (12 sections)

The following is the agreed **scope and discussion order**, not approval of each section's detailed standards. Discuss each section, consolidate decisions, then move to the next; do not create ad hoc sub-workstreams.

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
| 11 | Analisis, Reporting & Administration | Analisis, financial reporting, Excel extraction interface, Kitaran management, users, roles, configuration |
| 12 | Accessibility, Responsiveness & Quality Assurance | Keyboard navigation, contrast, focus, mobile ergonomics, performance, consistency, UI regression |

**Process:** Work in numerical order; consolidate each section before proceeding. Preserve the business architecture approved in AD-001–AD-012. Ground module-specific design in actual workflows and screens. The deliverable is one consolidated AD-013 design specification. No section is deemed complete merely because a cross-cutting foundation was approved.

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

## Section 06 — Tables & Data Presentation (Approved; complete)

1. **Table structure:** Compact rows, distinguished headers and subtle horizontal dividers; avoid unnecessary gridlines and zebra striping.
2. **Column alignment:** Text left; numbers, financial amounts and percentages right; consistent decimal precision and tabular numerals.
3. **Sorting:** Controls only on sortable columns; clearly show active sort direction.
4. **Filtering and search:** Controls above the relevant table; preserve filters across pagination and sorting.
5. **Pagination:** Compact navigation with record counts and previous/next controls; avoid long page-number lists.
6. **Row actions and selection:** Consistent row action location; checkboxes only for already-supported bulk operations, without inventing new bulk capabilities.
7. **Responsive presentation:** Previously approved hybrid strategy: compact cards for suitable mobile listings; horizontal scrolling for granular financial tables.
8. **Table states:** Contextual skeleton rows, meaningful empty states and explicit error/retry feedback.

Column sets, business actions and financial data structures remain governed by the relevant approved functional ADs.

## Section 07 — Loading, Feedback & System States (Approved; complete)

1. **Skeleton loading:** Contextual skeletons match the actual content layout; application shell remains visible and stable.
2. **Loading transitions:** Avoid skeleton flashes on quick requests; preserve existing content during background refreshes with local loading indicators.
3. **Saving feedback:** Distinguish *Belum Disimpan*, *Menyimpan*, *Disimpan* and *Gagal Disimpan*; never report success until persistence is confirmed.
4. **Success notifications:** Brief, unobtrusive feedback for completed actions; avoid routine success dialogs.
5. **Error handling:** Understandable errors, appropriate recovery/retry actions, and preservation of entered data.
6. **Empty states:** Distinguish truly empty datasets from filtered-out results; show relevant next action only when permitted.
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

## Section 09 — Bida Peruntukan & Anggaran Interface (Pending discussion)

Review actual Program/ Aktiviti, Senarai Anggaran, shared OS wizard, Butiran Anggaran and submission interface patterns. Preserve the AD-004/AD-007 canonical model, independently identified source rows, and existing workflow decisions. Do not finalise module-specific design without reviewing real workflows and screens.


## Pending design discussion — not yet approved

- Detailed navigation grouping and module labels.
- Fluid versus constrained content width and per-screen exceptions (working recommendation: fluid workspace with readable constrained form sections; not yet approved).
- Section 01 visual foundation approved; implementation-level tokens and component dimensions to be specified consistently.
- Detailed form patterns and action placement (hybrid responsive table strategy approved).
- Interaction feedback, validation, save states and page-specific layouts (contextual skeleton loading required).

**Boundary:** This AD is a design discussion draft, not a claim of implemented UI or an approval of the pending choices. No functional architecture in AD-001–AD-012 is reopened.
