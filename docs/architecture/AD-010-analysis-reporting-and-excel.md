# AD-010 — Analysis, Reporting & Excel

**Status:** Finalised — reconciled architecture; implementation mappings remain pending  
**Date:** 11 November 2026  
**Related:** AD-002, AD-003, AD-004, AD-006, AD-007, AD-008, AD-009

## Scope and change control

This document records the decisions established in the AD-010 discussion, reconciled after an assistant introduced unsolicited proposals. The worksheet clarification is part of the existing Excel decision, **not a new decision**. Do not interpret suggestions about additional export filters, extra worksheet rules, or Program/ Aktiviti export filters as approvals. No implementation is claimed.

## Decision 01 — Canonical reporting source

Financial reporting and official Excel derive from the canonical **Butiran Anggaran** belonging to **Anggaran (OS)** within **Bidaan** (AD-002). Financial totals must reconcile from valid authoritative granular records, rather than independent competing totals. Respect financial versions, authorisation, and immutable submission/finalisation snapshots. OS-specific calculation rules belong to AD-008.

## Decision 02 — Dashboard financial overview

Use only two primary financial amount indicators:
- **Jumlah Anggaran Semasa:** current working estimates, including applicable drafts.
- **Jumlah Muktamad:** latest applicable finalised financial snapshots.

These are distinct perspectives, not additive amounts. Do not double-count a Bidaan across draft, submitted, revised or finalised versions. Keep workflow progress/status separate from amount categories; primary Bidaan statuses follow AD-009: Dalam Penyediaan, Dalam Semakan, Dikembalikan, Muktamad.

## Decision 03 — Reporting dimensions and Analisis filters

Support reporting by existing, authorised database dimensions and relationships, extensible when actual reporting requirements justify it: Kitaran ABM, Fasa/Pusingan, PTJ, Sektor, Aktiviti Kewangan, OS/Kod Objek, and existing programme associations where relevant. **Do not invent a Bidang field.** Not every reporting dimension is a mandatory filter on every page.

The **Analisis** page has exactly this agreed cascading filter hierarchy:
**Aktiviti Kewangan → PTJ → OS**.
Selections must respect existing reference data and permissions; one or multiple selections can be supported. **Tempat Bertugas is excluded from Analisis** because the historical expenditure reference is PTJ-level. Kitaran ABM and Fasa/Pusingan are contextual selections rather than additions to this three-filter chain.

This does **not** amend AD-004's approved **Senarai Anggaran** filters: Aktiviti Kewangan → PTJ → Tempat Bertugas → Status Anggaran.

## Decision 04 — Historical expenditure reference

Historical actual expenditure is a separate, read-only comparison dataset, not an Anggaran/Bidaan transaction. Importing or correcting historical expenditure must never automatically alter financial estimates or submitted/finalised snapshots. Support multiple years, original source metadata, matching by existing identifiers and auditability. Do not fabricate mappings when source codes are incompatible.

## Decision 05 — Official Excel extraction

Preserve the previously agreed export scopes:
- **Keseluruhan Data**;
- **Bidaan Lengkap** (one or more selected Bidaan);
- **OS Terpilih** (one or more selected OS).

The **complete dataset** is one Excel workbook with two worksheets:
1. **Gaji & Emolumen** — OS11000, OS12000 and OS13000, with its own official dataset structure (exact columns await the official template).
2. **Perbelanjaan** — applicable OS14000 through OS29000, and OS42000, using the official **27-column granular** dataset in its exact official column order.

When users select particular OS, **export only the selected OS**, in the applicable worksheet format. Do not impose new empty-sheet, conditional-sheet, naming, or filter rules beyond the agreed export behaviour. The two-sheet explanation describes the **default complete set**, not a new architectural decision. Exports remain permission-scoped and version-aware.

**No additional Ekstrak Excel filters were approved during the later clarification.** In particular, suggestions to introduce Program/ Aktiviti as an export filter, a Penapis Tambahan UI, or an additional filter set are excluded. Existing agreed scope/selection remains unchanged.

## Decision 06 — Analisis table and comparison

Analisis is **table-driven**, without charts or extra summary cards. Its only financial cards are **Anggaran Semasa** and **Jumlah Muktamad**.

