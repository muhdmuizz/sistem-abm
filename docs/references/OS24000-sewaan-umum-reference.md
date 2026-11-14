# OS24000 — Sewaan (Umum): category and Kadar reference

**Owner-confirmed reference:** 12 November 2026. **Owner-approved column applicability and calculation:** 14 November 2026. **Scope:** Documentation only; no application/DB changes.

## Identity

This is the reference for **OS24000 — Sewaan (Umum)**. **Sewaan (Mesin Fotostat)** has a separate reference to be supplied; this document does not assume its categories or rates.

## Approved category mapping

| Level | Field |
| --- | --- |
| **Kategori1** | **Kategori Sewaan** (`Berkontrak`, `Tidak Berkontrak`) |
| **Kategori2** | **Kategori Peralatan** |
| **Kategori3** | **Jenis Peralatan** |
| **Kadar** | **Mandatory user-entered** |

The [machine-readable category CSV](OS24000-sewaan-umum-kategori.csv) contains **64 combinations**: the same 32 equipment selections for each rental arrangement. `Kategori3` for **Tanah** is left blank in both source combinations; **do not invent a Jenis Peralatan** or force a fabricated category value.

Normalise only repeated interior whitespace from the supplied table (e.g. `Tidak   Berkontrak`, `Alat   Kelengkapan`). The two supplied category-wording variants for broadcasting equipment are retained as distinct source labels when applicable:

- **Berkontrak:** Kategori2 `Sewa Alat Kelengkapan Penyiaran`; Kategori3 `Alat Kelengkapan Penyiaran`.
- **Tidak Berkontrak:** Kategori2 `Alat Kelengkapan Penyiaran`; Kategori3 `Alat Kelengkapan Penyiaran`.

## Mandatory Kadar

**Kadar for Sewaan (Umum) is provided by the user** for every applicable record, and must be present for the item to be complete. No fixed rate or Gred-based lookup is supplied in the original category source. The subsequently approved calculation is recorded below.

The owner's **universal decision** is recorded separately in [the shared Kadar rule](shared-mandatory-kadar-rule.md): every applicable Kadar is mandatory, whether derived automatically from approved references/calculations or entered by the user. This does **not** introduce new rate rules for other OS or override their documented input modes.

## Boundaries

This file documents the 64 supplied category paths, with no change to the source's actual options. Sewaan (Umum) and Sewaan (Mesin Fotostat) are handled as **separate Anggaran entries because their Kod Aktiviti differ**, notwithstanding their shared Kod Objek; do not merge them or their category references. Master OS catalogue, Kod Aktiviti mapping, ADs, Laravel and PostgreSQL are unchanged.

## Approved 27-column applicability — 14 November 2026

Applies to OS24000 **Sewaan (Umum)** only, for the 64 approved combinations. The 27 canonical Excel headers and global rules remain unchanged.

| No. | Canonical column | Status | Behaviour |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Auto from selected Anggaran |
| 02 | JPNT/ PPD | Active | Auto from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Controlled Tempat Bertugas choice under PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Active | User input |
| 05 | TARIKH MULA | Conditional | Applicable only for KATEGORI1 = Berkontrak |
| 06 | TARIKH TAMAT | Conditional | Applicable only for KATEGORI1 = Berkontrak |
| 07 | TEMPOH | Conditional | Calculated from dates only when KATEGORI1 = Berkontrak |
| 08 | NAMA | Inactive | — |
| 09 | JAWATAN | Inactive | — |
| 10 | GRED | Inactive | — |
| 11 | KATEGORI1 | Active | Berkontrak / Tidak Berkontrak |
| 12 | KATEGORI2 | Active | Approved dependent Kategori Peralatan |
| 13 | KATEGORI3 | Active | Approved dependent Jenis Peralatan; preserve intentionally blank Tanah records |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Active | User input |
| 17 | LOKASI URUSAN RASMI | Inactive | — |
| 18 | KADAR | Active | Mandatory manual input |
| 19 | JARAK | Inactive | — |
| 20 | KEKERAPAN | Inactive | — |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Inactive | — |
| 23 | BILANGAN BULAN | Active | Manually entered |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Calculated automatically |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan Aktiviti Kewangan |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti pathway |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti pathway |

**Reconciliation: 12 Active + 10 Inactive + 3 Conditional + 2 Deferred = 27.**

### Approved conditional date treatment

- **Berkontrak:** columns 05 TARIKH MULA and 06 TARIKH TAMAT apply; column 07 TEMPOH is calculated from those dates.
- **Tidak Berkontrak:** columns 05–07 are inactive.
- No additional date-to-month conversion or formula is approved. **BILANGAN BULAN remains manually entered in both cases**, independent of the computed TEMPOH.

### Approved annual calculation

For both Berkontrak and Tidak Berkontrak:

**ANGGARAN JUMLAH SETAHUN (column 24) = BIL. UNIT (column 16) × KADAR (column 18) × BILANGAN BULAN (column 23).**

KADAR is mandatory manual input, and BILANGAN BULAN is manual rather than fixed at 12. No further multiplier, rate, rounding, or eligibility rule is introduced.

**Status:** Approved specification committed; no Laravel code, database, master reference CSV or Excel export was modified.
