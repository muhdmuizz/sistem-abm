# OS22000 — Consolidated Kategori1 branching (owner-confirmed decision)

**Confirmed:** 12 November 2026. **Scope:** Reference and input decisions only; detailed #P7OS specification/implementation is not resumed by this record.

## Current structural decision

**OS22000 is to be treated as ONE Anggaran (OS) with two paths distinguished at Kategori1:**

| Kategori1 | Subsequent classification / inputs |
| --- | --- |
| **Pertukaran/ Penempatan** | Preserve the previously supplied Pertukaran/ Penempatan category combinations and reference/rate material under this path. The detailed reconciliation of legacy subsections is not newly decided here. |
| **Pengangkutan Barang** | **No Kategori2, Kategori3, Kategori4 or further categories.** User must enter **Program/ Aktiviti/ Tujuan** and **Kadar**; both are required inputs. |

**The mandatory Program/ Aktiviti/ Tujuan is an input field, not an extra category.** Its allowed values, source, inheritance and free-text behaviour are **not yet specified**; do not import OS14000 option rules without a separate decision.

For **Pengangkutan Barang**, **Kadar is user-entered**, not calculated from the old grade-based reference table. No grade-based lookup or category options are to be assumed for this branch.

## Preservation of earlier data and decisions

- [OS22000 — Tuntutan Pertukaran/ Penempatan category reference](OS22000-pertukaran-penempatan-kategori.md) and its eight-row CSV are preserved verbatim as **legacy reference material for the Pertukaran/ Penempatan branch**. The source's original Kategori1–Kategori4 columns are retained as supplied; they do not override the newly confirmed top-level Kategori1.
- [OS22000 — earlier Pengangkutan Barang-barang source reference](OS22000-pengangkutan-barang-reference.md), the 126-row rate CSV and its four-row Kadar mode CSV are preserved as **historical reference material**, based on the earlier, now-superseded assumption of a separate OS22000 Pengangkutan Barang-barang identity. **Do not automatically assign their old category and grade lookup structure to the new Pengangkutan Barang branch.** The owner explicitly said the previous material belongs under Pertukaran/ Penempatan; exact internal reclassification is left unmodified pending clarification.
- **Jawatan, Gred, Gaji & KGT** remain shared Reference 07 attributes applicable across OS where relevant; Jawatan and Gred are **never category levels**.

## Master-catalogue and Kod Aktiviti reconciliation boundary

This decision **supersedes** the earlier assumption that `22000 — Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula` and `22000 — Pengangkutan Barang-barang` are separately selected OS identities. The canonical display label for the unified OS has **not** been newly confirmed; do not coin one.

The existing `anggaran-os-catalogue.csv` and `kod-aktiviti-anggaran-os-mapping.csv` preserve the **legacy source records** for now. In particular, the supplied activity mapping has differing Kod Aktiviti in some source rows (including `030301` vs `030302` for Pendidikan Menengah and `030313` vs `030314` for Pengurusan Asrama). The owner's statement that the new paths belong within the same activity context does not provide a verified replacement for every legacy mapping. **Do not fabricate or overwrite Kod Aktiviti** or erase original source provenance. Reconcile this separately against authoritative mappings before changing the master CSVs.

## Scope

This is a user-approved structural decision checkpoint, **not** the implementation of a new OS wizard, transaction model, calculation, or export. Existing legacy reference files, the master OS catalogue, Kod Aktiviti mapping, Reference 07, ADs, Laravel and PostgreSQL are unchanged in this commit.
