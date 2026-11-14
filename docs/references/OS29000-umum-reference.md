# OS29000 — Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti (Umum)

**Owner-approved reference and Excel mapping:** 12 November 2026. **Scope:** documentation of supplied categories, dedicated Gred Penceramah/ Fasilitator and mandatory Kadar treatment only. No wizard, database, or export implementation.

## Field mapping and separation from universal personnel reference

| Field | Meaning |
| --- | --- |
| **Kategori1** | Source `KATEGORI` |
| **Kategori2** | Source `PERKHIDMATAN`, selected within Kategori1 |
| **Kategori3** | **Not required** by this OS29000 (Umum) reference; do not repurpose it for Gred Penceramah/ Fasilitator |
| **Gred Penceramah/ Fasilitator** | Dedicated classification and dedicated **existing Excel output column**; applicable only to `Bayaran Penceramah` and `Bayaran Fasilitator` |
| **Jawatan & Gred** | Separate universal personnel reference (Reference 07); **must not be conflated** with Gred Penceramah/ Fasilitator |
| **Kadar** | Mandatory; automatic fixed lookup for 18 rows or user-entered for 4 rows |

The existing approved 27-column Excel output includes a dedicated `GRED PENCERAMAH/ FASILITATOR` column (previously identified as column 15). **No new Excel column is requested.** Do not move the speaker classification into `KATEGORI3` merely to avoid the dedicated column.

## Penceramah/ Fasilitator classification and Kadar

These labels belong to the dedicated speaker/facilitator classification, **not** Reference 07 `Gred`. Preserve source grouping/range labels exactly (especially `Kumpulan P&P Gred 11-13`).

| Gred Penceramah/ Fasilitator | Penceramah (RM) | Fasilitator (RM) |
| --- | ---: | ---: |
| Kumpulan Pengurusan Tertinggi | 300.00 | 100.00 |
| Kumpulan P&P Gred 14 | 200.00 | 100.00 |
| Kumpulan P&P Gred 11-13 | 150.00 | 90.00 |
| Kumpulan P&P Gred 9-10 | 120.00 | 80.00 |
| Kumpulan Sokongan | 80.00 | 60.00 |

No payment unit (per hour/session etc.) was supplied; do not invent one.

## Other supplied services

| Kategori1 | Kategori2 | Kadar (RM) | Mode |
| --- | --- | ---: | --- |
| Bayaran Yuran Bengkel/ Kursus/ Taklimat/ Seminar | Bayaran Yuran | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Mesyuarat) | 8.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Program) | 18.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Majlis Persaraan) | 60.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Hadiah Penghargaan Pesara | 600.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Medal | 10.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Piala | 50.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Dobi | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan (Banner) | 150.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan (Sijil) | 1.90 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Lain-lain | *(blank)* | User input |

## Reference files and boundaries

- [Supplied-source reference, standardised whitespace](OS29000-umum-kadar-source.csv): 22 rows; blanks preserved, not set to zero.
- [Reference choices and rate modes](OS29000-umum-kategori-kadar.csv): 22 rows, **18 automatic**, **4 mandatory user-entered** Kadar.
- Mandatory applicable rates follow the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).
- Do not add eligibility checks, grade-to-speaker-group conversion rules, rate units, price changes, or extra categories without separate decisions.
- No changes to existing Excel column layout, OS catalogue, Kod Aktiviti mapping, other OS29000 variants, application or database.


## Owner-approved 30-column Anggaran and calculation specification — 15 November 2026

This later decision supersedes earlier **pending** statements on the rate unit, grade-to-payment classification, quantity formula and personnel applicability. The original 22 reference records, their **18 AUTO_REFERENCE and 4 USER_INPUT_KADAR** modes, prices and category labels remain unchanged. This applies **only to OS29000 Umum**, not its separate Kod Aktiviti variants.

### Canonical 30-column applicability

| Status | Positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 04, 05, 06, 07, 14, 15, 17, 19, 21, 27, 28 | 14 |
| Conditional | 11, 12, 13, 18 | 4 |
| Inactive | 08, 09, 10, 16, 20, 22, 23, 24, 25, 26 | 10 |
| Deferred | 29, 30 | 2 |

