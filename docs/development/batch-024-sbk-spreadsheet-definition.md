# Batch 024 — OS42000 SBK Spreadsheet Definition

**Status:** Merged in PR #56; local update/functional checks pending — owner authorised continuation 15 November 2026.
**Baseline:** `250d57f95706e3caf1eb30b9a8a815d7644700e6` on dev.

## Scope and authority

Continue the shared source-draft spreadsheet with the approved SBK template from [OS42000 SBK](../references/OS42000-utiliti-pendidikan-rendah-reference.md) and the shared four Pukal rows in [OS23000 CSV](../references/OS23000-perhubungan-utiliti-kategori.csv). Preserve distinct Pendidikan Rendah 030228 and Pendidikan Menengah 030310 mappings, original historical wording and AMal exclusion. No source CSV/catalogue reassignment or new financial policy.

Resolve definitions from actual OS plus Bidaan activity/mapping context. Reuse the common grid/services; input columns are authorised Tempat Bertugas, Kategori2 utility and manual monthly KADAR. Kategori1 is fixed Pukal, BILANGAN BULAN fixed 12, exact KADAR × 12 row preview. Partial drafts may be retained without claiming completeness; no fabricated rate/default. Reject user overrides of fixed/derived fields and cross-definition payloads. Keep OS25000 source schema/behaviour compatible.

Preserve live role/workplace/central/Fasa/session/revision/audit safeguards, source-only removal and legacy protection. No migration, seeder, official outputs/totals/completeness, programme inheritance or submission. OS23000's legacy identity reconciliation is not bypassed by enabling SBK.

## Verification — 15 November 2026

Final tested application head `5ebdce2244d8f80aa021df961b1af4efd1c4eb9f`: [CI 43635684953](https://github.com/muhdmuizz/sistem-abm/actions/runs/43635684953) passed **431 tests / 3354 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`, production build and Chromium regression including the numeric alignment checks. Previous run 43634996480 passed 431 tests / 3354 assertions, build and Chromium at 38c0fdee0d2c8f57a655455680c8845446d2414f. Visual review of its 14 new SBK captures identified left-aligned monthly-rate inputs/read-only values; corrected through metadata-based numeric cells/headers and decimal input styling, with browser regression assertions. Do not treat the earlier pass as verification of the later alignment correction.

Eight new PostgreSQL HTTP tests cover the four utilities, manual rate source/save/reopen/edit/no-op, both activity/code mappings and distinct Anggaran source identities, rejected wrong/removed mappings and TASKA/AMal, fixed/derived field tampering, malformed rates and partial drafts, private legacy/cross-definition protection and live phase/role/stale guards. Existing common-grid tests cover audit rollback, deletion and other scope/session boundaries.

Browser SBK checks exercise inline manual rates, fixed Pukal/12, exact decimal and large-integer previews, Tab, TSV-handler delivery, local duplication and retained validation feedback. Static fixture save failures are intercepted; real persistence is tested through PostgreSQL HTTP routes. No physical-device or independent Windows access claimed.

Artifact `14130395999` contains 129 PNG captures (14 new SBK states). Before the alignment correction, all 14 new states were visually reviewed: empty/populated/Menengah/partial/legacy/read-only and validation, each at 1440/390 widths. Final-head review rechecked populated desktop/mobile, read-only desktop, validation desktop and OS25000 populated desktop to confirm the correction and compatibility. Other final-head captures were generated and browser-checked but not individually re-reviewed after the numeric styling change. Mobile column containment/scrolling passed. No new asynchronous initial load, filtered-results or expanded-panel interface is introduced; no physical-device, full design acceptance or production readiness is claimed.

## Local follow-through — pending

Keep the running server. With a clean working tree, switch to dev, pull --ff-only and run php artisan optimize:clear; Ctrl+F5 refreshes the changed grid script. No migration, seeder, npm build or restart required.

Open an authorised active OS42000 — Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK) under Pendidikan Rendah or Pendidikan Menengah, then Buka Jadual Butiran. Check the four Pukal utilities, manual blank monthly KADAR, fixed Pukal/12, an exact KADAR × 12 preview, save/reopen, edits, duplicate/remove/undo, and narrow horizontal scrolling. OS25000 retains its existing entry flow. Do not enable AMal or alter permissions to bypass the live access controls.

## Remaining work

Official calculation/output/completeness and programme association/inheritance remain unimplemented. Other ready OS need typed definitions. Preserve all approved OS decisions and keep unresolved rules isolated; OS14000 detailed work stays paused.

## Merge checkpoint

[PR #56](https://github.com/muhdmuizz/sistem-abm/pull/56) merged into dev at `68a8f46fb839675f2d83ad7914e9b4cb2f163a19`. Final evidence-only commit `5925c375f85ff878726492ea5da0c71ab3e13a88` changes documentation; application code remains the exact tested head `5ebdce2244d8f80aa021df961b1af4efd1c4eb9f`. Local update has not been executed by the cloud agent or confirmed by the user.
