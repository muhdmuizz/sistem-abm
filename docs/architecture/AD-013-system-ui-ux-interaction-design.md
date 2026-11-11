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

## Pending design discussion — not yet approved

- Detailed navigation grouping and module labels.
- Fluid versus constrained content width and per-screen exceptions (working recommendation: fluid workspace with readable constrained form sections; not yet approved).
- Design tokens: precise typography, colour palette, spacing, borders and component dimensions.
- Detailed form patterns and action placement (hybrid responsive table strategy approved).
- Interaction feedback, validation, save states and page-specific layouts (contextual skeleton loading required).

**Boundary:** This AD is a design discussion draft, not a claim of implemented UI or an approval of the pending choices. No functional architecture in AD-001–AD-012 is reopened.
