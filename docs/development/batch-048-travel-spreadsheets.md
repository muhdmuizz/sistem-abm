# Batch048 — OS21000 travel spreadsheets

**Status:** Application verified; merge/local acceptance pending.
**Baseline:** `9f85069a144429d34d0f2ea06bdc7922f8f00f59` (approved decision PR106 merged). Prior application checkpoint Batch047 PR104,578tests/7,020assertions; prior local sync/migrations/import/financial/device acceptance unconfirmed.

## Approved scope

Three distinct Anggaran labels Umum/Sukan/Kokurikulum with exact original PPN010801/010803/010803 mappings. Separate Pertukaran adapter unchanged. Share all83 untouched source rows/six Kategori1 labels; preserve C and blank cells. One named officer/component per independent source row. Meals=reference daily rate×manual claimable days; hotel=reference nightly rate×manual nights; vehicle=reference1.00×manual distance/trip; taxi/rail/boat/air=manual fare/trip. All ×trips/month×manual months. No financial rounding policy invented: exact finite products preserved, money presentation grouped/tabular two decimals with exact-value disclosure. Kategori2 conditional only combined category: vehicle or Bas/ Teksi; Kategori3 absent/inactive. Bilangan Hari contextual days/nights, inactive elsewhere; distance mileage only. Inclusive day duration is independent of claimable quantities, dates within Kitaran year.

Use established full SSPA numeric-level lookup only for source numerical grades. Never map VU7→C or fabricate missing shared grade11/15 entries. Retain original unmatched source rows; withhold missing-match rates/previews, reject manual substitutes for fixed/unmatched paths. Blank-grade rail/boat/air references apply to valid shared personnel without inventing numerical/source-C mapping. Taxi is explicitly approved manual fare, preserving original combined-category1.00 CSV value for mileage. No new vehicle bands, cap, entitlement, negative/zero/fraction rule.

Reuse source-only shared editor metadata (no shared JS/CSS changes), conditional clearing/validation, mixed rates, strict source schema, partial drafts, exact money, revisions/CAS, roles/PTJ/workplace/central/Fasa, copy/delete/audit. Purpose direct text; programme inheritance/official outputs/totals/completeness/submission/export and shared context classifications remain deferred. No daily expansion, category/variant consolidation, catalogue/mapping/CSV change, migration/seed/reset. AD008 stays Draft; Steps02/03 in progress. Sequence remaining editors→consolidated UI refinement→shared proposals. Keep Server separate from Updates.

## Verification

Pending dedicated HTTP/PostgreSQL reference/formula/guard tests, full isolated suite/build/exact-money/Chromium and applicable desktop/mobile visual review. Local PHP/Windows unavailable. Changed smoke script passes Node syntax check; no application execution claim from static review.


### Initial verification attempt

Initial head `064c859ffdba57673d9d39a997c3edcee81b353e`, CI44335510778/job134910985837 stopped before tests ran (exit255): private component() test helper conflicted with inherited Laravel TestCase component method. Renamed helper to claimRow; application unchanged. No test count/build/browser/artifact evidence from this failed attempt.


### Browser interaction follow-up

Head `0ac07bc3691129900af38b6d19716ff08cbbaaa1`, CI44335911969/job134912451795: 586 tests/7,479 assertions, production build and exact-money checks passed; Chromium reached its last travel copy/remove step, then checkbox click did not change state after editing a copied precise fare. Test now explicitly blurs the money input before checking the checkbox (letting its existing blur formatting/exact-detail layout finish), and asserts row removal. No forced checkbox/DOM-state mutation, no application change. Potential row geometry change when leaving a precision disclosure remains a consolidated UI investigation; this failed browser run is not full verification. Local replay unavailable: packaged browser absent and system Chromium refused socket creation; final replay remains in CI.


Visual review of intermediate artifact14441542768 found day/night quantity hints displayed below inactive mileage/fare cells. Corrected metadata to provide the hint only for meals/accommodation and added regression assertions. No shared grid/CSS or financial rule change. Supersedes the intermediate browser-test-only head for final full verification.


## Final verification —18November2026

Application head `9eebfba5da255e5908fa3368221e91d74d62d0e7`: [CI44338401373](https://github.com/muhdmuizz/sistem-abm/actions/runs/44338401373), job134921288935 passed **586 tests /7,484 assertions**, production build, exact-money checks and full Chromium. PHP8.4.27/PostgreSQL18.7, isolated `sistem_abm_testing`; test duration284.06s. Eight new HTTP/PostgreSQL tests preserve all83 rows/C/blanks, check available numerical/shared-grade matches without fabricating11/15 or VU7→C, seven claim-component formulas and conditional metadata, exact manual fare/grouped paste, dates/factors independence, distinct officer/component source IDs, save/reopen/edit/no-op/partial/read-only, atomic invalid/derived/inactive-field rejection, three exact variant mappings/separate Pertukaran, copy/delete and live revision/role/PTJ/workplace/central/Fasa guards. Official output table remains untouched. Node smoke syntax passed locally; local PHP/browser/Windows verification unavailable as documented above.

Earlier attempts remain explicit: initial helper-name fatal before tests; then586/7,479+build/exact-money with last copy-checkbox browser failure. Intermediate `30c9f6507954e581136fe7fd9727642be75dad7e`, CI44338079754/job134920141230 completed successfully after explicit input blur; superseded by the final metadata hint correction and its regression assertions. Only final head is current application verification.

Final artifact14442019034 contains **594 PNGs /22 travel captures**. Individually reviewed **11 travel captures**: populated desktop left/right/mobile right; empty desktop/mobile left; partial desktop right; read-only mobile right; unmatched desktop/mobile; validation mobile; expanded exact amount mobile. Also inspected existing Pertukaran populated desktop-right (**12 captures total**). Earlier artifact14441542768 had one populated desktop-right capture reviewed, exposing the corrected inactive quantity hints; it is separate historical evidence. Final reviewed states show grouped/tabular two-decimal amounts and exact disclosure, fixed/manual rates, missing-match explanation/withheld amount, correct inactive hints, error summary/cell and readable controls. No shared JS/CSS changes.

Limits: horizontal/vertical table scrolling, tall rows/long helper text and focused skip-link overlap remain consolidated UI work. Current endpoint captures do not visually certify all middle category controls or lower rows (including the final incomplete row in the partial fixture); dedicated HTTP/browser assertions cover their data/behaviour, not visual completeness. Other captures, pending/loading, physical devices/screen readers and local financial acceptance are uninspected. Potential geometry change when a precise fare loses its disclosure on blur remains shared UI investigation; final copy/remove check explicitly finishes blur then uses normal checkbox/action and verifies removal. No filtered-empty state applies; no UI-complete/production-ready claim.

Merge pending; later documentation-only commits do not extend the tested application head. No new migration/seed; prior local sync/migration/import/financial/device statuses remain unconfirmed.
