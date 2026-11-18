# OS15000 — Faedah-faedah Kewangan Yang Lain: Approved 27-Column Applicability Checkpoint

**Recorded:** 14 November 2026  
**Status:** Approved *direct-entry column-applicability and input-behaviour decisions* for all 13 Jenis Kemudahan. **Kadar × 1 approved 18 November 2026**; percentage-based KGT remains unavailable pending its remaining rules. Implementation verification is separate.  
**Scope:** The Anggaran (OS) identified by Kod Objek `15000` + Butiran Perbelanjaan **Faedah-faedah Kewangan Yang Lain**. The separate **OS15000 — Bayaran Perubatan** is explicitly excluded.

## 1. Evidence, authority and non-goals

This checkpoint records decisions expressly made in the OS × 27-column configuration conversation, retaining the earlier approved references without rewriting their values:

- [Consolidated OS15000 reference](OS15000-faedah-faedah-kewangan-yang-lain-consolidated.md)
- [OS15000 categories and rate modes](OS15000-faedah-faedah-kewangan-yang-lain-kategori.csv)
- [Original Kemudahan/Gred/Kadar reference](OS15000-kemudahan-gred-kadar.csv)
- [Jawatan/Gred and unspecified Kadar rules](OS15000-jawatan-gred-and-unspecified-kadar-rules.md)
- [AD-008 framework](../architecture/AD-008-os-catalogue-calculation-framework.md)

**Important distinction:** *Applicability* (active/inactive/deferred) is independent of *value behaviour* (auto, manual, reference drop-down, calculated or OS-specific). "Manual" here can mean selecting an existing controlled reference rather than free text. A global input-behaviour rule does not make a column active for all OS.

Program/ Aktiviti is intentionally excluded as an input pathway during this **direct-entry** matrix exercise. It remains an approved alternative doorway into the same canonical Anggaran (OS) / Butiran Anggaran, to be mapped separately; this checkpoint does **not** delete or redesign AD-007.

## 2. Approved global behaviours across the canonical 27 columns

| No. | Canonical Excel column | Global behaviour / rule |
| ---: | --- | --- |
| 01 | OBJEK SEBAGAI | Auto — selected Anggaran (OS) |
| 02 | JPNT/ PPD | Auto — Bidaan's authorised PTJ |
| 03 | SEKTOR/ SEKOLAH | Manual controlled selection of **Tempat Bertugas**, restricted to locations under the authorised PTJ; not inferred from PTJ alone |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | OS-specific |
| 05 | TARIKH MULA | OS-specific |
| 06 | TARIKH TAMAT | OS-specific |
| 07 | TEMPOH | Calculated when applicable |
| 08 | NAMA | Manual when applicable |
| 09 | JAWATAN | Manual shared-reference drop-down |
| 10 | GRED | Manual shared-reference drop-down; retain full SSPA grade |
| 11 | KATEGORI1 | Existing OS-specific reference drop-down |
| 12 | KATEGORI2 | Existing OS-specific dependent reference drop-down |
| 13 | KATEGORI3 | Existing OS-specific dependent reference drop-down |
| 14 | UNIT | OS-specific |
| 15 | GRED PENCERAMAH/ FASILITATOR | Manual shared-reference drop-down when applicable |
| 16 | BIL. UNIT | OS-specific |
| 17 | LOKASI URUSAN RASMI | OS-specific |
| 18 | KADAR | OS-specific; consult established rate mode |
| 19 | JARAK | OS-specific |
| 20 | KEKERAPAN | OS-specific |
| 21 | JAM | OS-specific |
| 22 | BILANGAN HARI | OS-specific |
| 23 | BILANGAN BULAN | OS-specific |
| 24 | ANGGARAN JUMLAH SETAHUN | Calculated when applicable; exact OS-specific amount formula still subject to verification |
| 25 | KATEGORI SUMBER PERUNTUKAN | Auto — derived from the selected Bidaan's **Aktiviti Kewangan**, using approved category mapping |
| 26 | KATEGORI PROGRAM | Reserved/deferred for Program/ Aktiviti entry point |
| 27 | KATEGORI PESERTA | Reserved/deferred for Program/ Aktiviti entry point |

These are **behaviours**, not new invented Excel header names. The canonical 27-column export layout is retained.

## 3. Approved OS15000 applicability — all 13 Kemudahan

For all 13 Kemudahan, **columns 01, 02, 03, 08, 09, 10, 11, 18, 24 and 25 are ACTIVE**.

For all 13, **columns 05, 06, 07, 14, 15, 16, 17, 19, 20, 21, 22 and 23 are INACTIVE**, and **columns 26–27 are DEFERRED** (Program/ Aktiviti).

The remaining columns vary only as follows:

- **04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN:** ACTIVE for **Bayaran Balik Yuran** only, meaning **Tujuan Yuran** (required description input); INACTIVE for the other 12.
- **12 — KATEGORI2:** ACTIVE for **Tambang Ziarah Wilayah** and **Hadiah Pengiktirafan Akademik (HPA)**; INACTIVE for the other 11.
- **13 — KATEGORI3:** ACTIVE for **Tambang Ziarah Wilayah** only; INACTIVE for the other 12.

This compact rule set specifies all 27 statuses for all 13 Kemudahan without requiring repeated manual entry. There are **zero unset column-applicability decisions**.

### Kemudahan by approved variant

