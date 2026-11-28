# Batch 052 — Program/ Aktiviti integration

Status: scope discovery and programme form refinement in [PR #119](https://github.com/muhdmuizz/sistem-abm/pull/119). Programme-to-Butiran Anggaran linkage is pending.

## Programme OS choices — owner decision, 26 November 2026

The owner specified the following seven groups for the programme OS checklist.

| Kod Objek | Catalogue scope |
| --- | --- |
| OS14000 | Bayaran Lebih Masa |
| OS21000 | Perjalanan Sara Hidup (Umum), (Sukan), (Kokurikulum) |
| OS24000 | Sewaan (Umum) only; canonical catalogue label Sewaan |
| OS25000 | Bahan-bahan Makanan dan Minuman |
| OS27000 | Bekalan Alat Pejabat only |
| OS29000 | Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti (Umum) |
| OS29000 | Pakej (Mesyuarat/ Bengkel/ Kursus) |

Use the canonical OS identities and existing PTJ, Aktiviti Kewangan, Fasa and role rules. OS21000 Tuntutan Perbelanjaan Pertukaran and other unlisted OS are outside the programme checklist. OS24000 Sewaan Mesin Fotostat dan Riso, OS29000 Bacaan Meter, OS27000 Pakaian Seragam and OS27000 Bekalan dan Bahan-bahan Lain are excluded from the programme checklist. The standalone Fotostat rental/meter dependency and financial input rules stay in effect. The Nama Syarikat correction is tracked separately in PR #120.

AD-007 remains the approved shared-wizard and programme-inheritance contract. Inheritance and calculation mappings must be resolved per OS. Pakej-only direct-entry restriction is pending the owner's decision, as is the precise automatic selection/locking behaviour.

## Owner direction — 27 November 2026

Use one consistent checklist workflow; checking an OS should generate actual programme-linked Butiran Anggaran using reliable existing programme data, then expose only remaining OS-specific inputs through the existing editor. OS14000 generation is restricted to Urus Setia (AKP), one row per eligible person. For OS21000, the owner illustrated one row per participant (45 participants produce 45 rows); exact eligible Urus Setia/participant categories per variant remain to be confirmed. For other OS, initial blank/inherited rows and category-dependent quantity prepopulation are proposed, not approved mappings. Do not invent rates or infer rental, printing, hall or other requirements from headcounts. Preserve saved input, stable identities, permission/phase checks and explicit deletion safeguards. Financial integration is not implemented. UI placement and precise mappings remain pending.

## Owner-authorised implementation slice — 28 November 2026

Steps 1–2 only: persistent programme OS checklist, scoped links into existing editors, and stable nullable programme identity on manually saved Butiran. Direct editor entry remains available; no programme registration prerequisite or automatic name matching. Existing rows remain unlinked. Existing OS activation workflow remains separate; selected inactive OS must be activated before opening. No row generation, inheritance mapping, new rates, totals or programme completeness automation. Programme workplace changes and removing a selected OS are blocked when linked drafts would be affected. Additive migration; CI and local acceptance pending. Backup: owner confirmed PostgreSQL dump/list and Git bundle verification on 27 November; encryption verification and isolated restore remain pending under AD-012.
