# Gaji & Emolumen — OS11000, OS12000 and OS13000

Status: requirements discovery. This note supports [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md) and [AD-010](../architecture/AD-010-analysis-reporting-and-excel.md). Gaji & Emolumen has a separate dataset and worksheet. Its official export template remains pending.

| OS | Scope | Evidence to obtain |
| --- | --- | --- |
| OS11000 | Gaji dan Upahan | Current worksheet or sample for salary base, employee or position counts, periods, adjustments and annual total. |
| OS12000 | Elaun/Imbuhan Tetap | Eligible allowance components, their rates and effective periods, and how they are entered today. |
| OS13000 | Emolumen Lain | Components actually bid under this OS, calculation basis and whether each uses recurring periods or a one-off amount. |

## First decision

Identify the current unit of preparation for each OS: one entry per named officer, one group entry per grade or post, or import from an existing payroll extract. The answer may differ by OS. A blank template or anonymised worked example can establish the granularity and the fields without collecting employee records during discovery.

## Decisions that follow

- Confirm who prepares estimates at PTJ, sector and school level. Record any school-level contribution and the responsible PTJ separately, with no implied expansion of access.
- Confirm the canonical field list and authority for rates, periods, eligibility and annual calculations for each OS, including changes within a year. The approved OS14000 overtime median formula is specific to that editor and supplies no payroll rule.
- Confirm the official worksheet headers and mapping from the official template. Retain the existing Perbelanjaan dataset separately.
- Specify completeness, examples and test cases per OS before implementation, then integrate applicable payroll estimates into Bidaan completeness.

No payroll fields, formulas, reference mappings, access changes or implementation are approved by this discovery note.
