# OS42000 — Subsidi Yuran TASKA

**Owner-confirmed:** 12 November 2026. **Scope:** the entire currently supplied reference; no further reference options or calculations are established.

| Attribute | Approved value |
| --- | --- |
| Kod Objek | **42000** |
| Name | **Subsidi Yuran TASKA** |
| Aktiviti Kewangan | **Pengurusan Pentadbiran Negeri (PPN)** |
| Kod Aktiviti | **Blank / not assigned** |
| Kadar | **RM180.00** |
| Kadar mode | **Automatically populated from prescribed reference** |

**Machine-readable reference:** [OS42000-subsidi-yuran-taska-kadar.csv](OS42000-subsidi-yuran-taska-kadar.csv), exactly one rate record.

The owner will supply any Kod Aktiviti later through the planned **Superadmin administration/reference-data page** if it becomes available. Leave it **genuinely blank** in the current mapping; do not generate, borrow, or guess any code, and do not present the future administrative capability as already implemented.

The owner explicitly states that **RM180.00 is the only available reference for this variant at present**. No category hierarchy, payment unit, quantity/duration calculation, entitlement, eligibility, or rate component has been supplied. Do not invent them. The [mandatory-Kadar principle](shared-mandatory-kadar-rule.md) applies.

## Distinction from the other OS42000 reference

[OS42000 — Utiliti (Pendidikan Rendah)](OS42000-utiliti-pendidikan-rendah-reference.md) reuses OS23000 Pukal choices, with **mandatory user-entered Kadar** and Kod Aktiviti `030228`. Subsidi Yuran TASKA is under **PPN**, with **no Kod Aktiviti yet**, and **fixed RM180.00 Kadar**.

Only reference documentation/catalogue mapping are updated; no Laravel, PostgreSQL or user-facing Superadmin admin page is implemented by this commit.
