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

## Section E consolidation — 28 November 2026

Owner approved one selection point: move package components out of C, derive Kaedah Pelaksanaan from OS29000 Pakej selection, and reveal Pakej or Iktisas detail options beneath selected OS29000 rows using existing references. Persist programme service choices and existing package components; no financial row generation or recalculation. New additive migration adds service selections. Existing package drafts are offered their prior selection when eligible. Verification and local acceptance pending.

## Owner-approved OS14000 inheritance — 28 November 2026

For programme-linked OS14000 only, generate exactly one row per Urus Setia (AKP) person from Section D, excluding Pegawai and all Peserta. Each row retains the originating numeric Gred level. Restrict Jawatan to REF-07 positions having a matching numeric Gred, and restrict the dependent Gred code to that same level (e.g. Gred 5 permits only valid codes at level 5). Amend Section D to change the inherited grade. Inherit workplace, programme name/purpose and start/end dates; Nama and Jawatan are manual, with Gred chosen from the restricted options. Kadar and Anggaran follow existing OS14000 calculations. Jam Sehari, Hari Setiap Kejadian, Kekerapan Sebulan and Bilangan Bulan remain manual. No automatic inference of chargeable duration. Direct OS entry unchanged. Approved design only; implementation deferred until remaining mappings are discussed. Owner reported Section E and editor linkage working locally; full automated verification remains separate.

## Owner-approved OS21000 inheritance — 28 November 2026

Applies to Perjalanan Sara Hidup (Umum), (Sukan) and (Kokurikulum). Generate one row per eligible person from both Section D Jemputan/ Urus Setia and Peserta breakdowns. Include Pegawai, Guru and AKP only, respecting the existing categories available in each group; exclude Murid Rendah, Murid Menengah and Lain-lain. Each generated row inherits its originating numeric Gred level, restricting Jawatan and dependent SSPA Gred codes to matching REF-07 entries exactly as for OS14000. Change the grade through Section D, not the linked editor. Existing travel components, rates and calculation rules remain unchanged; component-level additional row requirements are not amended by this decision. Design approved, implementation pending.
