# OS15000 — Faedah-faedah Kewangan Yang Lain: Consolidated Reference

**Recorded:** 12 November 2026  
**Status:** Consolidated from user-provided references and decisions. Reference documentation only, not implementation.

## Machine-readable reference data

This Markdown document preserves decisions and exceptions. The associated **CSV references** preserve structured records for future reference-data import/use:

- [OS15000-kemudahan-gred-kadar.csv](OS15000-kemudahan-gred-kadar.csv) — 125 grade/rate source rows, including the deliberately blank Bayaran Balik Yuran Kadar.
- [OS15000-faedah-faedah-kewangan-yang-lain-kategori.csv](OS15000-faedah-faedah-kewangan-yang-lain-kategori.csv) — 17 category/rate-mode records (10 legacy Kemudahan + 2 Tambang Ziarah Wilayah selections + BIPs + 4 HPA qualifications).

**Rate modes:** `GRADE_RATE_LOOKUP` = consult the 125-row reference; `USER_INPUT_DESCRIPTION_AND_KADAR` = require both fields; `USER_INPUT_KADAR` = user-entered rate; `KGT_REFERENCE` = derive from Jawatan/Gred KGT; `FIXED_RATE` = use the prescribed RM rate.

The CSV `Kategori1` assignment for each legacy Kemudahan is a **machine-readable representation of its source Jenis Kemudahan name**, not a new policy or extra category level. Source rows remain preserved separately. Empty Kategori2/Kategori3 fields mean no subordinate values supplied; they are not synthetic defaults. This documentation does not create or update PostgreSQL tables.

## 1. Anggaran (OS) identity and scope

**Anggaran (OS) = Kod Objek + canonical Butiran Perbelanjaan** (Reference 04). Therefore:

- **OS15000 — Faedah-faedah Kewangan Yang Lain** is the subject of *this* reference.
- **OS15000 — Bayaran Perubatan** is a **different Anggaran (OS)** despite sharing Kod Objek `15000`. It has its own source/reference file `OS15000-bayaran-perubatan-reference.md` and is **not a category or child of Faedah-faedah Kewangan Yang Lain**.
- **Tambang Ziarah Wilayah** is within **Faedah-faedah Kewangan Yang Lain**, **not** another separate Anggaran (OS).

## 2. Flexible category hierarchy

The user requires a hierarchy of `Kategori`, `Kategori1`, `Kategori2`, `Kategori3`, and further category levels **if needed**. This records the approved classification values below, without requiring every entry to have every level or setting unprovided values.

### Tambang Ziarah Wilayah

| Kategori1 | Kategori2 | Kategori3 |
| --- | --- | --- |
| Tambang Ziarah Wilayah | Wilayah Asal | Sabah |
| Tambang Ziarah Wilayah | Wilayah Asal | Sarawak |

**Kadar:** **User-entered for both Sabah and Sarawak.** No predefined rate lookup or fixed prescribed rate is used for Tambang Ziarah Wilayah.

### Bayaran Insentif Berasaskan Prestasi (BIPs)

**Named reference:** Bayaran Insentif Berasaskan Prestasi (BIPs).

**Approved rate rule:** `Kadar BIPs = KGT` for the applicable **Jawatan + full SSPA Gred**, retrieved from the existing **Reference 07 — Jawatan, Gred, Gaji dan KGT**.

- No independent BIPs rate table is needed.
- Fixed-RM KGT uses its existing reference values.
- If KGT is percentage-based (e.g. special VU7 rule), retain the calculation treatment documented in Reference 07; **the precise salary basis/rounding and other unresolved percentage-calculation details remain unresolved**. This file does not settle them.
- BIPs is intended to be classified within this OS; its deeper category levels, if any, have not been supplied.

### Hadiah Pengiktirafan Akademik (HPA)

**Kategori1:** Hadiah Pengiktirafan Akademik (HPA).

| Kategori2 | Kadar (RM) |
| --- | ---: |
| Ijazah Kedoktoran (PhD) | 1,250.00 |
| Ijazah Sarjana (Master) | 1,000.00 |
| Ijazah Sarjana Muda | 750.00 |
| Diploma/ Setaraf | 500.00 |

Rates above are predefined by the user, **not** manually entered. No extra Kategori3 was supplied for HPA.

## 3. Earlier committed Jenis Kemudahan reference — retained intact

The original **125-record** grade/rate source table is preserved unchanged in:

- [OS15000-kemudahan-gred-kadar-source.md](OS15000-kemudahan-gred-kadar-source.md).
- [OS15000-jawatan-gred-and-unspecified-kadar-rules.md](OS15000-jawatan-gred-and-unspecified-kadar-rules.md).

These previously agreed rules continue to apply:

- Where personnel attributes are required, use the shared **Jawatan + full SSPA Gred** input, and let the system interpret the grade as required by the corresponding source rate table.
- Do not assume equivalence between special legacy grade `C` and any numerical grade.
- For **Bayaran Balik Yuran** and any other **Kemudahan with no prescribed Kadar**, **Description and user-entered Kadar are both mandatory**. A blank source rate is not RM0.
- Preserve the recorded rates; no other eligibility, rate caps, or financial formulae are established by this document.

## 4. Current boundary and outstanding information

- The original 125 source rows are not rewritten, renamed or reclassified by this consolidation. Mapping their flat `Jenis Kemudahan` rows to category fields can be defined without changing the source evidence.
- Tambang Ziarah Wilayah's Kadar is entered by the user for the selected Wilayah Asal (Sabah or Sarawak).
- BIPs' rate derives from KGT; no independent rate schedule is introduced.
- No additional HPA qualification levels are inferred.
- No new operational restrictions, eligibility tests, calculations, database changes, Laravel code changes, or changes to existing Architecture Decisions are authorized by this reference commit.


## Owner calculation approval — 18 November 2026

One named recipient and one benefit per row; **Anggaran Setahun = applicable Kadar × 1** for prescribed rates, manual amounts, HPA and fixed-RM KGT BIPs. Existing reference modes/values remain unchanged. Percentage-based KGT (intentional VU7 blank) stays unavailable pending salary basis/rounding; no manual substitute, guessed median-salary basis or legacy C equivalence. See [approved applicability/calculation checkpoint](OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md) and [Batch 046](../development/batch-046-benefits-spreadsheet.md). Programme inheritance and official financial integration remain separate.


## Batch 046 implementation checkpoint — 18 November 2026

OS15000 Faedah spreadsheet merged in [PR #101](https://github.com/muhdmuizz/sistem-abm/pull/101) at `0a09e8fab62ee6962d395b5175d2a67bf6d40f5a`: one named recipient/benefit, exact Kadar × 1, prescribed grade rates, manual Yuran/territory rates, HPA and exact job-grade fixed KGT. Percentage KGT remains unavailable pending salary basis/rounding. Tested `6bc52911cf169bd09a5da6ac1aad7fa321b7ea89`, run 44224595873/job 134534547451: 570 tests / 6,665 assertions plus build/exact-money/Chromium on PHP 8.4.27/PostgreSQL 18.7 isolated database. Five captures individually reviewed; remaining captures and local financial/device acceptance unverified. No new migration/seed or CSV changes; local sync pending. AD-008 stays Draft, Steps 02/03 in progress; official financial integration and programme inheritance remain pending. See Batch 046 development record for failed initial test-helper attempt and presentation limits.
