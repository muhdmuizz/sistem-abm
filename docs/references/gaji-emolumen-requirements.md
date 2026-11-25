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
- The wizard generates **Isi** rows equal to Pengisian and **Kosong** rows equal to Kekosongan for each Jawatan. Penyedia Anggaran enters officer names in the Isi rows. Change Pengisian in JIK to revise the Isi/Kosong counts.
- The wizard cannot add or delete position rows or edit the Jawatan or row count. Add a Jawatan or amend Bil. Perjawatan in JIK to change the row structure. The editable payroll fields within each generated row remain to be specified.

## Confirmed JIK scope — offices and schools

- Each PPD prepares one JIK for its own office staff across all its sectors. Use Pejabat PPD as the office's own Tempat Bertugas in Gaji & Emolumen.
- JPNT prepares one JIK for its headquarters staff across all its sectors. Use Pejabat JPNT as the headquarters' own Tempat Bertugas in Gaji & Emolumen.
- Schools remain distinct workplaces, with their staff recorded separately from office JIKs.
- The office workplaces are specific to Gaji & Emolumen. Perbelanjaan continues to use the AD-005 workplace rules.

## Ketua Jabatan — individual officer confirmed

The owner confirmed that Ketua Jabatan identifies the individual officer holding the designation. Proposed placement: select the officer in the combined Gaji & Emolumen wizard after the named incumbents have been entered. The selector would offer only named officers attached to filled positions in the applicable organisational scope. JIK remains the source of Jawatan and establishment counts.

The owner confirmed one Ketua Jabatan per Tempat Bertugas across the combined Gaji & Emolumen group. Under [AD-005](../architecture/AD-005-identity-roles-organisation.md), an SMK PTJ can also be its own Tempat Bertugas. The confirmed office workplaces each have one Ketua Jabatan, as does each school workplace. Whether selection is mandatory, its effect on calculations or workflow, and handling of a changed incumbent also remain open.

## OS11000 — confirmed vacant-post salary basis

- Each **Kosong** row carries an OS11000 salary estimate. Use **Gaji Median** for the row's Jawatan and Gred as the monthly salary basis.
- Jawatan is inherited from JIK. For now, the Penyedia Anggaran selects the Gred in the wizard. The system resolves Gaji Median using the selected Gred and inherited Jawatan.
- Visually distinguish Kosong rows in the wizard and retain the visible **Kosong** status label so vacancy remains identifiable without relying on colour.
- **Bilangan Bulan is fixed at 3** for every Kosong row and is read-only in the wizard. The OS11000 estimate per vacant post is **Gaji Median × 3**.

## OS11000 — confirmed filled-post salary input

- For an **Isi** row, the salary input is the officer's **Gaji Pokok Terakhir**.
- Only a **Penyedia Anggaran** authorised for the applicable workplace may enter this value in the Gaji & Emolumen wizard.
- The salary reference date and how to apply salary changes during the budget year remain open.

## Decisions that follow

- Confirm who prepares each office and school JIK under the applicable access rules.
- Confirm the canonical field list and authority for rates, periods, eligibility and annual calculations for each OS, including changes within a year. The approved OS14000 overtime median formula is specific to that editor and supplies no payroll rule.
- Confirm the official worksheet headers and mapping from the official template. Retain the existing Perbelanjaan dataset separately.
- Specify completeness, examples and test cases per OS before implementation, then integrate applicable payroll estimates into Bidaan completeness.

The owner confirmed the JIK workflow above. Detailed payroll fields and calculation rules, reference mappings, access changes and implementation remain open.
