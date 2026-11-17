# Batch 042 — One OS26000 card and spreadsheet

**Status:** In progress; owner approved 17 November 2026 after reporting the two OS26000 cards.
**Baseline:** `129a8c63b03d56a9e40d99eb696134fe28057182` (Batch 041 application PR #93, documentation PR #94). Batch 041 local sync/financial acceptance remains unconfirmed; earlier unconfirmed migrations/imports remain pending.

## Authorised correction

Supersede Batch 041's separate catalogue identities with one retained OS26000 card (`Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan`). Select Pukal/Bukan Pukal per spreadsheet row; both can coexist in one Anggaran. Preserve all nine existing Kod Aktiviti mappings, original provenance, exact code parents, 42 reference paths, units/rates, source schema and formula semantics. Filter available paths by the exact mappings for the current Aktiviti Kewangan and validate the same limits server-side. Pendidikan Awal has Bukan Pukal only. The single retained OS permission controls the card; obsolete permission overrides are not promoted.

Pukal = quantity × reference rate × frequency × months. Bukan Pukal = quantity × reference rate, with frequency/months cleared client-side and rejected if injected. Source-only `os26000.v1` values under the retained identity remain readable; no version/schema rewrite. Fuel assumptions remain approved only for 2027/2028 planning; no new financial rule.

As previously authorised for development-only obsolete Pukal records, a targeted transactional migration removes only the obsolete OS26000 identity's draft/source/output graph and overrides; retains canonical and unrelated records; repoints non-duplicate mappings with stable IDs and original wording; increments affected bid revisions; aborts atomically if obsolete estimates belong to non-draft bids. Idempotent reference import cannot recreate the old card. No reset/fresh or broad financial seed. Expected catalogue 30 (28 eligible/two excluded), mappings unchanged at 105 (103 eligible); prior counts are historical.

## Boundaries and verification

No proposal workflow, official outputs/totals/completeness/submission, programme inheritance or export changes. AD-008 remains Draft; Steps 02/03 in progress. Proposal governance remains the next agreed slice. Server remains running in its dedicated terminal; use Updates for Git/migration/import.

Pending full isolated PostgreSQL tests/build/exact-money/Chromium plus relevant desktop/mobile screenshot review. Node syntax check passed locally. Record tested head/run/runtime/counts and review limits before merge; actual merge and local acceptance pending.
