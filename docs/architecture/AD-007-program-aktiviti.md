# AD-007 — Program/ Aktiviti

**Status:** Finalised — approved architectural contract; OS-specific specialisation deferred to AD-008  
**Date:** 10 November 2026  
**Related:** AD-002 — Canonical Financial Data Hierarchy; AD-004 — Anggaran Wizard Architecture; AD-008 — OS Catalogue & Calculation Framework

## 1. Foundational principle

**Program/ Aktiviti is a guided workflow façade (a lobby) into the existing Anggaran (OS) wizards.** It is not an alternative financial hierarchy, separate calculation engine, duplicate financial store or independent wizard.

**Anggaran = OS** is the main financial entity and stores **Butiran Anggaran**. Butiran may be entered directly from the Anggaran (OS) list or through a Program/ Aktiviti, which guides the Penyedia Anggaran through its selected OS.

The canonical hierarchy remains:

**PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**

Each programme has a unique **Program/ Aktiviti ID**. Programme-related Butiran Anggaran reference this ID and their parent Anggaran (OS), regardless of entry route; free-text programme names are never linking keys.

## 2. OS selection and shared wizard

1. The programme displays applicable OS as a checkbox checklist, including progress/status for each selected OS.
2. Checking an OS enables the consistent action **Lengkapkan Butiran Anggaran**. Unchecked OS have no such action.
3. Clicking the action opens the **existing AD-004 Anggaran wizard**, scoped to the current Program/ Aktiviti ID and selected OS. No separate wizard or financial records are created.
4. When entering through Program/ Aktiviti, the wizard's OS dropdown and **Sebelumnya/Seterusnya** navigation show only OS selected for that programme. **Kembali ke Program/ Aktiviti** returns to the same programme checklist, subject to safe autosave/navigation.
5. Direct entry from the main Anggaran list continues to offer all applicable OS in the Bidaan; programme associations must still be preserved where relevant.
6. Some OS may be selected and locked automatically by programme characteristics.

### Safeguards for changing OS selection

- Checking an OS adds it to the programme's required OS list.
- Unchecking an OS with **no saved programme-associated Butiran Anggaran** is allowed immediately.
- Unchecking an OS **with saved programme-associated Butiran Anggaran is blocked** until those records are explicitly removed through the authorised deletion workflow.
- Never silently delete, detach, orphan or reassign financial records. Apply AD-004 audit/deletion rules and preserve historical submission snapshots.
- Automatically required OS remain locked while their triggering programme characteristics apply.

## 3. Completion and validation

Programme completion is calculated from **its own** required fields and programme-associated Butiran Anggaran, separately from the overall OS completion in the Bidaan:

- **Belum Mula:** no OS selected.
- **Belum Lengkap:** OS selected but no associated Butiran, any selected OS lacking valid Butiran, any associated Butiran incomplete/invalid, or required programme fields invalid.
- **Lengkap:** all required programme fields valid, every selected OS has at least one valid programme-associated Butiran, and all associated Butiran are complete/valid.
- Revalidate automatically when programme or associated financial data change. No manual **Tandakan Lengkap**.
- Overall Anggaran (OS) completion evaluates all its relevant Butiran in the Bidaan; programme completion evaluates only Butiran linked to the given programme. One programme may be Lengkap while another using the same OS is Belum Lengkap.

## 4. Maklumat Program/ Aktiviti — approved four-section arrangement

### A. Objektif

Three cascading, database-backed selections, in this order:

1. **Teras Strategik**
2. **Strategi**
3. **Prakarsa**

Each is required; available choices depend on the preceding selection.

### B. Maklumat Program/ Aktiviti

- **Nama Program/ Aktiviti** — required.
- **Jenis Program/ Aktiviti** — required dropdown, **Umum; Sukan; Kokurikulum**.
- **Kategori Program/ Aktiviti** — required dropdown, **Kursus/ Bengkel; Taklimat; Mesyuarat; Perjumpaan; Pemantauan**.

Do not mix the Jenis and Kategori choices. **Pegawai** and **Guru** are also separate participant classifications, not a combined category.

### C. Pelaksanaan

- **Peringkat** — Kebangsaan; Negeri; Daerah.
- **Kaedah Pelaksanaan** — Pakej; Bukan Pakej.
- **Tarikh Mula** and **Tarikh Tamat** — valid chronological dates within the selected Kitaran ABM year.