The table has OS rows on the left; eligible, completed historical expenditure years as columns; a right-hand amount column switching between **Anggaran Semasa** (default) and **Jumlah Muktamad**; and a **% difference** column calculated against a user-selected eligible historical year. Changing the amount perspective or comparison year updates the comparison, while both financial cards remain visible.

Percentage difference = (selected ABM amount − selected historical expenditure) / selected historical expenditure × 100. When the historical denominator is zero or missing, display **—**, not a misleading percentage. Missing historical values must not be treated as zero.

Only **complete/verified** historical years appear. For a Kitaran ABM 2028 example, 2027 expenditure is not displayed until its December year-end data is available and verified; 2024–2026 can appear when complete. Eligible years are data-driven, not hardcoded. Default comparison uses the latest eligible completed historical year. The ABM target year is not a historical expenditure column.

## Decision 07 — Historical expenditure Excel import

Historical expenditure is imported and managed by **Superadmin**. Validate against existing references, preview unmatched/errors, guard against duplicate imports, and preserve corrections/version history and source metadata. Other users consume this as read-only reference data.

The actual source column headings are:
`Program | Kod PTJ | PTJ | Aktiviti | Objek | Butiran Perbelanjaan | Bulan | Peruntukan Kemaskini | Baki Sebenar | Belanja Bersih | Belanja Bulanan | Permohonan`.

Interpretation:
- **Program** = **Aktiviti Kewangan** (explicitly clarified).
- **Kod PTJ / PTJ** = PTJ code/name.
- **Aktiviti** = original source activity code; do not confuse with Program/Aktiviti Kewangan.
- **Objek** = Kod Objek; **Butiran Perbelanjaan** = original description.
- **Bulan = 12** identifies the December year-end reporting snapshot, subject to completeness/verification of source coverage.
- **Belanja Bersih** = cumulative expenditure **including December**, the annual expenditure figure at the December snapshot. **Do not add Belanja Bulanan** and do not sum cumulative figures from multiple months.
- **Belanja Bulanan** = December's monthly expenditure, separately retained as source reference.
- **Peruntukan Kemaskini** = revised allocation, not expenditure; **Baki Sebenar** = balance, not expenditure.
- **Permohonan** is excluded from the imported analytical dataset.

The import's financial year must come from verified import context/metadata because the provided source headings do not include a year column. Preserve the correct financial identity and prevent duplicate December records from inflating totals.

## Decision 08 — PTJ-level comparison

Historical expenditure is available **at PTJ level only**, not Tempat Bertugas. Match against existing **Aktiviti Kewangan, PTJ, and OS/Kod Objek** identities, preserving source Aktiviti and description. Aggregate current ABM records to compatible PTJ/OS scope for like-for-like comparison. Never invent workplace attribution. This is why **Tempat Bertugas is removed only from Analisis**, not from unrelated approved screens.

## Decision 09 — Expandable OS rows

The main Analisis table shows aggregated OS rows. Expanding an OS goes **directly to Butiran Perbelanjaan**, preserving relevant Kod Objek and descriptions, without an intermediate OS → Kod Objek → Butiran Perbelanjaan navigation level. Keep the presentation compact and totals reconcilable. Source descriptions are not unique matching keys; do not imply historical PTJ-level source rows map one-to-one to granular ABM Butiran.

## Decision 10 — Analisis does not export Excel

**Do not put an Ekstrak Excel action on Analisis.** Official Excel extraction remains separate and is governed by Decision 05.

## Compatibility and outstanding implementation inputs

- AD-002 canonical hierarchy and totals are unchanged.
- AD-004 Senarai Anggaran filters and shared wizard are unchanged.
- AD-007 Program/ Aktiviti remains a guided façade, not a second financial store.
- AD-009 whole-Bidaan review, Ketua-only finalisation and immutable snapshots are unchanged.
- AD-008 must supply OS-specific financial calculations and mappings before end-to-end implementation.
- The official **Gaji & Emolumen** export column template is not yet supplied; **do not invent its schema**.
- This is an architecture record, not evidence of completed code, testing, or deployment.

**Reconciliation boundary:** Exclude unsolicited proposals made after the user's worksheet explanation. Any future change to an approved requirement must be separately and explicitly authorised.
