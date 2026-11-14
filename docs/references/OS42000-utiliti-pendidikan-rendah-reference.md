# OS42000 — Utiliti (Pendidikan Rendah)

**Owner-confirmed:** 12 November 2026. **Scope:** reference reuse, not application implementation.

- **Aktiviti Kewangan:** Pendidikan Rendah.
- **Kod Aktiviti:** `030228` (as recorded in the existing activity-to-OS mapping).
- **Kod Objek:** `42000`.
- **Existing mapped Butiran Perbelanjaan:** Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK).
- **Template:** reuse the [OS23000 — Perhubungan dan Utiliti reference](OS23000-perhubungan-utiliti-reference.md), restricted to **Pukal** only.
- **Kategori1:** `Pukal` (fixed).
- **Kategori2:** `Air`, `Elektrik`, `Telefon/ Faks`, `Pembetungan`.
- **Kadar:** mandatory **user-entered** for each applicable selection. No prescribed rates.

Read the four Pukal entries from [OS23000 reference CSV](OS23000-perhubungan-utiliti-kategori.csv). Do not create a duplicate item dataset. **Bukan Pukal** does not apply to this OS42000 reference.

The existing Pendidikan Menengah OS42000 mapping is not changed by this decision. No changes are made to application code, database or existing financial formulas.


## Owner-approved 30-column applicability and calculation — 15 November 2026

This later approval adopts the already approved **OS23000 Perhubungan dan Utiliti — Pukal** quantity/rate calculation and column applicability for **OS42000 Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK), Pendidikan Rendah**, as a separate OS identity with Kod Aktiviti `030228`. Reuse the existing four Pukal rows from the OS23000 reference CSV; do not duplicate the source dataset or include Bukan Pukal.

| Status | Canonical 30-column numbers | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 14, 15, 21, 26, 27, 28 | 9 |
| Inactive | 04, 05, 06, 07, 08, 09, 10, 11, 12, 13, 16, 17, 18, 19, 20, 22, 23, 24, 25 | 19 |
| Deferred | 29, 30 | 2 |

**9 + 19 + 2 = 30.** The original canonical headers do not change.

- **14 KATEGORI1**: fixed **Pukal**.
- **15 KATEGORI2**: one of **Air**, **Elektrik**, **Telefon/ Faks**, **Pembetungan** only, from the existing shared OS23000 Pukal reference.
- **21 KADAR**: mandatory manual input representing the estimated **monthly** utility expenditure; no prescribed or automatic rate.
- **26 BILANGAN BULAN**: **fixed at 12**, not user-adjustable.
- **27 ANGGARAN JUMLAH SETAHUN = KADAR × BILANGAN BULAN = KADAR × 12**, automatically calculated.
- **01–03 and 28** retain their global organisational/funding reference rules; **29–30** remain Deferred to Program/ Aktiviti.
- No BIL. UNIT, KEKERAPAN or additional multiplier is used, consistent with OS23000 Pukal.

**Boundaries:** This decision does not combine OS42000 with OS23000, alter the distinct Kod Aktiviti or expand Pukal category coverage. It does not change the existing **Pendidikan Menengah OS42000 mapping**, any reference CSV, the master catalogue, Laravel, PostgreSQL or the Excel exporter. Documentation only; AD-008 remains Draft.


## Shared SBK template for Pendidikan Menengah — owner-approved 15 November 2026

The owner additionally approved reuse of this **same SBK Pukal Anggaran template** for **Pendidikan Menengah, Kod Aktiviti `030310`**, while **Pendidikan Rendah remains Kod Aktiviti `030228`**. The two financial catalogue identities and activity mappings remain distinct; no original CSV mapping row is replaced or merged.

Both approved SBK contexts use the **same 9 Active / 19 Inactive / 2 Deferred** canonical 30-column mapping documented above:
- **01, 02, 03, 14, 15, 21, 26, 27, 28** Active;
- **04–13, 16–20, 22–25** Inactive;
- **29–30** Deferred.
- **KATEGORI1 (14)** is fixed **Pukal**.
- **KATEGORI2 (15)** comprises exactly the four shared OS23000 Pukal choices **Air**, **Elektrik**, **Telefon/ Faks**, **Pembetungan**.
- **KADAR (21)** is mandatory manually entered estimated monthly expenditure.
- **BILANGAN BULAN (26)** is fixed at **12**.
- **ANGGARAN JUMLAH SETAHUN (27) = KADAR × 12**, automatically calculated. No BIL. UNIT or KEKERAPAN multiplier.

This approval extends the documented template reuse to Pendidikan Menengah; it **supersedes only** the earlier statement that its template reuse had not yet been approved. Preserve the shared reference rows and the original source spelling in historical catalogue records. **Asrama Malaysia (AMal), Kod Aktiviti `030318`, remains Excluded — reference only** and is not an eligible Anggaran in this design.

No Laravel, PostgreSQL, Excel exporter, mapping CSV, reference data or master catalogue changes are authorised by this documentation decision; AD-008 remains Draft.

## Implementation status — Batch 024, 15 November 2026

The earlier no-application-change statements describe the documentation approvals above. The owner's subsequent instruction to continue development authorised the source-draft implementation in [Batch 024](../development/batch-024-sbk-spreadsheet-definition.md). [PR #56](https://github.com/muhdmuizz/sistem-abm/pull/56), merged `68a8f46fb839675f2d83ad7914e9b4cb2f163a19`, enables the shared SBK template for the two existing approved activity/code mappings. It reads the original shared CSV; no reference/catalogue/mapping or permission data is changed. Fixed Pukal/12 and manual monthly rate produce exact row previews; official output/totals/completeness remain pending. CI 43635684953 passed 431 tests / 3354 assertions plus build/Chromium. Local acceptance pending. This status update changes no approved applicability or financial rule.
