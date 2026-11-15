# Batch 031 — Remove Obsolete Utility Test Data and Add MySTEP

**Status:** Merged; local targeted migration/acceptance pending. Owner authorised 15 November 2026.
**Baseline:** `680aafd7f2a3253909c578230795097c4d149842` on dev.

## Owner correction and cleanup

Owner explicitly confirms the system is in development with no real data and instructs removal of obsolete Pukal data rather than maintaining compatibility/preservation UI. This supersedes Batch 030's **Rekod sedia ada** approach and its deferred legacy budget reconciliation. Target only **OS23000 / Utiliti Pukal**: a normal targeted data migration deletes its calculated test outputs, Butiran, Anggaran and OS-specific permission rows, removes the obsolete reference, and repoints its four existing Kod Aktiviti mappings to canonical **Perhubungan dan Utiliti** without granting permissions. Bump affected Bidaan revisions so stale editors cannot save against pre-cleanup context. Keep current canonical budgets, Pukal category choices, other OS, organisation/users/programmes and append-only audit protection untouched. No whole-database fresh/reset. Deleted test inputs are not recreated by down().

Remove all old-identity card grouping, preservation panels/CSS and compatibility activation helpers/tests. Catalogue becomes **32 identities / 30 eligible / 2 excluded**, replacing historical 33/31/2 counts; **105 mappings / 103 eligible** remain, preserving source codes and original wording. Updated CSV import cannot recreate the duplicate identity. Both existing OS23000 category paths remain in Batch 029's single editor.

## Next approved OS — MySTEP

Implement the [approved single MySTEP template](../references/OS29000-mystep-reference.md) across its three existing catalogue variants and seven existing mappings: PPN/MySTEP 010812; Pendidikan Rendah Guru Ganti 030223 / PPM Ganti 030226; Pendidikan Awal Guru Ganti 030106 / PPM Ganti 030105; Pendidikan Menengah Guru Ganti 030305 / PPM Ganti 030309. No new contexts or variant-specific reference sets.

Reuse unchanged 11-row category and purpose sources and four academic-rate rows. Three Kategori1 and all six Tujuan Lantikan choices are independent; Kategori2 is Taraf Akademik. Unit fixed Orang; rate is the supplied **JUMLAH** (2638.35 / 2408.85 / 2179.35 / 1949.85) per person/month, never recomputed from Upah/KWSP/PERKESO. User quantities and budgeted months; exact draft preview = Bil. Unit × Kadar × Bilangan Bulan. No identified personnel inputs. Dates stay within Kitaran year and ordered; a shared date-cell/Tempoh-in-days display includes both endpoints and handles leap days, independent of manually entered budgeted months. It does not establish month conversion, entitlement or a financial rounding policy. Partial source drafts remain saveable; no official completeness certification.

Keep actual title-case headers, right-aligned/tabular grouped/two-decimal money and exact source values. Retain audited identity/revision/session/Fasa/role guards and source copy/removal. No rate changes, payroll component calculation, official output/totals/completeness, programme inheritance or submission. Earlier local execution remains unconfirmed.

## Verification


## Verification — passed before merge

Tested application head `005776cec1c85ebd3cdbabcb889b04925e31f228`; [CI 43679552938](https://github.com/muhdmuizz/sistem-abm/actions/runs/43679552938), job 132706974322: **474 tests / 3925 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, money formatter and Chromium regression passed. Cleanup tests exercise dependent-output deletion, source/Anggaran removal, retained canonical budgets, revision bump, permission isolation, mapping collision/repoint, idempotence and reimport. MySTEP HTTP tests cover all 18 category/purpose combinations, four monthly rates, seven mappings, exact persistence/edit/no-op, date-year/order/leap-day guards, partial drafts and existing access/Fasa/stale/removal guards. Inventory assertions now reflect 32 identities and eight actual OS23000 source mappings.

Artifact **14148295570** contains 264 PNGs, including 20 new captures (18 MySTEP and two clean utility-card states), replacing six preservation-specific captures. Representative final-head visual review covered 12 captures: clean utility card and MySTEP empty/populated/partial/read-only/validation at desktop/mobile widths. Internal horizontal scrolling, right-aligned grouped/two-decimal amounts, date controls and padded states retained. Native editable date formatting follows browser locale; read-only dates use dd/mm/yyyy. Not all old captures, middle-column mobile positions, expanded precision details or physical devices were inspected. Actual persistence/cleanup are covered by HTTP/database tests; browser validation responses are intercepted static fixtures, not live browser-save certification.

No application changes after tested head. Local update/migration pending; the cloud agent has not accessed Windows. Official outputs/totals/completeness, programme inheritance and submission remain pending.

## Local follow-through — pending

Keep server running. From a clean dev tree pull --ff-only, verify .env uses pgsql/sistem_abm, clear config, run normal php artisan migrate, then optimize:clear and Ctrl+F5. No fresh/reset, reference reseed, npm build or restart. The targeted migration removes only obsolete OS23000 Utiliti Pukal test budgets/reference; current canonical Pukal/Bukan Pukal budgets remain. Confirm one OS23000 card without Rekod sedia ada. Open eligible MySTEP; choose academic qualification, 2 units and 12 months: degree rate RM2,638.35 => **63,320.40**. Save/reopen, check date-year restriction, independent months and reference choices.


## Merge checkpoint

PR #72 merged at `8ac02d13c0304a115783fa49190634e0b27812f9`. Evidence-only final head `3f166ca15183f2edf597fe9c9b24d79b7151aaf9` preserves tested application code. This close-out changes Markdown only; no local execution or new test run claimed.
