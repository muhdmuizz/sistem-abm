# OS42000 — Subsidi Yuran TASKA

**Owner-confirmed:** 12 November 2026. **Scope:** the entire currently supplied reference; no further reference options or calculations are established.

| Attribute | Approved value |
| --- | --- |
| Kod Objek | **42000** |
| Name | **Subsidi Yuran TASKA** |
| Aktiviti Kewangan | **Pengurusan Pentadbiran Negeri (PPN)** |
| Kod Aktiviti | **010102** (owner-confirmed 13 November 2026) |
| Kadar | **RM180.00** |
| Kadar mode | **Automatically populated from prescribed reference** |

**Machine-readable reference:** [OS42000-subsidi-yuran-taska-kadar.csv](OS42000-subsidi-yuran-taska-kadar.csv), exactly one rate record.

On **13 November 2026**, the owner assigned **Kod Aktiviti `010102`** to **Subsidi Yuran TASKA**, under **Pengurusan Pentadbiran Negeri (PPN)**. This supersedes the previous unassigned-code instruction. Preserve the leading zero and update the existing TASKA reference mapping rather than creating another OS identity.

The owner explicitly states that **RM180.00 is the only available reference for this variant at present**. No category hierarchy, payment unit, quantity/duration calculation, entitlement, eligibility, or rate component has been supplied. Do not invent them. The [mandatory-Kadar principle](shared-mandatory-kadar-rule.md) applies.

## Distinction from the other OS42000 reference

[OS42000 — Utiliti (Pendidikan Rendah)](OS42000-utiliti-pendidikan-rendah-reference.md) reuses OS23000 Pukal choices, with **mandatory user-entered Kadar** and Kod Aktiviti `030228`. Subsidi Yuran TASKA is under **PPN**, with **Kod Aktiviti `010102`**, and **fixed RM180.00 Kadar**.

This reference records the owner-confirmed code assignment. The reference seeder supports both fresh imports and upgrading the previous blank-code TASKA mapping while retaining its ID. No rate, calculation, entitlement or Superadmin UI change is introduced by this correction.


## Owner-approved 30-column applicability and monthly Anggaran — 15 November 2026

This later owner decision establishes the payment unit and calculation that were previously unconfirmed. Preserve **Kod Aktiviti 010102**, PPN ownership and the existing single **RM180.00 AUTO_REFERENCE** source row.

| Status | Canonical 30-column positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 11, 17, 19, 21, 26, 27, 28 | 10 |
| Inactive | 04, 05, 06, 07, 08, 09, 10, 12, 13, 14, 15, 16, 18, 20, 22, 23, 24, 25 | 18 |
| Deferred | 29, 30 | 2 |

**10 + 18 + 2 = 30.** Columns 01–03 and 28 retain globally approved organisational/funding reference rules.

| Column | Canonical header | Approved OS42000 TASKA behaviour |
| ---: | --- | --- |
| 11 | NAMA | **Nama Ibu/ Bapa**, required; this is the **parent's name, not the child's name** |
| 17 | UNIT | **Anak**; do not label this unit Kanak-kanak |
| 19 | BIL. UNIT | Number of children receiving subsidy; editor label **BIL. ANAK** |
| 21 | KADAR | Prescribed **RM180.00 per anak per month**, populated automatically and read-only |
| 26 | BILANGAN BULAN | User-entered number of budgeted months |
| 27 | ANGGARAN JUMLAH SETAHUN | Automatically calculated **BIL. UNIT × KADAR × BILANGAN BULAN** |

No KATEGORI1–3 or individual child-name field is required. Do **not** set BIL. UNIT to a forced value of 1, impose one child per row, or add an unapproved rule requiring one parent per row: those earlier suggestions were not approved. The canonical exported headers remain unchanged; only the editor's contextual labels differ.

**Boundary:** This documents budgeting fields and the owner's calculation decision, not new eligibility or entitlement conditions. No changes to reference CSV data, other OS42000 variants, catalogue/Kod Aktiviti mapping, Laravel, actual exporter or PostgreSQL. AD-008 remains Draft.
