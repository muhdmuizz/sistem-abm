# OS15000 — Bayaran Perubatan reference

**Status:** Confirmed by user on 12 November 2026.  
**Aktiviti Kewangan:** Pengurusan Pentadbiran Negeri (PPN)  
**Kod Aktiviti:** `010804`  
**Kod Objek:** `15000`  
**Butiran Perbelanjaan:** Bayaran Perubatan

## Jenis Bayaran Perubatan

The user supplied the following nine selectable categories, reproduced in the original order:

| No. | Jenis Bayaran Perubatan |
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