01, 02, 03 and 28 retain the shared authorised organisational references; 04–06 (name/purpose, start/end dates) are Active and 07 **TEMPOH** is Active/calculated under existing duration rules. 14 **KATEGORI1** and dependent 15 **KATEGORI2** use existing choices; 16 **KATEGORI3** is Inactive. 17 **UNIT**, 19 **BIL. UNIT**, 21 **KADAR**, and 27 calculated annual amount are Active. Columns 08–10 export blank for this OS. Columns 29–30 remain Deferred. Contextual editor labels do not change any canonical Excel header.

### UNIT and BIL. UNIT contextual labels

| Existing service group (design grouping only) | UNIT (17) | BIL. UNIT (19) editor label |
| --- | --- | --- |
| Bayaran Penceramah / Bayaran Fasilitator | Jam | BIL. JAM |
| Bayaran Yuran | Peserta | BIL. PESERTA |
| Makan Minum (Mesyuarat / Program / Majlis Persaraan) | Orang | BIL. ORANG |
| Hadiah Penghargaan Pesara / Medal / Piala | Unit | BIL. UNIT |
| Dobi | Unit | BIL. UNIT |
| Percetakan (Banner / Sijil / general) and Lain-lain | Unit | BIL. UNIT |

These six groupings are **not additional category levels**. For Penceramah/Fasilitator the approved reference **KADAR is RM per hour**, UNIT=Jam, column 19 is BIL. JAM, and canonical column 24 **JAM stays Inactive**, avoiding duplicate hour inputs.

### Approved calculation and KADAR

For all six groups: **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR**. The specified UNIT determines the user's quantity meaning. All 18 reference rate entries remain automatic, and the four original blank KADAR services (Bayaran Yuran, Dobi, Percetakan, Lain-lain) require mandatory user-entered KADAR; do not replace source blanks with zero. Do not add unapproved rounding or eligibility rules.

### Conditional personnel-field decision

- 11 **NAMA**, 12 **JAWATAN**, 13 **GRED** are **mandatory** for individual **Bayaran Penceramah**, **Bayaran Fasilitator**, and **Bayaran Yuran** entries.
- For **Hadiah Penghargaan Pesara**, NAMA/JAWATAN/GRED are **Inactive**; the user enters BIL. UNIT and the prescribed RM600 rate applies. These fields are Inactive for all other service entries.
- 18 **GRED PENCERAMAH/ FASILITATOR** is **conditional only** for Bayaran Penceramah / Bayaran Fasilitator; it is automatically derived and output in its own dedicated canonical column, not an independently selected category and not part of KATEGORI3.
- For Bayaran Yuran, the mandatory personnel fields do **not** trigger the speaker payment classification; its KADAR remains manual.

### Approved SSPA grade-to-payment classification

Use the single 131-row [shared JAWATAN/GRED reference](REF-07-jawatan-gred-gaji-kgt.md). Select **JAWATAN → dependent GRED**, then derive the payment classification from the **numeric SSPA grade level**, without requiring a second selection:

| SSPA grade | GRED PENCERAMAH/ FASILITATOR |
| --- | --- |
| VU7 | Kumpulan Pengurusan Tertinggi |
| Numbered grade 14 | Kumpulan P&P Gred 14 |
| Numbered grades 11–13 | Kumpulan P&P Gred 11-13 |
| Numbered grades 9–10 | Kumpulan P&P Gred 9-10 |
| Numbered grades 1–8 | Kumpulan Sokongan |

The current shared reference has **131 rows / 82 distinct grade codes**, all syntactically classifiable by this rule (including VU7). Select applicable hourly KADAR by **Bayaran Penceramah vs Bayaran Fasilitator + derived classification** using the unchanged ten reference rate combinations. Do not silently classify unsupported future grade codes. This is a **budget-rate lookup rule, not an independent payment eligibility approval**.

### Implementation and change-control boundary

Preserve AD-004's single Anggaran wizard and stable Butiran IDs, AD-008's 30-column Perbelanjaan output, historical snapshots and authorised organisational scope. This documentation commit does **not** change Laravel, database schema/data, CSV sources, reference amounts or the actual exporter; AD-008 remains Draft.