**Pakej** reveals **Komponen Pakej** with **Makan Minum, Penginapan, Dewan**; at least one applicable component must be selected. The system automatically selects and locks the relevant OS, such as OS29000 Pakej or applicable OS42000, according to rules to be specified in AD-008. Switching to **Bukan Pakej** removes the package requirement only subject to the saved-Butiran safeguards above; never silently remove financial records.

### D. Bilangan Jemputan/ Urus Setia dan Peserta

These are **separate, required, positive headcounts**, captured once at programme level through category/classification breakdown rows. The totals are **derived from the rows**, not separately entered. Every row quantity must be a positive integer (minimum 1); **each group must have at least one valid row and a positive total**. Zero, negative and fractional headcounts are invalid.

#### D1. Jemputan/ Urus Setia

Only the following categories are available:

| Kategori | Required classification |
| --- | --- |
| **Pegawai** | **Gred 9 dan ke atas** |
| **AKP** | **Gred 1–8** |

Each row records category, applicable grade and positive **Bilangan**. **Guru, Murid and Lain-lain are not options** for Jemputan/ Urus Setia.

#### D2. Peserta

| Kategori | Required classification |
| --- | --- |
| **Pegawai** | **Gred 9 dan ke atas** |
| **Guru** | **Gred 9 dan ke atas** |
| **AKP** | **Gred 1–8** |
| **Murid Rendah** | **Tahun 1–6** |
| **Murid Menengah** | **Tingkatan 1–6** |
| **Lain-lain** | **Catatan wajib**; no grade |

Each row records category, its required grade/Tahun/Tingkatan or Catatan, and positive **Bilangan**. **Pegawai and Guru must remain distinct categories**, even though their grade ranges are the same. **Lain-lain remains available for Peserta only**, with mandatory Catatan.

#### Financial calculation principle

Headcounts are **financial estimation inputs**, not merely descriptive metadata. Applicable OS estimates use eligible quantities and relevant rates (**Bilangan Layak × Kadar Berkaitan**), with exact eligibility, mappings, rates and formulas defined under **AD-008**. Relevant OS wizards reuse programme breakdown data without redundant re-entry; changes to breakdowns trigger recalculation or revalidation of affected estimates.

## 5. Data context and draft handling

**Aktiviti Kewangan, PTJ and Tempat Bertugas** come from the user's authorised, database-backed context rather than being redundantly re-entered. Users may progressively **Simpan Draf** with incomplete programme fields; however, the programme cannot be **Lengkap** until all required programme fields, both positive headcount groups and selected OS/Butiran validations pass.

## 6. Complete programme inheritance and change management

- **Every field and value available in Program/ Aktiviti is inherited by its associated Anggaran (OS) wizard**, including identity, objectives, classifications, implementation dates, Pakej components, both detailed headcount breakdowns and authorised organisational context.
- Program/ Aktiviti is the **single authoritative source** for programme-owned values. The wizard must not require duplicate entry or permit independent editing of inherited programme fields; changes occur in Program/ Aktiviti.
- All inherited information is available to the wizard, but only contextually relevant fields need to be displayed; inheritance does not imply every field participates in every OS calculation.
- For **draft** Anggaran, programme changes synchronise automatically, triggering recalculation or revalidation of affected estimates while preserving unrelated OS-specific inputs where valid. Financial amount changes must be clearly indicated to users.
- **Submitted financial snapshots are immutable**. Later changes belong to the authorised Pindaan workflow and cannot rewrite historical submissions.
- The **exact inheritance-to-OS mappings, calculation dependencies, eligibility rules, rates, formulas, and specialised OS requirements must be specified for every applicable OS in AD-008 before implementing the complete inheritance/recalculation engine**. This is an explicit implementation dependency, not an unresolved AD-007 decision.

## 7. Design boundary and closure

- **AD-004 is finalised**; this document reuses its shared wizard, autosave, row validation, safe navigation, deletion and audit rules.
- **AD-008** will determine precise OS mapping, package eligibility, automatic prepopulation, rates, calculation formulas and specialised OS wizard behaviour.
- **AD-007 is finalised.** Its programme workflow, data contract, validations and change-management principles are approved. Detailed OS pages and their calculation mappings are the next design task under AD-008.
