# Gaji & Emolumen — OS11000, OS12000 and OS13000

Status: requirements discovery. This note supports [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md) and [AD-010](../architecture/AD-010-analysis-reporting-and-excel.md). Gaji & Emolumen has a separate dataset and worksheet. Its official export template remains pending.

| OS | Scope | Evidence to obtain |
| --- | --- | --- |
| OS11000 | Gaji dan Upahan | Current worksheet or sample for salary base, employee or position counts, periods, adjustments and annual total. |
| OS12000 | Elaun/Imbuhan Tetap | Eligible allowance components, their rates and effective periods, and how they are entered today. |
| OS13000 | Sumbangan Berkanun | Confirm the source of each monthly contribution amount. |

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

## PPD preparation — workplace inclusion in Rumusan

Only PPDs use **Rumusan** to choose which Tempat Bertugas to include for the selected Kitaran, PTJ and Aktiviti Kewangan. Offer **Tambah Tempat Bertugas** from the existing workplace references, restricted to the user's authorised workplaces and those applicable to the selected activity. The included workplaces determine the available choices in JIK and Terperinci, and the rows in the Rumusan worksheet for that extraction scope. Each workplace has separate JIK, Butiran Anggaran and Ketua Jabatan; JIK determines its Terperinci rows as before.

| Selected Aktiviti Kewangan | Confirmed workplace applicability |
| --- | --- |
| Pengurusan Pentadbiran Negeri (PPN) | Pejabat PPD is eligible and optional. If included, it appears first in Rumusan. |
| Pendidikan Rendah | Penyedia Anggaran selects the relevant sekolah rendah from the authorised workplace list. |

Pejabat PPD belongs to PPN and is excluded from other activities. The available list uses the existing authorised non-sector workplace references for the selected PPD. The Penyedia Anggaran determines which schools or other workplaces are relevant to the selected activity; Sistem ABM does not automatically classify schools by activity. Each PPD activity starts with no workplaces included. JPNT and SMK PTJ do not use a selectable Pusat Kos inclusion list.

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
- **Gaji Asal** is the Gaji Pokok Terakhir as at **1 January of the Kitaran year**. For Kitaran 2028, use 1 January 2028.

### OS11000 filled-post columns

1. TPG 20XX, where 20XX is the Kitaran year
2. Gaji Asal
3. Bil. Bulan (Gaji Asal)
4. Jumlah Kecil (Gaji Asal)
5. KGT (Kenaikan Gaji Tahunan), the rate
6. Gaji Baharu
7. Bil. Bulan (Gaji Baharu)
8. Jumlah Kecil (Gaji Baharu)
9. Jumlah OS11000

### TPG and monthly salary allocation

TPG options within the Kitaran year are **1 January, 1 April, 1 July and 1 October**.

For **TPG 1 January**, enter Gaji Asal as the 1 January salary basis before applying that year's KGT in the estimate. The wizard sets Bil. Bulan (Gaji Asal) to zero, with no Gaji Asal subtotal. It calculates **Gaji Baharu = Gaji Asal + KGT**, sets Bil. Bulan (Gaji Baharu) to **12**, and calculates **Jumlah Kecil (Gaji Baharu) = (Gaji Asal + KGT) × 12**. Jumlah OS11000 equals that subtotal. Zero-month and zero-subtotal cells may display blank or zero.

The owner confirmed the following read-only month allocations for a full Kitaran year. Gaji Baharu applies from the TPG month.

| TPG | Bil. Bulan (Gaji Asal) | Bil. Bulan (Gaji Baharu) |
| --- | ---: | ---: |
| 1 January | 0 | 12 |
| 1 April | 3 | 9 |
| 1 July | 6 | 6 |
| 1 October | 9 | 3 |

For each TPG, multiply each salary by its allocated months to calculate its subtotal. **Jumlah OS11000** is the sum of the two subtotals. The wizard looks up the **KGT rate** from the salary reference for the selected **Gred** and displays it as a read-only value. The exact reference dataset and handling of any missing rate remain to be verified.

## OS12000 — fixed-allowance columns

| Monthly column | Annual column |
| --- | --- |
| ITJU/ Bulan | ITJU/ Tahun |
| ITK @ ITKA/ Bulan | ITK @ ITKA/ Tahun |
| ITP @ BIPW/ Bulan | ITP @ BIPW/ Tahun |
| BISH @ BIW/ Bulan | BISH @ BIW/ Tahun |

Each annual amount is its monthly amount × 12 for **Isi**, or × 3 for **Kosong**. **Jumlah OS12000** is the sum of those annual amounts. Draft PR #118 previews manual monthly entry for Isi and Kosong rows under Terperinci using the same JIK rows as OS11000. Actual monthly rate sourcing remains open.

**Elaun-elaun Lain:** The owner confirmed that each item has monthly and annual amounts and follows the same calculation as the four fixed allowances: **/ Tahun =/ Bulan × 12 for Isi or × 3 for Kosong**. The Penyedia Anggaran types the allowance name as free text for each item. For Excel, retain one row per officer in the existing Gaji & Emolumen worksheet. Export two numeric summary columns: **Elaun-elaun Lain/ Bulan**, the sum of item monthly amounts, and **Elaun-elaun Lain/ Tahun**, the sum of item annual amounts. No additional worksheet is needed for these items. Retain individual allowance names and amounts in Sistem ABM. Exact Excel column positions remain subject to the official template.

## OS13000 — Sumbangan Berkanun

The Terperinci grid places **OS13000/ Bulan** and **OS13000/ Tahun** after Jumlah OS12000. The annual amount is the monthly amount × 12 for Isi or × 3 for Kosong. The local preview uses manual monthly entry.

All three OS apply a fixed three-month multiplier to Kosong rows. OS11000 uses the Gaji Median for the selected Jawatan and Gred. OS12000 applies the multiplier to the four fixed allowances and each Elaun Lain item. OS13000 applies the multiplier to its monthly amount.

## Rumusan worksheet — confirmed for Excel extraction

Include **Rumusan** as a separate worksheet in the future Excel extraction alongside **Gaji & Emolumen** and **Perbelanjaan**. Mirror the on-screen Rumusan for the extraction scope, with one row per included Tempat Bertugas and Pejabat PPD first when included in its PPD scope.

| # | Tempat Bertugas | Bil. Perjawatan | Bil. Pengisian | Bil. Kekosongan | OS11000 (RM) | OS12000 (RM) | OS13000 (RM) | Jumlah (RM) |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |

Place **Jumlah** in the final row and sum all seven numeric count and amount columns. Export count and monetary cells as numbers; display monetary amounts to two decimal places. The two numeric Elaun-elaun Lain columns remain on the Gaji & Emolumen detail worksheet. This records the export requirement, with implementation pending.

## Decisions that follow

- Confirm who prepares each office and school JIK under the applicable access rules.
- Confirm the canonical field list and authority for rates, periods, eligibility and annual calculations for each OS, including changes within a year. The approved OS14000 overtime median formula is specific to that editor and supplies no payroll rule.
- Confirm the official worksheet headers and mapping from the official template. Retain the existing Perbelanjaan dataset separately.
- Specify completeness, examples and test cases per OS before implementation, then integrate applicable payroll estimates into Bidaan completeness.

The owner confirmed the JIK workflow above. Detailed payroll fields and calculation rules, reference mappings, access changes and implementation remain open.
