# Reference 07 — Jawatan, Gred, Gaji and KGT

**Status:** Approved reference-data cleanup and VU7 calculation requirement, recorded 12 November 2026.  
**Data:** [jawatan-gred-gaji-kgt.csv](jawatan-gred-gaji-kgt.csv) (131 unique rows).  
**Scope:** Reference decisions and source catalogue only; no application/database modification is implied.

## Universal applicability across Anggaran (OS)

**User-confirmed, 12 November 2026:** This **single shared** `Jawatan, Gred, Gaji dan KGT` reference applies to **every Anggaran (OS) where its personnel/salary attributes are relevant**; it is **not an OS14000-only reference**. Reuse the 131-record CSV rather than duplicating it for each OS.

- **Jawatan and full SSPA Gred** are separate personnel-reference attributes, **never `Kategori1`, `Kategori2`, `Kategori3` or any other category level**.
- Where relevant, OS-specific rules may reference `Gaji Minimum`, `Gaji Maksimum`, `Gaji Median` or `KGT`. Do **not** assume every field or formula applies to every OS.
- For **OS14000 — Bayaran Lebih Masa**, the owner separately confirmed `Kadar = Gaji Median × 12 ÷ 2504`; see [OS14000-reference-consolidated.md](OS14000-reference-consolidated.md).
- Other OS-specific uses, grade mappings and calculations continue to require their own confirmed reference rules. Preserve special source grade `C` without assuming an equivalence.

## 1. Authoritative source and cleanup

The user's **second, KGT-inclusive** Jawatan/Gred dataset supersedes the earlier submission.

- Source input: **136 rows**.
- Remove exactly **five exact duplicate records**, leaving **131 unique rows**; retain each distinct Jawatan + SSPA record.
- Remove source columns **`SSPA2`** and **`Column1`**.
- Retain these seven columns: **KLASIFIKASI PERKHIDMATAN**, **SKIM PERKHIDMATAN/ JAWATAN**, **SSPA**, **GAJI MINIMUM**, **GAJI MAKSIMUM**, **GAJI MEDIAN**, **KGT**.
- Money fields use RM amounts; the fixed-value **KGT** column contains RM amounts where supplied, rather than percentages.

## 2. Pembantu Akauntan — all four records present in source catalogue

The supplied **reference catalogue already contains all four Pembantu Akauntan grades (W1–W4)**. Preserve the following records; no claim is made about any application database:

| Jawatan | SSPA | Gaji Minimum (RM) | Gaji Maksimum (RM) | Gaji Median (RM) | KGT tetap (RM) |
| --- | --- | ---: | ---: | ---: | ---: |
| Pembantu Akauntan | W1 | 1,500.00 | 4,680.00 | 3,090.00 | 100.00 |
| Pembantu Akauntan | W2 | 2,220.00 | 5,770.00 | 3,995.00 | 115.00 |
| Pembantu Akauntan | W3 | 2,790.00 | 6,090.00 | 4,440.00 | 125.00 |
| Pembantu Akauntan | W4 | 2,930.00 | 6,410.00 | 4,670.00 | 130.00 |

**Reference status:** W1–W4 are present in the committed 131-row CSV. Their presence does not establish whether a future application import, lookup or user interface is implemented.

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

## 4. Future implementation considerations (not completed)

1. When implementing Jawatan/Gred references, use the full 131-row catalogue, including Pembantu Akauntan W1–W4. **No assertion is made about the current contents of an application database.**
2. Implement VU7's percentage-based KGT calculation rather than fixed-KGT XLOOKUP; verify the salary base and rounding rules before implementation.
3. Validate future reference imports, calculations and Jawatan/Gred selection against the committed catalogue.

**Not performed by this commit:** changes to Laravel code, database data/schema, AD documents, deployment or any payroll calculation engine.
