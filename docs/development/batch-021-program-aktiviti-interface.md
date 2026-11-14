# Batch 021 — Program/ Aktiviti Interface

**Status:** In progress; user approved the bounded interface scope on 14 November 2026.
**Baseline:** `e2197b6012d77f8f15ba7a95773e1eb1a6826618` on dev.

## Scope and authority

Implement a scoped programme list and one-programme four-section draft create/edit page under Bida Peruntukan. Reuse Batch 020's audited DrafProgramAktiviti service; preserve AD-007, Reference 06 strict objective defaults and AD-013 spacing/accessibility. Include dependent database-backed objectives, separate Jenis/Kategori, budget-year dates, package components, classified Jemputan/Peserta rows and derived totals. Authorised workplace context comes from the database. Live roles, central permissions, Fasa, active Bidaan, workflow and revisions continue to govern saves.

No OS selection/association, inheritance/recalculation, financial completeness, deletion, submission, optionality administration or resumed paused AD-008 design. Metadata readiness is not financial completion. No migration or seeder change is planned.

## Verification

Pending: isolated PostgreSQL HTTP tests, full suite, build, browser interaction and desktop/mobile visual review. Local synchronisation and programme create/edit smoke checks remain pending until the user reports them. Do not equate fixtures with live Google/Windows acceptance.
