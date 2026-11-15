# Batch 034 — OS29000 Umum source spreadsheet

**Status:** In progress; user authorised “Proceed” on 15 November 2026.
**Baseline:** `e7fb9632b15798e8137cbd8f338b51f947158154` on dev. Batch 033 remains merged/verified; local sync and detailed acceptance remain unconfirmed.

## Approved scope

Implement [OS29000 Umum](../references/OS29000-umum-reference.md) in the existing shared source spreadsheet for canonical Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti. Existing mappings: PPN/010801, Pendidikan Rendah/030219, Pendidikan Awal/030101, Pendidikan Menengah/030301, Pengurusan Asrama/030313. Preserve the 22-row reference (18 automatic, four manual rates) and shared 131-row Jawatan/Gred catalogue.

Kategori1 → Kategori2; conditional Nama/Jawatan/Gred for Penceramah, Fasilitator and Yuran; dependent Jawatan → Gred. Derive speaker/facilitator classification using approved VU7/numbered SSPA rules, in a dedicated output, never Kategori3. Hourly speaker rates selected by service plus classification; contextual Unit/quantity labels for all services. Exact preview = Bil. Unit × applicable Kadar. Purpose/year-bounded dates and inclusive calendar duration. Partial drafts remain saveable, but a preview requiring missing personnel/rate/quantity is withheld; this is not official completeness certification. Inactive personnel inputs are disabled/cleared on service changes and rejected server-side if injected. Automatic rates cannot be client overrides. Shared money formatting, title-case headers, guards, row IDs/copy/removal/TSV and error recovery retained.

No migration/seed, CSV/catalogue/mapping/permission changes, new rates/eligibility/rounding, programme association/inheritance or official output/export/totals/completeness/submission. Pakej programme-only entry remains a recorded proposal; its current grouped editor is unchanged. Reference lookup is a budget estimation rule, not payment eligibility approval.

## Verification

Pending isolated PostgreSQL, exact reference/classification/guard regressions, build/formatter/Chromium and desktop/mobile state review. Record actual tested head/evidence before merge; local sync remains pending.
