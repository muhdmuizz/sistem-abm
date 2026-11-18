# OS15000 — Bayaran Perubatan reference

**Status:** Confirmed by user on 12 November 2026.  
**Aktiviti Kewangan:** Pengurusan Pentadbiran Negeri (PPN)  
**Kod Aktiviti:** `010804`  
**Kod Objek:** `15000`  
**Butiran Perbelanjaan:** Bayaran Perubatan

## Jenis Kemudahan Perubatan — Kategori1

**User-confirmed classification:** Each of the following nine Jenis Kemudahan Perubatan is a selectable **Kategori1** under the distinct Anggaran (OS) **OS15000 — Bayaran Perubatan**. The nine labels remain exactly as supplied, in the original order; no Kategori2 or Kategori3 values have been supplied.

**Machine-readable reference:** [OS15000-bayaran-perubatan-kategori.csv](OS15000-bayaran-perubatan-kategori.csv). Kadar mode: `USER_INPUT_KADAR` for all nine Kategori1 values.



| No. | Kategori1 — Jenis Kemudahan Perubatan |
| ---: | --- |
| 1 | Pembekalan Ubat |
| 2 | Pembekalan Alat |
| 3 | Perkhidmatan Perubatan |
| 4 | Rawatan Penyakit Buah Pinggang |
| 5 | Rawatan Kemandulan |
| 6 | Rawatan Kepakaran |
| 7 | Rawatan di Institut Jantung Negara (IJN) Sdn. Bhd. |
| 8 | Rawatan Kecemasan |
| 9 | Rawatan di Luar Negeri |

## Kadar

- **Kadar is entered by the user** for every Jenis Bayaran Perubatan category.
- No predetermined rate or rate lookup is provided for these categories.
- Historical source collection supplied no further calculation requirements. The later [applicability and 18 November formula/duration approval](OS15000-bayaran-perubatan-27-column-applicability.md) supersedes the earlier formula gap. No additional eligibility, cap, quantity-unit or rounding policy is inferred.
- This record captures a user-approved reference and input rule. It does **not** independently add an extra free-text description requirement for these nine categories. The earlier OS15000 rule requiring a description and Kadar for **Kemudahan without a prescribed Kadar** is documented separately in `OS15000-jawatan-gred-and-unspecified-kadar-rules.md`; do not silently conflate the two reference components.

## Status and boundaries

This completes collection of the **Bayaran Perubatan** reference currently supplied. The other OS15000 reference component (**Faedah-faedah Kewangan Yang Lain**) is separately committed.

**Documentation only:** No changes to Architecture Decisions, Laravel application, database, financial calculations or deployed features are implied by this document.


## Subsequent owner approval — 18 November 2026

All nine categories use **Bil. Unit × manual Kadar × Kekerapan × manually entered Bilangan Bulan**. Kidney-treatment contract duration uses the existing inclusive years/months/days convention and never determines months or amount. Existing Kitaran-year date limits and inactive dates for the other eight categories remain. No category/rate CSV changes. See [approved calculation/duration checkpoint](OS15000-bayaran-perubatan-27-column-applicability.md) and [Batch 045](../development/batch-045-medical-spreadsheet.md).
