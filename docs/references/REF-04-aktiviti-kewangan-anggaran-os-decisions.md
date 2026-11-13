# Reference 04 — Aktiviti Kewangan and Anggaran (OS): Confirmed Decisions

**Confirmed:** 12 November 2026  
**Status:** Approved reference-modelling decisions. Reference persistence was subsequently implemented in Batch 004; this record does not approve financial calculations or workflows.  
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

## 6. Scope and status — reconciled 13 November 2026

- **Historical preliminary counts:** the conversation initially tallied 106 raw mapping rows / 103 after exact-duplicate removal and estimated 31 distinct OS. These were preliminary, not the final current reference inventory.
- **Current committed source:** [anggaran-os-catalogue.csv](anggaran-os-catalogue.csv) contains 33 identities (31 eligible references and two exclusions); [kod-aktiviti-anggaran-os-mapping.csv](kod-aktiviti-anggaran-os-mapping.csv) contains 105 mappings. Preserve the approved identity/wording/duplicate/exclusion rules above.
- Batch 004 implements and tests catalogue/mapping persistence. The owner subsequently assigned TASKA Kod Aktiviti `010102` under PPN; [the TASKA reference](OS42000-subsidi-yuran-taska-reference.md) records that correction. Current Kod Aktiviti inventory is 38.
- [Reference 05](REF-05-ptj-aktiviti-kewangan-anggaran-os-permissions.md) now records the confirmed applicability/permission framework. Do not infer individual PTJ OS selections from this catalogue.
- [Development progress and verification evidence](../development/README.md) record the actual implementation checkpoints. Import completion does not finalise Draft AD-008 or implement OS calculations, transaction workflows or permission administration.
- This reconciliation changes status reporting only; it does not amend the approved modelling rules or AD decisions.
