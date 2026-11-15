# Batch 034 — OS29000 Umum source spreadsheet

**Status:** Merged in PR #79; local update pending. User authorised “Proceed” on 15 November 2026.
**Baseline:** `e7fb9632b15798e8137cbd8f338b51f947158154` on dev. Batch 033 remains merged/verified; local sync and detailed acceptance remain unconfirmed.

## Approved scope

Implement [OS29000 Umum](../references/OS29000-umum-reference.md) in the existing shared source spreadsheet for canonical Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti. Existing mappings: PPN/010801, Pendidikan Rendah/030219, Pendidikan Awal/030101, Pendidikan Menengah/030301, Pengurusan Asrama/030313. Preserve the 22-row reference (18 automatic, four manual rates) and shared 131-row Jawatan/Gred catalogue.

Kategori1 → Kategori2; conditional Nama/Jawatan/Gred for Penceramah, Fasilitator and Yuran; dependent Jawatan → Gred. Derive speaker/facilitator classification using approved VU7/numbered SSPA rules, in a dedicated output, never Kategori3. Hourly speaker rates selected by service plus classification; contextual Unit/quantity labels for all services. Exact preview = Bil. Unit × applicable Kadar. Purpose/year-bounded dates and inclusive calendar duration. Partial drafts remain saveable, but a preview requiring missing personnel/rate/quantity is withheld; this is not official completeness certification. Inactive personnel inputs are disabled/cleared on service changes and rejected server-side if injected. Automatic rates cannot be client overrides. Shared money formatting, title-case headers, guards, row IDs/copy/removal/TSV and error recovery retained.

No migration/seed, CSV/catalogue/mapping/permission changes, new rates/eligibility/rounding, programme association/inheritance or official output/export/totals/completeness/submission. Pakej programme-only entry remains a recorded proposal; its current grouped editor is unchanged. Reference lookup is a budget estimation rule, not payment eligibility approval.

## Verification

Tested application head `44a27bfda1c2046d322df43f12ff39f5e4111096`; [CI 43697537069](https://github.com/muhdmuizz/sistem-abm/actions/runs/43697537069), job 132761286994: **491 tests / 4570 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated `sistem_abm_testing`; production build, monetary formatter and full Chromium regression passed. Artifact **14155505983** contains 322 PNGs including 19 Umum captures.

Six new HTTP tests cover 18 automatic/four manual reference rows, all 131 Jawatan/Gred pairs and 82 grade codes, classification boundaries including VU7, all 14 selectable service choices, exact manual-rate source storage and previews, stable IDs/reopening/no-op audit behaviour, inactive-field/derived injection rejection, invalid pair/rate/date rejection with atomic rollback, partial drafts, live Fasa/role/revision guards, audited deletion and all five exact activity mappings. No generated official financial rows are produced.

Chromium exercises dependent service and Jawatan/Gred choices, grade reset on job change, derived classification/rates, automatic rate locking, contextual quantities, inactive-personnel clearing, manual precision, calendar duration, row copying, source-only payloads and retained validation feedback. Save requests are intercepted for client checks; real persistence/authorisation is covered by PostgreSQL HTTP tests. Existing OS/Pakej browser regression remains in the same suite. Local Node syntax checks passed. No local PHP, Windows/Google session, physical-device or screen-reader execution is claimed.

The first full application head passed CI 43696906575 (491 tests / 4569 assertions plus build/formatter/Chromium). Final review aligned the server's null/empty personnel checks with the browser and added one regression assertion; final-head evidence below supersedes that earlier count. No business-name eligibility rule was introduced.

### Visual review and limits

Twelve final-head captures inspected: empty 1440/390 left; populated 1440 left/right and 390 right; partial 1440/390 right; read-only partial 1440/390 right; validation 1440/390 right; exact-value expanded desktop. Checked title-case headers, right-aligned tabular/grouped/two-decimal money, shared-job/grade controls and dedicated classification, contextual rate/quantity hints, inactive-field presentation, incomplete previews, retained validation inputs, comfortable empty-state padding and contained mobile scroll. Leading cells are partly outside the viewport at the right edge and remain available by scrolling left; populated captures expose the current internal vertical viewport, not all 14 services simultaneously. No new blocking defect found in reviewed content. Other 310 PNGs were generated/automated but not individually visually inspected. New Umum saving/network/loading/filter states were not visually reviewed; no new filter/async retrieval UI was introduced. This is bounded source-editor verification, not full accessibility, financial or production-readiness certification.

**Merge/local:** PR #79 merged at `b77a97df189d2a274d32af633a2712be3bedb746`. Local Batch 034 synchronisation/financial acceptance pending; previous local statuses remain unchanged. No migration/seed or server restart. Programme inheritance/official calculations and submission remain pending; the recorded Pakej programme-only proposal remains unchanged.

## Local checks to run

Pull dev and `php artisan optimize:clear` in another terminal; keep the existing server running and Ctrl+F5. No migration/seed. Penceramah + JURUTERA/J14 + 3 hours should show RM200.00/hour and RM600.00; Fasilitator with the same grade/quantity should show RM100.00/hour and RM300.00. Dobi with quantity 3 and manual rate 1,234.567 should show RM3,703.70 while preserving exact rate/preview. Switching to Hadiah Penghargaan Pesara must make personnel fields inapplicable; switching Jawatan clears the old Gred. Save/reopen and confirm stable rows. Local execution remains pending.
