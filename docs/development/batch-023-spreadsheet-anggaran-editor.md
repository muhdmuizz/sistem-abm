# Batch 023 — Shared Spreadsheet Anggaran Editor

**Status:** Merged in PR #54; local functional checks pending. Authorised 15 November 2026 after review of the owner's OS specification improvements.
**Baseline:** `88e852623a3bfe6bf83a0133c327a3200025a7f7` on dev.

## Authorised scope and sequence

Reconcile AD-008/AD-010/AD-013 and the architecture index to the approved 30-column Perbelanjaan contract and current OS readiness. Preserve historical 27-column decisions using the approved +3 shift after field 07. Distinguish approved specifications from implementation. Do not reopen approved categories, rates or formulas or resume paused OS14000 design.

Build one shared spreadsheet-like source editor under the existing Anggaran context. First configured definition: OS25000, using its committed four-row category/UNIT/KADAR reference and approved quantity formula. This is a representative definition, not an architectural preference or substitute for other OS coverage. Existing unsupported OS remain read-only until their typed definitions are enabled.

Provide inline source editing, keyboard Tab navigation, multi-row addition and multi-cell paste, stable saved IDs, draft save/reopen, local row duplication into new identities, authorised removal and clear field/row/save/conflict feedback. Reuse existing atomic draft services/live guards and audit; do not expose opaque JSON editing.

Calculated row previews may use exact approved OS25000 multiplication. They are not persisted financial outputs, completed Anggaran totals or submission readiness. No unapproved rounding/precision/eligibility is inferred. Official calculation/output/completeness integration and programme inheritance remain separate work within the approved overall plan, alongside per-OS definitions. An unfinished OS does not block the shared editor or ready definitions.

## Verification — passed 15 November 2026 (Malaysia)

Tested application head: `11255c04c0c7c0d117c66d58aa475896b7f654e3`. [CI 43632464587](https://github.com/muhdmuizz/sistem-abm/actions/runs/43632464587): **423 tests / 3245 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; production asset build and Chromium regression passed. Thirteen new HTTP/service tests cover actual save/reopen/edit/no-op, blank/partial drafts, exact decimal text, stale revisions, role/workplace/Fasa/session/central guards, legacy preservation, independent row IDs, audited removal and transactional rollback on both create and deletion audit failure.

Browser checks exercise multi-row addition, native Tab navigation, quoted-TSV handler delivery, reference unit/rate display, exact large decimal preview, duplication, unique control IDs, remove/undo and stable deletion payloads. Static fixture save responses are intercepted for 422/409/network feedback and retained-input/retry checks; actual successful persistence is verified by PostgreSQL HTTP tests, not those browser interceptions.

Artifact `14128543309` contains 115 PNG captures (11 new grid captures). Visually reviewed all 11: empty/populated/legacy/read-only at 1440 and 390, validation at both widths and narrow conflict feedback. Desktop spacing, cell alignment, feedback and mobile horizontal table containment are acceptable for this functional slice. This does not establish physical-device, Windows, production or full-design acceptance. Other existing browser regression checks passed; all previous screenshots were not individually re-reviewed.

No migration or seed; no official financial output, totals/completeness, submission or programme inheritance enabled. OS25000 uses the exact committed four-row reference; other OS remain unconfigured. Source-only removal rejects calculated sources. Preview arithmetic is exact and does not invent a rounding policy.

## Prior local acceptance

User reported Batch 022 UI clean, while noting overall visual design still needs future refinement. Record supplied update/requested layout checks as user-confirmed, not independent Windows/device inspection or complete design acceptance.

## Windows follow-through (not yet executed)

Keep the existing development server running. With a clean working tree, switch to dev, pull with --ff-only and run php artisan optimize:clear. This batch has no migration, seeder or local asset build.

In Bida Peruntukan, open an eligible active OS25000 Anggaran, then Buka Jadual Butiran. Check multiple-row entry and Tab navigation; paste approved Tempat Bertugas, Kategori1 and Bil. Unit values; save/reopen and edit; duplicate a selected row, save and confirm a distinct Butiran ID; select a saved row, remove/undo it, then confirm removal on save and its Log Tindakan entry. Check narrow-window horizontal table scrolling. Actual role, Fasa and central settings still apply; do not seed permissions to bypass them. Official totals and other OS entry remain outside this slice.

## Next implementation boundary

Continue typed definitions for ready OS and their exact approved references, then separately integrate official calculation/output/completeness and approved programme association/inheritance. Keep OS14000 detailed work paused, and isolate the specific OS15000/general OS21000 unresolved financial rules. No blanket redo of approved OS applicability or formula discussions.

## Merge checkpoint

[PR #54](https://github.com/muhdmuizz/sistem-abm/pull/54) merged into dev at `683629ba734e9104897e8722363bbcaebeb92270`. Evidence-only final commit `6d4bc49c9288d0bce95ce0e0199a93e6599ebe91` changes documentation; the tested application head above is unchanged. Local update has not been executed by the cloud agent or confirmed by the user.
