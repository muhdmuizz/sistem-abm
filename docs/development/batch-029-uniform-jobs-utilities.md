# Batch 029 — Applicable Uniform Jawatan and OS23000 Utilities

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `dffc449c439e9cc5c4181bb1177bffa2a6f8011d` on dev.

## Correction and approved scope

Owner requires OS27000 Pakaian Seragam Jawatan limited to applicable posts. Derive the permitted subset from the unchanged four [approved category/job rates](../references/OS27000-pakaian-seragam-reference.md): **Pembantu Khidmat Am** and **Pembantu Makmal**, verified against shared Reference 07 rather than a duplicate personnel dataset. No additional jobs/grade filters/payroll rules. Keep Jawatan independent of category selection and Bil. Pasang fixed at 2. Enforce new input restriction server-side and on spreadsheet paste; keep consistent actual title-case headers and tabular accounting amounts.

Version v3. Existing valid v1/v2 drafts with unrelated shared jobs stay stored and repairable: no read-time rewrite, no fabricated price, project an empty Jawatan selection with an explicit reselection notice, offer only applicable choices, and replace through explicit audited save. Legacy unknown/malformed sources retain private quarantine. No bulk data rewrite.

Next bounded OS: [OS23000 Perhubungan dan Utiliti](../references/OS23000-perhubungan-utiliti-reference.md), seven existing dependent category combinations, manual monthly Kadar, fixed 12 months, exact preview Kadar × 12. Current canonical Perhubungan dan Utiliti mappings only: PPN/010801, Pendidikan Rendah/030219, Pendidikan Awal/030101, Pendidikan Menengah/030301, Pengurusan Asrama/030313, each requiring its existing mapping and live access. This does not resolve/merge the older **Utiliti Pukal** identity or create another wizard: that catalogue entry remains unconfigured, with source-association reconciliation pending. Reuse existing seven-row CSV shared by SBK; SBK retains its Pukal-only template.

No new quantity/date/eligibility/month/rounding policy. No migration/seed, catalogue/mapping/permission/reference CSV edits, official outputs/totals/completeness, programme inheritance or submission. Preserve exact source decimals, identities, audit/session/Fasa/revision/role guards, copy/removal and legacy protection.

## Verification

Pending isolated PostgreSQL/full regression, production build, formatter/Chromium and representative desktop/mobile inspection. Record tested head/evidence before merge. Batch 028 local sync/checks remain unconfirmed; Batch 029 local acceptance pending.
