# Batch 023 — Shared Spreadsheet Anggaran Editor

**Status:** In progress; authorised 15 November 2026 after review of the owner's OS specification improvements.
**Baseline:** `88e852623a3bfe6bf83a0133c327a3200025a7f7` on dev.

## Authorised scope and sequence

Reconcile AD-008/AD-010/AD-013 and the architecture index to the approved 30-column Perbelanjaan contract and current OS readiness. Preserve historical 27-column decisions using the approved +3 shift after field 07. Distinguish approved specifications from implementation. Do not reopen approved categories, rates or formulas or resume paused OS14000 design.

Build one shared spreadsheet-like source editor under the existing Anggaran context. First configured definition: OS25000, using its committed four-row category/UNIT/KADAR reference and approved quantity formula. This is a representative definition, not an architectural preference or substitute for other OS coverage. Existing unsupported OS remain read-only until their typed definitions are enabled.

Provide inline source editing, keyboard Tab navigation, multi-row addition and multi-cell paste, stable saved IDs, draft save/reopen, local row duplication into new identities, authorised removal and clear field/row/save/conflict feedback. Reuse existing atomic draft services/live guards and audit; do not expose opaque JSON editing.

Calculated row previews may use exact approved OS25000 multiplication. They are not persisted financial outputs, completed Anggaran totals or submission readiness. No unapproved rounding/precision/eligibility is inferred. Official calculation/output/completeness integration and programme inheritance remain separate work within the approved overall plan, alongside per-OS definitions. An unfinished OS does not block the shared editor or ready definitions.

## Verification

Pending: isolated PostgreSQL HTTP/service/full regression, build, Chromium keyboard/paste/row identity/save-feedback behavior and desktop/mobile visual inspection. No migration/seed is planned for the source-draft slice. Any limits or unverified financial behavior must remain explicit before merge.

## Prior local acceptance

User reported Batch 022 UI clean, while noting overall visual design still needs future refinement. Record supplied update/requested layout checks as user-confirmed, not independent Windows/device inspection or complete design acceptance.
