# OS23000 — Perhubungan dan Utiliti: category and rate reference

**Owner-confirmed categories:** 12 November 2026. **Owner-approved column applicability and calculation:** 14 November 2026. **Scope:** Reference and Anggaran specification only; no application or database implementation.

## Category hierarchy

- **Kategori1:** Kategori — `Pukal` or `Bukan Pukal`.
- **Kategori2:** Utiliti — depends on selected Kategori1.
- **Kadar:** **Mandatory user-entered** for each of the seven permitted combinations. No prescribed rate or Gred-based lookup was provided.

| Kategori1 | Kategori2 |
| --- | --- |
| Pukal | Air |
| Pukal | Elektrik |
| Pukal | Telefon/ Faks |
| Pukal | Pembetungan |
| Bukan Pukal | Pos/ Mesin Franking |
| Bukan Pukal | Bil Telefon |
| Bukan Pukal | Lain-lain |

**Structured reference:** [OS23000-perhubungan-utiliti-kategori.csv](OS23000-perhubungan-utiliti-kategori.csv) — seven rows.

Source normalisation: the source's `Bukan   Pukal` (repeated space) is standardised to `Bukan Pukal`, consistent with the other supplied rows. All seven options, including their original slash wording, are retained.

## Identity and mapping boundary

The owner explicitly approved **one OS23000 — Perhubungan dan Utiliti** whose Kategori1 distinguishes **Pukal** from **Bukan Pukal**. The existing master source catalogue also contains an older `23000 — Utiliti Pukal` description; this must **not automatically create a third category or an additional independent wizard**. Existing Kod Aktiviti mappings are **source records**, and their values are not changed by this category-level decision.

The OS catalogue and legacy mapping CSVs are deliberately left unchanged pending a separate reconciliation of their source associations; this does not change or qualify the **two confirmed Kategori1 values**. No inferred Kod Aktiviti, category, eligibility or formula is introduced.

## Boundaries

- Jawatan and Gred are shared personnel attributes where relevant, **not categories**. They are not required for the OS23000 rate lookup specified here.
- The approved BILANGAN BULAN = 12 and ANGGARAN JUMLAH SETAHUN = KADAR × 12 rule is recorded below. No further quantity, date, eligibility, authorisation or rounding rule is approved.
- This commit does **not** change ADs, Laravel, PostgreSQL, or the existing master source data.

## Approved 27-column applicability and calculation — 14 November 2026

The following single set of applicability decisions applies to **all seven approved KATEGORI1/KATEGORI2 combinations** under the same OS23000 Anggaran. Preserve the canonical 27-column Excel headers and existing global rules.

| No. | Canonical column | Status | Approved behaviour |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Automatically from selected Anggaran (OS) |
| 02 | JPNT/ PPD | Active | Automatically from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Controlled Tempat Bertugas selection under authorised PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Inactive | — |
| 05 | TARIKH MULA | Inactive | — |
| 06 | TARIKH TAMAT | Inactive | — |
| 07 | TEMPOH | Inactive | — |
| 08 | NAMA | Inactive | — |
| 09 | JAWATAN | Inactive | — |
| 10 | GRED | Inactive | — |
| 11 | KATEGORI1 | Active | Pukal / Bukan Pukal |
| 12 | KATEGORI2 | Active | Exactly the seven dependent category values in the existing source CSV |
| 13 | KATEGORI3 | Inactive | — |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Inactive | — |
| 17 | LOKASI URUSAN RASMI | Inactive | — |
| 18 | KADAR | Active | Mandatory user-entered value; no lookup or fabricated default |
| 19 | JARAK | Inactive | — |
| 20 | KEKERAPAN | Inactive | — |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Inactive | — |
| 23 | BILANGAN BULAN | Active | **Fixed at 12**; not a frequency or adjustable quantity |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | **KADAR × 12**, calculated automatically |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Automatically from Bidaan Aktiviti Kewangan |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti pathway |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti pathway |

**Reconciliation:** 9 Active + 16 Inactive + 2 Deferred = 27.

### Calculation contract

For every permitted **Pukal** and **Bukan Pukal** combination:

- **KADAR** (column 18): mandatory user entry.
- **BILANGAN BULAN** (column 23): fixed value **12**.
- **ANGGARAN JUMLAH SETAHUN** (column 24): automatically **KADAR × BILANGAN BULAN = KADAR × 12**.
- Do not infer additional quantities, frequency multipliers, rounding conventions, grade-based rates, or eligibility rules.
- Existing category reference CSV, catalogue and global rules are unchanged.

**Status:** Specification committed; Laravel application, PostgreSQL, master data, and Excel implementation are not changed by this documentation update.
