# Batch 004 — OS Catalogue & Source Mapping Foundation

Baseline: `3541052` on `dev`. Authority: AD-002 reference/transaction separation, REF-04 canonical identities and exclusions, REF-05 access dependencies, and the committed catalogue/mapping CSVs. The owner assigned TASKA Kod Aktiviti `010102` under PPN on 13 November 2026, superseding its previous blank code. This batch implements reference data only; it does not finalise Draft AD-008 or approve financial calculations.

## Scope

- `RujukanOs` / `rujukan_os`: OS reference identity is Kod Objek + canonical Butiran Perbelanjaan, not Kod Objek alone. These are catalogue records, not transactional Anggaran.
- `PemetaanOs` / `pemetaan_os`: retain the source Aktiviti Kewangan, optional Kod Aktiviti, OS identity, original description, source abbreviation and source status. A composite foreign key prevents associating a code with the wrong Aktiviti Kewangan. Parent deletion is restricted.
- PostgreSQL unique identities include nullable-code mappings (`NULLS NOT DISTINCT`), so any genuinely nullable-code mapping also remains unique. TASKA uses the owner-confirmed code `010102`.
- `OsReferenceSeeder`: validate the complete source and existing parents before writes; import atomically; preserve IDs and unrelated records on rerun. Requires Batch 002 references. No automatic DatabaseSeeder change.
- Eligible-reference query scopes omit the two excluded references and their mappings. They do not grant PTJ/user access, set category defaults, or assert wizard/calculation readiness.

## Verified source inventory

| Reference | Count |
| --- | ---: |
| Catalogue entries | 33 |
| Eligible catalogue references | 31 |
| Reference-only exclusions (POL and AMal) | 2 |
| Source mappings | 105 |
| Non-excluded source mappings | 103 |
| Blank Kod Aktiviti mappings after TASKA correction | 0 |
| Kod Aktiviti references after TASKA correction | 38 |

These are audited counts from the current committed CSVs, not hardcoded application limits. Earlier preliminary narrative tallies are not substituted for the actual source. Canonical wording, original wording and genuinely empty original descriptions are retained. Sukan/Kokurikulum share `010803` while retaining distinct OS identities.

## Verification boundary

Integration tests cover inventory, relationships, original wording, leading zeroes, distinct same-code variants, exact-duplicate consolidation, exclusions, TASKA code `010102` and upgrade of the previous blank mapping, stable IDs, unrelated-record preservation, missing/wrong parents and database constraints. GitHub Actions must execute these against isolated `sistem_abm_testing`; actual results are recorded on the PR.

No authentication, roles, PTJ/OS permissions, financial persistence, OS fields/rates/formulas, UI or export changes are included. The owner-confirmed TASKA correction updates its reference MD and both affected CSVs; architecture decisions remain unchanged.

## Local synchronisation after tested merge

On a clean `dev` working tree, pull with `git pull --ff-only origin dev`. Clear cached configuration and verify the actual PostgreSQL database is `sistem_abm` before running `php artisan migrate`, then `php artisan db:seed --class=OrganisationalReferenceSeeder` and `php artisan db:seed --class=OsReferenceSeeder`. The first seeder adds the newly assigned `010102` reference; the second upgrades any previous blank TASKA mapping in place without deleting unrelated records. Use neither fresh nor reset migrations. Cloud CI does not synchronise the Windows database.
