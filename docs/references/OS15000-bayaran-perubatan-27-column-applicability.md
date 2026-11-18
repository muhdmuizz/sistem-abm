# OS15000 — Bayaran Perubatan: Approved 27-Column Applicability Checkpoint

**Recorded:** 14 November 2026  
**Status:** Completed direct-entry **column applicability**; financial formula and duration now owner-approved (18 November 2026); implementation verification and Program/ Aktiviti inheritance remain pending.  
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
| 07 | TEMPOH | Conditional | Calculated from relevant contract dates only for Rawatan Penyakit Buah Pinggang; inclusive calendar years, months and days, using the existing photocopier-contract convention; independent of manual Bilangan Bulan |
| 08 | NAMA | Active | Manual name |
| 09 | JAWATAN | Active | Existing shared reference drop-down |
| 10 | GRED | Active | Existing shared reference drop-down; full SSPA grade |
| 11 | KATEGORI1 | Active | Existing nine Jenis Kemudahan Perubatan options |
| 12 | KATEGORI2 | Inactive | No subordinate reference values supplied |
| 13 | KATEGORI3 | Inactive | No subordinate reference values supplied |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Active | Manually entered quantity factor in Bil. Unit × Kadar × Kekerapan × Bilangan Bulan |
| 17 | LOKASI URUSAN RASMI | Inactive | — |
| 18 | KADAR | Active | **Manual user-entered** for all nine KATEGORI1 options; no default/fixed rate |
| 19 | JARAK | Inactive | — |
| 20 | KEKERAPAN | Active | Manually entered frequency factor in the approved four-factor formula |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Inactive | — |
| 23 | BILANGAN BULAN | Active | Manually entered number of months; independent of contract dates/duration |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Calculated: Bil. Unit × Kadar × Kekerapan × Bilangan Bulan for all nine categories |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan's Aktiviti Kewangan using approved mapping |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti alternative doorway, not configured in direct-entry exercise |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti alternative doorway, not configured in direct-entry exercise |

**Counts:** 13 active + 3 conditional + 9 inactive + 2 deferred = 27. **Zero unset** statuses.

## Conditional variant

Columns 05, 06 and 07 are present only when KATEGORI1 is **Rawatan Penyakit Buah Pinggang**. Column 05 is **Tarikh Mula Kontrak**, column 06 is **Tarikh Tamat Kontrak** (the user explicitly corrected an earlier duplicated “Mula”), and column 07 is calculated **TEMPOH**. For the other eight KATEGORI1 options these columns are inactive.

The exact nine options and their original order remain defined in `OS15000-bayaran-perubatan-kategori.csv`; this checkpoint does not invent KATEGORI2/3 values. The reference establishes `USER_INPUT_KADAR` for all nine; **do not silently impose an additional mandatory free-text Description field** simply because another OS15000 component has a no-prescribed-Kadar description rule.

## Outstanding / explicit boundaries

1. Formula and duration gaps resolved by the explicit owner approval below; no further multiplier or rounding policy is implied.
2. Financial output/completeness/submission integration remains separate from draft source editing.
3. Program/ Aktiviti alternative-entry inheritance, including columns 26–27.
4. Operational implementation, validation, database changes, and testing.

**Documentation checkpoint only.** A complete column-applicability matrix is not approval of financial formulas or production readiness.


## Owner approval — 18 November 2026

Owner answered **YES to both** proposed rules:

1. **Anggaran Setahun = Bil. Unit × Kadar × Kekerapan × Bilangan Bulan**, for **all nine medical categories**. All four factors are entered independently; no default/extra multiplier or new quantity unit is inferred.
2. **Rawatan Penyakit Buah Pinggang** contract duration displays **years, months and days**, using the existing **inclusive photocopier-contract calendar convention** (complete clamped years, then months, then remaining days). **Bilangan Bulan stays manually entered** and is never populated or changed by dates/duration. Duration does not change the financial result.

The existing rule restricting Anggaran dates to the Kitaran year remains; no cross-year medical contract or additional overall-contract columns are introduced. Other eight categories have no contract dates/duration; clear dates when switching away and reject inactive injected dates. Purpose remains Inactive. Partial source drafts may be saved; a missing financial factor withholds the draft amount, without treating absent values as zero. No extra description, eligibility, rate cap, quantity convention or rounding rule is added. Keep exact decimals and accounting presentation. The historical 27-column matrix above maps to the current 30-column contract under AD-008; shared headers/order remain unchanged.

Implementation/evidence: [Batch 045](../development/batch-045-medical-spreadsheet.md). Programme inheritance and official output/totals/completeness/submission remain deferred.
