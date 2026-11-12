# Reference 07 — Jawatan, Gred, Gaji and KGT

**Status:** Approved reference-data cleanup and VU7 calculation requirement, recorded 12 November 2026.  
**Data:** [jawatan-gred-gaji-kgt.csv](jawatan-gred-gaji-kgt.csv) (131 unique rows).  
**Scope:** Reference decisions and source catalogue only; no application/database modification is implied.

## 1. Authoritative source and cleanup

The user's **second, KGT-inclusive** Jawatan/Gred dataset supersedes the earlier submission.

- Source input: **136 rows**.
- Remove exactly **five exact duplicate records**, leaving **131 unique rows**; retain each distinct Jawatan + SSPA record.
- Remove source columns **`SSPA2`** and **`Column1`**.
- Retain these seven columns: **KLASIFIKASI PERKHIDMATAN**, **SKIM PERKHIDMATAN/ JAWATAN**, **SSPA**, **GAJI MINIMUM**, **GAJI MAKSIMUM**, **GAJI MEDIAN**, **KGT**.
- Money fields use RM amounts; the fixed-value **KGT** column contains RM amounts where supplied, rather than percentages.

## 2. Pembantu Akauntan — all four records present in source catalogue

The user identified **Pembantu Akauntan** as missing from the **application database**. It is **not missing from the uploaded source**. Preserve all four records:

| Jawatan | SSPA | Gaji Minimum (RM) | Gaji Maksimum (RM) | Gaji Median (RM) | KGT tetap (RM) |
| --- | --- | ---: | ---: | ---: | ---: |
| Pembantu Akauntan | W1 | 1,500.00 | 4,680.00 | 3,090.00 | 100.00 |
| Pembantu Akauntan | W2 | 2,220.00 | 5,770.00 | 3,995.00 | 115.00 |
| Pembantu Akauntan | W3 | 2,790.00 | 6,090.00 | 4,440.00 | 125.00 |
| Pembantu Akauntan | W4 | 2,930.00 | 6,410.00 | 4,670.00 | 130.00 |

**Implementation follow-up (not completed by this commit):** check the reference-data seeder/import and Jawatan autocomplete against W1–W4 before assuming new salary reference entries must be created. This record does **not** claim a database repair.

## 3. Pengarah Pendidikan Negeri — VU7

The original row had a grade but no salary amounts. Populate the reference with the verified figures adopted by the user:

| Field | Reference |
| --- | --- |
| Klasifikasi Perkhidmatan | PENDIDIKAN (D) |
| Skim Perkhidmatan/ Jawatan | PENGARAH PENDIDIKAN NEGERI |
| SSPA | VU7 |
| Gaji Minimum | RM7,990.00 |
| Gaji Maksimum | RM22,040.00 |
| Gaji Median (arithmetic midpoint) | RM15,015.00 |
| KGT type | **Calculated percentage** |
| KGT rate | **9%** |
| Fixed-value KGT cell in CSV | **Blank (intentional)** |

**Mandatory implementation decision:** VU7 KGT must be **calculated by the system rather than sourced by XLOOKUP of a fixed RM amount**. The rule is percentage-based: nominal increment = **applicable salary basis × 9%**.

- Do **not** write `9`, `0.09`, or a computed amount into the existing fixed-RM **KGT** column in the CSV; an empty cell intentionally signals no fixed lookup amount.
- Model the VU7 rate and calculation mode separately when building the application's financial calculations.
- A median salary is a **reference midpoint**, **not automatically the officer's actual salary** or the approved basis for individual KGT.
- The exact applicable salary basis, rounding and maximum/ceiling behaviour should be confirmed against relevant official implementation rules before implementing payroll-like calculations; none is newly decided here.
- The imported source does not independently establish that every Pengarah Pendidikan Negeri position is VU7. This reference preserves the grade specified by the user.

**Source provenance for VU7 salary range and rate:** `https://belanjawan.mof.gov.my/pdf/belanjawan2026/sp/LAMPIRAN-A.pdf` (MOF, Buku Senarai Perjawatan 2026, Lampiran A); figures and 9% rate discussed and accepted in conversation.

## 4. Outstanding implementation tasks

1. Verify whether the existing application database lacks Pembantu Akauntan W1–W4 and repair its import/seeder if required.
2. Implement VU7's percentage-based KGT calculation rather than fixed-KGT XLOOKUP; verify the salary base and rounding rules.
3. Validate any application-specific calculations, reference imports and autocomplete behaviour against the 131-row catalogue.

**Not performed by this commit:** changes to Laravel code, database data/schema, AD documents, deployment or any payroll calculation engine.
