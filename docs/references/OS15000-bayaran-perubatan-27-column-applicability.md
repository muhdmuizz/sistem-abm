# OS15000 — Bayaran Perubatan: Approved 27-Column Applicability Checkpoint

**Recorded:** 14 November 2026  
**Status:** Completed direct-entry **column applicability**; financial formula validation and Program/ Aktiviti inheritance remain pending.  
**Scope:** A separate Anggaran (OS): Kod Objek `15000`, Butiran Perbelanjaan **Bayaran Perubatan**, Aktiviti Kewangan **Pengurusan Pentadbiran Negeri (PPN)**, Kod Aktiviti `010804`. Do not conflate with **OS15000 — Faedah-faedah Kewangan Yang Lain**.

## Approved sources and constraints

Retain the existing [Bayaran Perubatan reference](OS15000-bayaran-perubatan-reference.md), [nine KATEGORI1 values and manual KADAR mode](OS15000-bayaran-perubatan-kategori.csv), and [global 27-column behaviours](OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md). This checkpoint records the user's subsequent explicit column decisions. It does not replace category reference records or prescribe new eligibility tests, quantities, rate caps or multipliers.

## Final 27-column mapping

| No. | Canonical column | Applicability | Behaviour / contextual meaning |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Auto from selected Anggaran (OS) |
| 02 | JPNT/ PPD | Active | Auto from authorised PTJ of Bidaan |
| 03 | SEKTOR/ SEKOLAH | Active | Manual controlled selection of Tempat Bertugas under authorised PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Inactive | — |
| 05 | TARIKH MULA | Conditional | **Tarikh Mula Kontrak** only for KATEGORI1 = Rawatan Penyakit Buah Pinggang |
| 06 | TARIKH TAMAT | Conditional | **Tarikh Tamat Kontrak** only for KATEGORI1 = Rawatan Penyakit Buah Pinggang |
| 07 | TEMPOH | Conditional | Calculated from relevant contract dates only for Rawatan Penyakit Buah Pinggang; precise duration unit / counting convention remains unspecified |
| 08 | NAMA | Active | Manual name |
| 09 | JAWATAN | Active | Existing shared reference drop-down |
| 10 | GRED | Active | Existing shared reference drop-down; full SSPA grade |
| 11 | KATEGORI1 | Active | Existing nine Jenis Kemudahan Perubatan options |
| 12 | KATEGORI2 | Inactive | No subordinate reference values supplied |
| 13 | KATEGORI3 | Inactive | No subordinate reference values supplied |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Active | OS-specific quantity; precise financial interpretation not yet verified |
| 17 | LOKASI URUSAN RASMI | Inactive | — |
| 18 | KADAR | Active | **Manual user-entered** for all nine KATEGORI1 options; no default/fixed rate |
| 19 | JARAK | Inactive | — |
| 20 | KEKERAPAN | Active | OS-specific frequency; precise multiplier not yet verified |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Inactive | — |
| 23 | BILANGAN BULAN | Active | OS-specific number of months; precise multiplier not yet verified |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Calculated, exact formula not yet approved |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan's Aktiviti Kewangan using approved mapping |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti alternative doorway, not configured in direct-entry exercise |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti alternative doorway, not configured in direct-entry exercise |

**Counts:** 13 active + 3 conditional + 9 inactive + 2 deferred = 27. **Zero unset** statuses.

## Conditional variant

Columns 05, 06 and 07 are present only when KATEGORI1 is **Rawatan Penyakit Buah Pinggang**. Column 05 is **Tarikh Mula Kontrak**, column 06 is **Tarikh Tamat Kontrak** (the user explicitly corrected an earlier duplicated “Mula”), and column 07 is calculated **TEMPOH**. For the other eight KATEGORI1 options these columns are inactive.

The exact nine options and their original order remain defined in `OS15000-bayaran-perubatan-kategori.csv`; this checkpoint does not invent KATEGORI2/3 values. The reference establishes `USER_INPUT_KADAR` for all nine; **do not silently impose an additional mandatory free-text Description field** simply because another OS15000 component has a no-prescribed-Kadar description rule.

## Outstanding / explicit boundaries

1. Full calculation for column 24, including whether/how BIL. UNIT (16), KEKERAPAN (20) and BILANGAN BULAN (23) combine; no blanket multiplication is approved.
2. Exact TEMPOH calculation convention, and whether a duration affects the financial result.
3. Program/ Aktiviti alternative-entry inheritance, including columns 26–27.
4. Operational implementation, validation, database changes, and testing.

**Documentation checkpoint only.** A complete column-applicability matrix is not approval of financial formulas or production readiness.
