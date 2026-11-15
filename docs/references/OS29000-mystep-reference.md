# OS29000 — MySTEP: reference consolidation

**Owner-confirmed:** 12 November 2026. **Scope:** Reference choices and rate selection only. No application/database implementation, rate duration assumption or source-to-source eligibility join approved.

## Kategori1

Original supplied 11 rows (five Guru Ganti; five Pembantu Pengurusan Murid; one Pengurusan) retained in [Kategori1 source CSV](OS29000-mystep-kategori1-source.csv).

Unique options for Kategori1:
1. Guru Ganti
2. Pembantu Pengurusan Murid
3. Pengurusan

## TUJUAN (existing 27-column Excel field)

The **Tujuan Lantikan** choices map to the existing **TUJUAN** output field, **not Kategori2 or Kategori3**. Original supplied 11 source rows, including repetitions, are retained in [Tujuan source CSV](OS29000-mystep-tujuan-source.csv).

Unique options, preserving the supplied wording after whitespace normalisation:
1. Cuti Bersalin
2. Cuti Haji
3. Cuti Sakit Berpanjangan
4. Cuti Separuh Gaji
5. Cuti Tanpa Gaji
6. Projek/ Program/ Ad Hoc/ Tugas Bermusim (Seasonal)

Both source lists have 11 entries. This supports retaining their source order for audit, **not** assuming a binding eligibility/availability rule based solely on their aligned order. The exact relationship between Kategori1 and Tujuan has not been separately approved.

## Taraf Akademik and prescribed Kadar

The **Taraf Akademik** is a distinct selection attribute, not Kategori2 or Kategori3. The owner chose **JUMLAH as the single Kadar** to be auto-populated from the selected Taraf Akademik. Do **not** show Upah Tetap, KWSP or PERKESO as separate components of Kadar. Retain these three components in the source for audit. The supplied JUMLAH is used directly; do not recalculate or override it.

| Taraf Akademik | Upah Tetap (RM) | KWSP (RM) | PERKESO (RM) | JUMLAH / Kadar (RM) |
| --- | ---: | ---: | ---: | ---: |
| Setaraf Ijazah Sarjana Muda | 2,300.00 | 299.00 | 39.35 | **2,638.35** |
| Setaraf Diploma/ Sijil Tinggi Pelajaran Malaysia | 2,100.00 | 273.00 | 35.85 | **2,408.85** |
| Setaraf Sijil Pelajaran Malaysia | 1,900.00 | 247.00 | 32.35 | **2,179.35** |
| Tiada Sijil Pelajaran Malaysia | 1,700.00 | 221.00 | 28.85 | **1,949.85** |

See [academic rate source CSV](OS29000-mystep-taraf-akademik-kadar-source.csv).

## Unified MySTEP template — owner-confirmed

The owner explicitly confirmed that the following catalogue labels **are the same MySTEP template**, not different templates or different reference sets:

- `MySTEP`
- `MySTEP - Guru Ganti`
- `MySTEP - Pembantu Pengurusan Murid (PPM) Ganti`

**Use one shared MySTEP template and the same Kategori1, TUJUAN, Taraf Akademik and JUMLAH-as-Kadar references across all three.** The relevant **Aktiviti Kewangan** will distinguish their application later. Do not implement three separate templates or duplicate the references, and do not invent a new variant-specific difference or rate.

The existing legacy catalogue labels and mappings are left intact for now; this decision governs template reuse and does not itself specify or revise any Aktiviti Kewangan mapping, eligibility rule, or calculation method.

## Boundaries and open questions

- Applicable Kadar is mandatory per [shared rule](shared-mandatory-kadar-rule.md).
- No basis (monthly/hourly/session), duration/quantity formula, Excel destination field for Taraf Akademik, applicable year, or eligibility condition has been established by these source tables.
- No changes to the master OS catalogue, Kod Aktiviti mapping, other ADs, Laravel, PostgreSQL or existing 27-column Excel contract are authorised by this commit.


## Owner-approved 30-column MySTEP Anggaran specification — 15 November 2026

The following later owner decisions **supersede earlier reference-only exclusions** for TARAF AKADEMIK and the earlier absence of an approved monthly KADAR/formula. Keep the original CSV reference records and all existing labels unchanged. The three catalogue identities **MySTEP**, **MySTEP - Guru Ganti**, and **MySTEP - Pembantu Pengurusan Murid (PPM) Ganti** use **one shared Anggaran template** and reference set, with relevant Aktiviti Kewangan context distinguishing their applicability; do not invent additional mappings.

### Canonical 30-column Perbelanjaan applicability

