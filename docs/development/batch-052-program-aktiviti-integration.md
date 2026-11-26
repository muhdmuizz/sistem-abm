# Batch 052 — Program/ Aktiviti integration

Status: scope discovery and programme form refinement in [PR #119](https://github.com/muhdmuizz/sistem-abm/pull/119). Programme-to-Butiran Anggaran linkage is pending.

## Programme OS choices — owner decision, 26 November 2026

The owner specified the following seven groups for the programme OS checklist.

| Kod Objek | Catalogue scope |
| --- | --- |
| OS14000 | Bayaran Lebih Masa |
| OS21000 | Perjalanan Sara Hidup (Umum), (Sukan), (Kokurikulum) |
| OS24000 | Sewaan; Sewaan Mesin Fotostat dan Riso |
| OS25000 | Bahan-bahan Makanan dan Minuman |
| OS27000 | All eligible catalogue variants under OS27000, pending any further variant-specific exclusion by the owner |
| OS29000 | Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti (Umum) |
| OS29000 | Pakej (Mesyuarat/ Bengkel/ Kursus) |

Use the canonical OS identities and existing PTJ, Aktiviti Kewangan, Fasa and role rules. OS21000 Tuntutan Perbelanjaan Pertukaran and other unlisted OS are outside the programme checklist. OS29000 Bacaan Meter is a dependent item of OS24000 Sewaan Mesin Fotostat dan Riso, not a separate selectable choice; preserve the existing linkage and financial input rules. The Nama Syarikat correction is tracked separately in PR #120.

AD-007 remains the approved shared-wizard and programme-inheritance contract. Inheritance and calculation mappings must be resolved per OS. Pakej-only direct-entry restriction is pending the owner's decision, as is the precise automatic selection/locking behaviour.
