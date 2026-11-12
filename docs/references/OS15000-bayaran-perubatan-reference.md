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
- The user supplied no further eligibility, limit, formula, quantity or other calculation requirements for Bayaran Perubatan; none should be inferred here.
- This record captures a user-approved reference and input rule. It does **not** independently add an extra free-text description requirement for these nine categories. The earlier OS15000 rule requiring a description and Kadar for **Kemudahan without a prescribed Kadar** is documented separately in `OS15000-jawatan-gred-and-unspecified-kadar-rules.md`; do not silently conflate the two reference components.

## Status and boundaries

This completes collection of the **Bayaran Perubatan** reference currently supplied. The other OS15000 reference component (**Faedah-faedah Kewangan Yang Lain**) is separately committed.

**Documentation only:** No changes to Architecture Decisions, Laravel application, database, financial calculations or deployed features are implied by this document.
