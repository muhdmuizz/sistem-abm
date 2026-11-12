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
