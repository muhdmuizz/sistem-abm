# Gaji & Emolumen — OS11000, OS12000 and OS13000

Status: requirements discovery. This note supports [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md) and [AD-010](../architecture/AD-010-analysis-reporting-and-excel.md). Gaji & Emolumen has a separate dataset and worksheet. Its official export template remains pending.

| OS | Scope | Evidence to obtain |
| --- | --- | --- |
| OS11000 | Gaji dan Upahan | Current worksheet or sample for salary base, employee or position counts, periods, adjustments and annual total. |
| OS12000 | Elaun/Imbuhan Tetap | Eligible allowance components, their rates and effective periods, and how they are entered today. |
| OS13000 | Emolumen Lain | Components actually bid under this OS, calculation basis and whether each uses recurring periods or a one-off amount. |

## Confirmed JIK workflow

- Penyedia Anggaran prepares **Jawatan, Isi, Kosong (JIK)** once for the combined OS Gaji & Emolumen group covering OS11000, OS12000 and OS13000.
- For each Jawatan, enter Bil. Perjawatan and Pengisian. Sistem ABM calculates **Kekosongan = Bil. Perjawatan − Pengisian**.
- Bil. Perjawatan determines the number of wizard rows for that Jawatan. For example, a Jawatan with 5 Perjawatan and 3 Pengisian yields 2 Kekosongan and 5 wizard rows.
- The wizard cannot add or delete position rows or edit the Jawatan or row count. Add a Jawatan or amend Bil. Perjawatan in JIK to change the row structure. The editable payroll fields within each generated row remain to be specified.

## Open decision — Ketua Jabatan

Confirm whether Ketua Jabatan is a designation on one of the filled positions or serves another purpose such as workflow approval, and confirm its organisational scope. If it is a designation on a filled position, consider setting it with JIK as part of the establishment details. If the designation needs a named incumbent first and names are entered only in the wizard, consider selecting it in the wizard from the relevant filled rows. No designation timing or approval authority has been decided.

## Decisions that follow

- Confirm who prepares estimates at PTJ, sector and school level. Record any school-level contribution and the responsible PTJ separately, with no implied expansion of access.
- Confirm the canonical field list and authority for rates, periods, eligibility and annual calculations for each OS, including changes within a year. The approved OS14000 overtime median formula is specific to that editor and supplies no payroll rule.
- Confirm the official worksheet headers and mapping from the official template. Retain the existing Perbelanjaan dataset separately.
- Specify completeness, examples and test cases per OS before implementation, then integrate applicable payroll estimates into Bidaan completeness.

The owner confirmed the JIK workflow above. Detailed payroll fields and calculation rules, reference mappings, access changes and implementation remain open.
