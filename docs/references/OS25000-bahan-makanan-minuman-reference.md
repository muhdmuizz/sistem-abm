# OS25000 — Bahan-bahan Makanan dan Minuman: approved reference

**Owner-confirmed reference:** 12 November 2026. **Approved column applicability and calculation:** 14 November 2026. **Scope:** Specification only, not application implementation.

## Category and reference values

**Kategori1 = Jenis Bekalan.** The four supplied items are:

| Kategori1 — Jenis Bekalan | Unit (auto-populated) | Kadar (RM; reference) |
| --- | --- | ---: |
| Air Minuman | Kotak | 15.00 |
| Gula-gula | Balang/ Paket | 15.00 |
| Biskut | Kotak/ Paket | 15.00 |
| Kekacang | Kotak/ Paket | 15.00 |

**Machine-readable file:** [OS25000-bahan-makanan-minuman-kategori.csv](OS25000-bahan-makanan-minuman-kategori.csv).

## Confirmed behaviour

- The user chooses a **Kategori1 — Jenis Bekalan** item.
- **Unit is automatically populated** from the corresponding reference row; the user does not need to select it separately.
- **Kadar is automatically populated** from the matching reference row (RM15.00 for each currently supplied item). Its presence remains mandatory under the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).
- Retain the supplied compound unit labels `Balang/ Paket` and `Kotak/ Paket` as a **single reference value** each. Do not split them into additional categories or infer a unit-choice workflow.
- No **Kategori2** or subsequent category levels have been supplied.

Source formatting note: repeated interior spaces from the pasted table were normalised in the CSV without changing substantive labels or values.

## Scope and limits

- The approved quantity and calculation rule appears below; no additional items, rates, multipliers, eligibility constraints or rounding assumptions are approved.
- This does not modify the master Anggaran (OS) catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel, or PostgreSQL.

## Approved 30-column applicability — 14 November 2026

The following applies to all four existing KATEGORI1 Jenis Bekalan options. It follows the current **30-column Perbelanjaan** canonical order in [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md). Preserve the original automatic UNIT and KADAR reference values and all global rules.

| No. | Canonical column | Status | Approved behaviour |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Auto from Anggaran (OS) |
| 02 | JPNT/ PPD | Active | Auto from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Controlled Tempat Bertugas selection |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Inactive | — |
| 05 | TARIKH MULA | Inactive | — |
| 06 | TARIKH TAMAT | Inactive | — |
| 07 | TEMPOH | Inactive | — |
| 08 | TARIKH MULA KONTRAK KESELURUHAN | Inactive | Reserved for OS24000 Sewaan (Mesin Fotostat) |
| 09 | TARIKH TAMAT KONTRAK KESELURUHAN | Inactive | Reserved for OS24000 Sewaan (Mesin Fotostat) |
| 10 | TEMPOH KONTRAK KESELURUHAN | Inactive | Reserved for OS24000 Sewaan (Mesin Fotostat) |
| 11 | NAMA | Inactive | — |
| 12 | JAWATAN | Inactive | — |
| 13 | GRED | Inactive | — |
| 14 | KATEGORI1 | Active | Jenis Bekalan: four approved values |
| 15 | KATEGORI2 | Inactive | — |
| 16 | KATEGORI3 | Inactive | — |
| 17 | UNIT | Active | Automatically populated from matching reference |
| 18 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 19 | BIL. UNIT | Active | User-entered quantity |
| 20 | LOKASI URUSAN RASMI | Inactive | — |
| 21 | KADAR | Active | Automatically populated from reference; currently RM15.00 per selected unit |
| 22 | JARAK | Inactive | — |
| 23 | KEKERAPAN | Inactive | — |
| 24 | JAM | Inactive | — |
| 25 | BILANGAN HARI | Inactive | — |
| 26 | BILANGAN BULAN | Inactive | — |
| 27 | ANGGARAN JUMLAH SETAHUN | Active | Automatically calculated |
| 28 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan Aktiviti Kewangan |
| 29 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti pathway |
| 30 | KATEGORI PESERTA | Deferred | Program/ Aktiviti pathway |

**Reconciliation:** 9 Active + 19 Inactive + 2 Deferred = 30.

### Approved financial calculation

For all four Jenis Bekalan:

**ANGGARAN JUMLAH SETAHUN (27) = BIL. UNIT (19) × KADAR (21).**

UNIT (17) and KADAR (21) are populated from the selected approved reference row; KADAR remains mandatory and is not a manually chosen alternative rate. BIL. UNIT is the user-supplied quantity. Do not introduce a month, frequency, duration or distance multiplier. The supplied compound unit strings remain indivisible reference labels.

**Status:** Owner-approved specification. This documentation update does not change Laravel, PostgreSQL, the canonical template implementation, or any master/reference CSV.