| Status | Canonical positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 04, 05, 06, 07, 14, 15, 17, 19, 21, 26, 27, 28 | 15 |
| Inactive | 08, 09, 10, 11, 12, 13, 16, 18, 20, 22, 23, 24, 25 | 13 |
| Deferred | 29, 30 | 2 |

**15 + 13 + 2 = 30.** Column 04 **NAMA PROGRAM/ AKTIVITI/ TUJUAN** carries **TUJUAN LANTIKAN**, selected from the existing six-item source; 05 TARIKH MULA, 06 TARIKH TAMAT and 07 calculated TEMPOH follow their established canonical meaning. 11 **NAMA**, 12 **JAWATAN** and 13 **GRED** are Inactive: budgeting uses numbers of personnel and does not require identified individual names or SSPA personnel-reference grades. Columns 08–10 are Inactive, as are 16 KATEGORI3 and 18 GRED PENCERAMAH/ FASILITATOR.

- 14 **KATEGORI1**: existing **Guru Ganti**, **Pembantu Pengurusan Murid**, **Pengurusan** options.
- 15 **KATEGORI2**: **TARAF AKADEMIK**, selected from the four existing academic qualifications. **This is an express owner-approved revision to the prior exclusion of TARAF AKADEMIK from KATEGORI2**. It preserves the source qualification values and makes them explicitly visible in canonical Excel output. KATEGORI3 remains Inactive.
- 17 **UNIT**: **Orang**.
- 19 **BIL. UNIT**: number of MySTEP personnel in the line.
- 21 **KADAR**: automatically retrieved as the existing **JUMLAH** value for the chosen TARAF AKADEMIK, interpreted as **RM per person per month**. The exact four existing totals are RM2,638.35 / RM2,408.85 / RM2,179.35 / RM1,949.85. Do not recompute JUMLAH from Upah Tetap, KWSP, or PERKESO or display these source parts as independently entered KADAR.
- 26 **BILANGAN BULAN**: budgeted months.
- 27 **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × BILANGAN BULAN**. The calculation uses the approved monthly rate basis.
- Columns 01–03 and 28 use the existing organisational/funding references; 29–30 remain Deferred to Program/ Aktiviti.

### TUJUAN LANTIKAN availability and source fidelity

All **six existing TUJUAN LANTIKAN** options are available with **each of the three KATEGORI1 options**, without filtering or inferring eligibility from the coincidental source-row ordering. The two original 11-row KATEGORI1 and Tujuan reference files remain intact and auditable. This is the approved application selection design, not a new claim about statutory eligibility.

### Change-control boundaries

Keep the 30 canonical Excel headers and existing shared AD-004 Butiran Anggaran model; no new TARAF AKADEMIK export column. The source KATEGORI1/TUJUAN/TARAF AKADEMIK CSVs, all listed JUMLAH amounts, master catalogue, Kod Aktiviti mappings, Laravel application, actual exporter and PostgreSQL are **unchanged**. This is a documentation-only approval within **Draft AD-008**.

## Batch 031 checkpoint — 15 November 2026

[PR #72](https://github.com/muhdmuizz/sistem-abm/pull/72) merged at `8ac02d13c0304a115783fa49190634e0b27812f9`. Owner explicitly confirmed there is **no real business data** in the current development system and authorised discarding obsolete OS23000 **Utiliti Pukal** test budgets. Batch 030's preservation panel/compatibility handling is removed. A targeted migration deletes obsolete outputs/Butiran/Anggaran and old OS permissions, repoints four existing Kod Aktiviti mappings to **Perhubungan dan Utiliti**, removes the obsolete reference and bumps affected Bidaan revisions. Current canonical budgets and unrelated records remain; no full reset or reference reseed. Catalogue: **32 identities / 30 eligible / 2 excluded**; mappings: **105 / 103 eligible**, including eight actual OS23000 source mappings. Historical 33/31 counts are superseded by this inventory.

The approved shared **OS29000 MySTEP** source grid is now implemented for all three existing variants/seven mappings, using the unchanged four JUMLAH monthly rates, independent categories/purposes, Kitaran-year dates and manual budgeted months. Exact preview = Bil. Unit × Kadar × Bilangan Bulan; date duration does not determine months. Title-case headers and accounting-style money retained.

Tested application head `005776cec1c85ebd3cdbabcb889b04925e31f228`; [CI 43679552938](https://github.com/muhdmuizz/sistem-abm/actions/runs/43679552938): **474 tests / 3925 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB, production build/formatter/Chromium passed. Twelve representative final-head desktop/mobile captures reviewed; [Batch 031](../development/batch-031-utility-cleanup-mystep.md) records limits. Local targeted migration/update remains pending. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft.
