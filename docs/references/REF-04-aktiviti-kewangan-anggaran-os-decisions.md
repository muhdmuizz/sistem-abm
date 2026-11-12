# Reference 04 — Aktiviti Kewangan and Anggaran (OS): Confirmed Decisions

**Confirmed:** 12 November 2026  
**Status:** Approved reference-modelling decisions; no application implementation or database import authorised by this record.  
**Source:** User-supplied table with columns `Aktiviti`, `Kod Aktiviti`, `Kod Objek`, `Butiran Perbelanjaan`, and explicit subsequent decisions.

## 1. Aktiviti Kewangan names

The five named Aktiviti Kewangan are:

1. Pengurusan Pentadbiran Negeri (**PPN**)
2. Pendidikan Awal
3. Pendidikan Rendah
4. Pendidikan Menengah
5. Pengurusan Asrama

A single named Aktiviti Kewangan can have multiple **Kod Aktiviti**. Preserve each source mapping of Aktiviti Kewangan, Kod Aktiviti, Kod Objek and Butiran Perbelanjaan; do not equate the five names with five unique Kod Aktiviti.

## 2. Identity of Anggaran (OS)

**Anggaran (OS) = Kod Objek + canonical Butiran Perbelanjaan.**

- Kod Objek **alone is not unique** for Anggaran (OS).
- Different expenditure purposes under the same Kod Objek remain distinct Anggaran (OS).
- Keep the original Kod Aktiviti-to-Anggaran (OS) associations from the supplied data. This is a **reference classification/mapping**, **not** a new financial transaction layer under the already-approved AD hierarchy.

## 3. Exact duplicate handling

Use only **unique source records** when consolidating reference mappings.

The user explicitly approved retaining one instance (not four) of the identical source tuple:

`PPN | 010801 | 28000 | Penyelenggaraan dan Pembaikan Kecil Yang Dibeli`

The three surplus identical rows are removed **from the consolidated mapping**, but original source content may be retained separately for audit. Do not merge records that have different Kod Aktiviti, Kod Objek or materially different Butiran Perbelanjaan.

## 4. Approved canonical wording

Where source descriptions refer to the **same** expenditure purpose, standardise only the wording as follows, while retaining original descriptions for audit and preserving their Kod Aktiviti associations:

| Kod Objek | Approved canonical Butiran Perbelanjaan |
| --- | --- |
| `11000` | Gaji dan Upahan |
| `12000` | Elaun/ Imbuhan Tetap |
| `24000` | Sewaan Mesin Fotostat dan Riso |
| `29000` | Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti |
| `42000` | Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK) |

These decisions resolve spelling/punctuation variants only for these five groups. They **do not** consolidate other genuinely distinct Anggaran (OS) entries sharing the same Kod Objek.

## 5. Reference-only implementation exclusions

Keep the following items in the **reference data**, but **exclude them from Anggaran (OS) implementation** in accordance with the existing AD-008 exclusion decision:

| Aktiviti Kewangan | Kod Aktiviti | Kod Objek | Butiran Perbelanjaan | Implementation |
| --- | --- | --- | --- | --- |
| Pendidikan Menengah | `030306` | `29000` | Pengajaran Bahasa Yang Diluluskan (POL) | Excluded |
| Pengurusan Asrama | `030318` | `42000` | Asrama Malaysia (AMal) | Excluded |

Their presence in an official or source reference list does **not** re-authorise any corresponding wizard, calculation or entry workflow.

## 6. Scope and status

- The conversation's preliminary tally was **106 raw mapping rows**, **103 after removing three exact duplicates**.
- A preliminary estimate of **31 distinct Anggaran (OS)** following five wording consolidations was discussed. **This is a derived count, not a replacement for a validated catalogue**; verify against the actual consolidated output before treating it as final.
- This file captures **approved decisions only**. The full source table, the derived reference CSVs, and the implementation-specific active/excluded flags have **not** been committed by this action.
- **Reference 05 — PTJ ↔ Aktiviti Kewangan applicability** is still to be established; do not infer PTJ mappings from this record.
- No modifications are made to AD-008 or other Architecture Decisions by creating this record.