| No. | Jenis Kemudahan | Extra active columns beyond universal ten | KADAR method |
| ---: | --- | --- | --- |
| 1 | Bantuan Baju Kot | None | Existing Kemudahan/Gred rate lookup |
| 2 | Bantuan Pakaian Panas Tempat Kerja | None | Existing Kemudahan/Gred rate lookup |
| 3 | Bayaran Pakaian Istiadat | None | Existing Kemudahan/Gred rate lookup |
| 4 | Bantuan Bayaran Pakaian Black Tie | None | Existing Kemudahan/Gred rate lookup |
| 5 | Bayaran Pakaian Menghadiri Upacara Rasmi (Pegawai) | None | Existing Kemudahan/Gred rate lookup |
| 6 | Bayaran Pakaian Menghadiri Upacara Rasmi (Pasangan) | None | Existing Kemudahan/Gred rate lookup |
| 7 | Bayaran Pembaharuan Lesen Memandu | None | Existing Kemudahan/Gred rate lookup |
| 8 | Kemudahan Pembelian Alat Komunikasi Mudah Alih | None | Existing Kemudahan/Gred rate lookup |
| 9 | Elaun Pakaian Panas | None | Existing Kemudahan/Gred rate lookup |
| 10 | Bayaran Balik Yuran | 04 | **Manual Description / Tujuan Yuran** and **manual Kadar**, both required |
| 11 | Tambang Ziarah Wilayah | 12, 13 | Manual Kadar; established Sabah/Sarawak dependent categories |
| 12 | Bayaran Insentif Berasaskan Prestasi (BIPs) | None | Auto KGT lookup by Jawatan and full SSPA Gred (Reference 07) |
| 13 | Hadiah Pengiktirafan Akademik (HPA) | 12 | Auto fixed rate by KATEGORI2 qualification |

**KATEGORI1 is ACTIVE for all thirteen**, referring to the relevant Jenis Kemudahan. Under Tambang Ziarah Wilayah, **KATEGORI2 = Wilayah Asal**, **KATEGORI3 = Sabah/Sarawak** as existing reference records prescribe. Under HPA, **KATEGORI2** is the chosen qualification: PhD RM1,250; Master RM1,000; Sarjana Muda RM750; Diploma/ Setaraf RM500. No HPA KATEGORI3. The original categorisation and exact reference rate values are not replaced by this summary.

### Common and variant-specific data meanings

- **08 NAMA:** named officer/recipient for the individual Butiran Anggaran.
- **09 JAWATAN / 10 GRED:** controlled personnel reference and full SSPA grade; do not invent equivalence for special legacy grade `C`.
- **18 KADAR:** ACTIVE for all, but its population mode depends on the Kemudahan listed above; **do not show all Kadar as manual**.
- **24 ANGGARAN JUMLAH SETAHUN:** computed as Kadar × 1 per named recipient/benefit (owner approval, 18 November 2026), not directly overwritten.
- **Bayaran Balik Yuran:** Column 04 is explicitly **Tujuan Yuran**, the required descriptive input. Preserve the separately confirmed mandatory free-text description and manual Kadar rule.
- **BIPs:** KGT percentage cases and precise calculation/rounding, where relevant, remain unresolved; do not invent them.

## 4. Readiness and outstanding matters

**Completed:** All 13 Kemudahan mapped against all 27 canonical Perbelanjaan columns for **direct entry**, with the relevant Kategori hierarchy and rate-source *modes* preserved.

**Not completed / not authorised by this checkpoint:**

1. The overall amount rule is now approved below. Percentage-based KGT salary basis/rounding remain unresolved and unavailable; no additional multiplier or rounding policy is inferred.
2. Program/ Aktiviti alternative-entry inheritance mapping for these columns, including deferred 26–27.
3. OS15000 — **Bayaran Perubatan** (separate Anggaran with the same Kod Objek).
4. Laravel implementation, database changes, import/export code, and production readiness.

**This is a decisions-only archival checkpoint.** Do not interpret `27/27` applicability coverage as validation of calculations or implementation completeness.


## Owner-approved calculation — 18 November 2026

Owner explicitly approved: **one row = one named recipient and one benefit; Anggaran Setahun = Kadar × 1**. This covers the existing prescribed grade rates, valid manually entered amounts, HPA and fixed-RM KGT for BIPs. Quantity, frequency, months, dates and Unit remain Inactive; do not invent multipliers. Kadar source/mode remains category-specific. Bayaran Balik Yuran requires Tujuan Yuran plus manual Kadar; Tambang Ziarah Wilayah retains its two approved territory paths and manual rate; HPA remains qualification-based and fixed. BIPs uses the **exact Jawatan + full SSPA Gred** fixed-RM KGT, not an independent table or a guessed salary basis.

**Percentage-based KGT remains unavailable** pending the applicable salary basis/rounding rules. The intentional blank fixed-KGT cell for Pengarah Pendidikan Negeri/VU7, documented in Reference 07 as a percentage case, is not zero and permits no manual substitute. Legacy grade C remains unmapped, with no inferred VU7/numeric equivalence. Missing reference grade matches do not authorise manual overrides. Preserve all 125 grade/rate rows, 17 category paths, 131 personnel rows and current rates.

Partial source drafts may be retained, but withhold the draft amount until the named recipient, valid shared Jawatan/Gred and applicable rate/path are available; refunds additionally need Tujuan Yuran. Pending KGT remains without an amount. This is not official output/completeness/submission implementation. Programme inheritance and all unrelated OS remain unchanged. Historical 27-column positions map to current 30-column positions under AD-008. [Batch 046](../development/batch-046-benefits-spreadsheet.md) records implementation and verification.
