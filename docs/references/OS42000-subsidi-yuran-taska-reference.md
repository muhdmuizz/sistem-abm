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
