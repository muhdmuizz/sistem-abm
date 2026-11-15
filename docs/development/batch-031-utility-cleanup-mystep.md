# Batch 031 — Remove Obsolete Utility Test Data and Add MySTEP

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `680aafd7f2a3253909c578230795097c4d149842` on dev.

## Owner correction and cleanup

Owner explicitly confirms the system is in development with no real data and instructs removal of obsolete Pukal data rather than maintaining compatibility/preservation UI. This supersedes Batch 030's **Rekod sedia ada** approach and its deferred legacy budget reconciliation. Target only **OS23000 / Utiliti Pukal**: a normal targeted data migration deletes its calculated test outputs, Butiran, Anggaran and OS-specific permission rows, removes the obsolete reference, and repoints its four existing Kod Aktiviti mappings to canonical **Perhubungan dan Utiliti** without granting permissions. Bump affected Bidaan revisions so stale editors cannot save against pre-cleanup context. Keep current canonical budgets, Pukal category choices, other OS, organisation/users/programmes and append-only audit protection untouched. No whole-database fresh/reset. Deleted test inputs are not recreated by down().

Remove all old-identity card grouping, preservation panels/CSS and compatibility activation helpers/tests. Catalogue becomes **32 identities / 30 eligible / 2 excluded**, replacing historical 33/31/2 counts; **105 mappings / 103 eligible** remain, preserving source codes and original wording. Updated CSV import cannot recreate the duplicate identity. Both existing OS23000 category paths remain in Batch 029's single editor.

## Next approved OS — MySTEP

Implement the [approved single MySTEP template](../references/OS29000-mystep-reference.md) across its three existing catalogue variants and seven existing mappings: PPN/MySTEP 010812; Pendidikan Rendah Guru Ganti 030223 / PPM Ganti 030226; Pendidikan Awal Guru Ganti 030106 / PPM Ganti 030105; Pendidikan Menengah Guru Ganti 030305 / PPM Ganti 030309. No new contexts or variant-specific reference sets.

Reuse unchanged 11-row category and purpose sources and four academic-rate rows. Three Kategori1 and all six Tujuan Lantikan choices are independent; Kategori2 is Taraf Akademik. Unit fixed Orang; rate is the supplied **JUMLAH** (2638.35 / 2408.85 / 2179.35 / 1949.85) per person/month, never recomputed from Upah/KWSP/PERKESO. User quantities and budgeted months; exact draft preview = Bil. Unit × Kadar × Bilangan Bulan. No identified personnel inputs. Dates stay within Kitaran year and ordered; a shared date-cell/Tempoh-in-days display includes both endpoints and handles leap days, independent of manually entered budgeted months. It does not establish month conversion, entitlement or a financial rounding policy. Partial source drafts remain saveable; no official completeness certification.

Keep actual title-case headers, right-aligned/tabular grouped/two-decimal money and exact source values. Retain audited identity/revision/session/Fasa/role guards and source copy/removal. No rate changes, payroll component calculation, official output/totals/completeness, programme inheritance or submission. Earlier local execution remains unconfirmed.

## Verification

Pending isolated PostgreSQL regression including targeted cleanup/reimport/idempotence/unrelated-data cases, MySTEP persistence/mapping/date/rate guards, build/formatter/Chromium and desktop/mobile states. Record actual tested SHA/results before merge. Local migration/update pending.
