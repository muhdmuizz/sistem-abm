# OS24000 — Sewaan (Umum): category and Kadar reference

**Owner-confirmed reference:** 12 November 2026. **Owner-approved column applicability and calculation:** 14 November 2026. **Scope:** Approved reference/specification; source-editor implementation is recorded in the Batch 037 checkpoint below.

## Identity

This is the reference for **OS24000 — Sewaan (Umum)**. **Sewaan (Mesin Fotostat)** has a [separate approved reference](OS24000-sewaan-mesin-fotostat-reference.md); this document does not merge its categories or rates.

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

**Historical specification checkpoint (14 November 2026):** Approved specification committed; that documentation commit did not modify Laravel code, database, master reference CSV or Excel export. Later implementation is recorded below.

## Batch 037 checkpoint — 16 November 2026

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) merged at `d95b86029a72a77da72e7e0932b6dadeb3f4be3f`. OS24000 Sewaan Umum source editor implements five existing mappings and all 64 approved paths, three-level dependent categories with cascading resets, blank Tanah leaves, distinct broadcasting labels and Berkontrak-only Kitaran-year dates/inclusive day duration. Exact draft preview = Bil. Unit × manual Kadar × manual Bilangan Bulan; dates never set months. No migration/seed/reset, reference/rate/catalogue/mapping/permission/export-schema changes. Photocopier rental remains separate and unimplemented.

Checkout access recovered without changing workflow/credentials/permissions after browser inspection confirmed existing settings were correct; precise transient upstream cause unconfirmed. [CI 43799773291 attempt 2](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`: **510 tests / 5,119 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Later commits changed documentation only. Reviewed 14 rental and three existing-editor captures from 382 PNGs / 22 rental captures; [Batch 037](../development/batch-037-sewaan-umum-spreadsheet.md) records coverage, earlier failures and review limits.

Local sync/financial acceptance remains pending; prior unconfirmed local statuses are unchanged. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement; usability defects fixed as encountered. No routine server restart required.
