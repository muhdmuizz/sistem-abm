# AD-002 — Canonical Financial Data Hierarchy

**Project:** Sistem ABM  
**Date:** 10 November 2026  
**Status:** Agreed — architectural decision  
**Related:** [AD-001 — Aliran Kerja Anggaran dan Bidaan](AD-001-anggaran-bidaan-workflow.md)

## Decision

The canonical hierarchy is:

**PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**

- **PTJ** is the organisation preparing the estimate (for example, a PPD).
- **Aktiviti Kewangan** is the financial activity applicable to that PTJ (for example, Pengurusan Pentadbiran Negeri / PPN).
- **Bidaan** is the collection of OS estimates for one PTJ, one Aktiviti Kewangan, and the applicable Kitaran ABM/submission context.
- **Anggaran (OS)** is one OS-specific estimate inside a Bidaan. **Users call it OS; the internal system calls it Anggaran. These are the same transactional entity**, not separate layers.
- **Butiran Anggaran** are the granular financial calculation rows belonging to an Anggaran; an Anggaran may contain hundreds or thousands of rows.

## Relationships and integrity

1. One PTJ may have multiple applicable Aktiviti Kewangan.
2. For the applicable Kitaran and submission context, each PTJ/Aktiviti Kewangan combination has one Bidaan.
3. One Bidaan contains multiple Anggaran (OS).
4. Each applicable OS appears at most once within a Bidaan; its many expenditure items are stored as Butiran Anggaran.
5. Each Butiran Anggaran belongs to exactly one Anggaran.
6. Totals must be calculated or reconciled from authoritative granular records: **Jumlah Anggaran = sum of its Butiran Anggaran; Jumlah Bidaan = sum of its Anggaran**.
7. Saved drafts and formal submission are different lifecycle states, consistent with AD-001. Submission and amendment history must be preserved; exact versioning rules remain to be designed.

## Excel template interpretation

The existing ABM Excel workbook has approximately 31 OS worksheets; some worksheets combine OS variants. **For Sistem ABM, use one logical Anggaran structure per applicable OS**, even if the original Excel worksheet combined them. Each OS may have its own wizard, input fields, validation, and calculation rules. A reference OS catalogue and wizard definitions may be separate from transactional Anggaran records.

## Capacity is informational, not hard-coded

A PPD currently may have around **five Bidaan** based on its applicable Aktiviti Kewangan; a Bidaan may cover up to **31 Anggaran (OS)** based on the current template. These are **descriptive reference counts only**. Do not enforce numeric caps of five or 31 in database constraints, forms, or business logic.

Actual availability is driven by configured Aktiviti Kewangan, applicable OS and the Anggaran wizards implemented. No arbitrary small cap is placed on Butiran Anggaran; performance and validation must accommodate large datasets.

## UI and terminology

- User-facing terminology: **OS** (e.g. Tambah OS, Senarai OS, Simpan OS).
- Internal domain terminology: **Anggaran** and **Butiran Anggaran**.
- A **Bidaan** groups OS estimates; it is **not** a separate Bidaan per OS.
- No Bakul Bidaan. Follow AD-001: **Cipta Anggaran → Simpan Anggaran → Senarai Anggaran → Hantar Bidaan**.

## Out of scope / pending design

- Submission rounds, reopening and amendment versioning.
- Detailed OS wizard definitions and calculation formulas.
- Exact 27-column export mapping and reference data.
- Detailed authorisation matrix and review status transitions.

These decisions must be specified without contradicting this hierarchy.
