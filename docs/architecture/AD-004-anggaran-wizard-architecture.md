# AD-004 — Anggaran Wizard Architecture

**Status:** Finalised — OS-specific specialisation deferred  
**Scope:** Senarai Anggaran (OS), shared wizard framework, Butiran Anggaran editing and navigation  
**References:** AD-001, AD-002, AD-003, AD-005, AD-006, AD-007  
**Updated:** 10 November 2026

## 1. Purpose and canonical structure

Provide one consistent Anggaran (OS) experience while allowing different OS fields, eligibility checks and financial calculations to be specialised later.

The canonical hierarchy remains **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran** (AD-002). **Anggaran is the OS financial entity**, not a separate cart or additional financial layer. An Anggaran owns independently identifiable, persistently saved Butiran Anggaran. Its total is derived from its saved, valid granular financial records according to the applicable calculation rules.

There are two routes into the **same** OS wizard and underlying records:
- Directly from **Senarai Anggaran (OS)** under the applicable Bidaan.
- Through **Program/ Aktiviti**, acting as a guided workflow façade into related OS (AD-007), not a parallel financial hierarchy.

A Butiran Anggaran associated with a programme references its **unique Program/ Aktiviti ID**, not merely a matching name. This association must survive copying, editing, saving and wizard navigation; direct OS entry must not silently lose programme context.

## 2. Roles, activation and submission boundary

1. User opens **Bida Peruntukan** and sees only authorised PTJ/Aktiviti Kewangan scope; inactive Bidaan are visually dimmed.
2. **Penyedia Bidaan** activates the Bidaan. **Penyedia Anggaran** activates required OS, creating Anggaran records. OS not activated remain visible as applicable but dimmed.
3. Penyedia Anggaran opens the OS wizard, creates/edits Butiran Anggaran and saves drafts independently.
4. Anggaran completeness is evaluated automatically from its retained Butiran Anggaran (Section 5).
5. **Penyedia Bidaan** formally submits the Bidaan only when at least one active OS is complete and **all active OS are complete** (AD-003). Activation and completeness are distinct.
6. After formal submission, the submitted records are read-only until the governing review/return/Fasa rules permit editing. Formal resubmission creates a new immutable financial snapshot (AD-003, AD-006).

Deactivating an OS before submission does not delete its data; it is excluded from active Bidaan totals/readiness and revalidated when reactivated.

## 3. Senarai Anggaran (OS) — primary hub

The main page lists OS/Anggaran within the user's authorised scope and provides the action to open each OS wizard. Proposed row information:
- OS code and description;
- **Aktiviti Kewangan identifier/capsule** (with an intelligible label, not code alone);
- PTJ and Tempat Bertugas context;
- Anggaran completeness status;
- automatically calculated total amount;
- number of Butiran Anggaran;
- last edited time;
- compact **Buka** action.

The same OS code may appear in different Aktiviti Kewangan or PTJ/Bidaan contexts: **OS code alone is not an Anggaran identifier**. Opening an OS must carry its actual Anggaran/Bidaan identity and authorised context.

### 3.1 Approved filter hierarchy

Filters appear and constrain results in this exact order:

1. **Aktiviti Kewangan** — primary constraint.
2. **PTJ** — constrained by selected Aktiviti Kewangan and permissions.
3. **Tempat Bertugas** — constrained by upstream selections and permissions.
4. **Status Anggaran** — Belum Mula, Belum Lengkap, Lengkap (and applicable activation state where shown).

Also provide a text search for OS code/name and **Set Semula**. Dropdowns derive from existing database records and the user's actual permissions, not hardcoded sample values. Upstream changes refresh dependent options and reset downstream selections that cease to be valid. Filters narrow visibility; **they never confer access**. PTJ and Tempat Bertugas are separate attributes even if their displayed names happen to match.

The Aktiviti Kewangan capsule remains visible on each OS row even when a single activity is filtered, to avoid ambiguity for multi-activity users.

## 4. Shared Anggaran wizard framework

Every OS uses **one common wizard framework**, not dozens of independent implementations. Shared capabilities:
- OS/context header and summary, including total and completeness;
- Senarai Butiran Anggaran;
- Butiran editor, validation and calculated results;
- Semakan and Log Tindakan;
- persistent draft saving, autosave and failure handling;
- bulk row creation, row duplication, editing and deletion;
- navigation across applicable OS;
- auditing, permissions and concurrency safeguards.

These are functional areas, **not a mandatory four-tab or four-step UI**. Layout can adapt to desktop/mobile. OS-specific input fields, rates, formulas, eligibility and specialised calculations are **deferred** to later work (principally AD-008). OS14000 may be used for end-to-end testing but its illustrative inputs/formulas are not deemed approved financial policy.

A single wizard session may create or edit **multiple Butiran Anggaran**; one source input may produce one or more granular calculated output rows. Users edit source inputs, not arbitrary derived amounts.

## 5. Butiran Anggaran editing and completeness

### 5.1 Entry and productivity
- **Tambah Butiran** creates an editable row/record.
- **Tambah XX Baris** supports batch additions, for example +5, +10, +20, +50, so users need not click fifty times. No universal 50-row total cap; large batches should be processed efficiently.
- **Salin Baris** copies applicable source inputs into a **new Butiran Anggaran identity**; the duplicate remains editable, recalculates derived values and does not automatically become complete. Preserve the applicable Program/ Aktiviti association.
- For OS suited to tabular entry, provide **spreadsheet-style inline editing**, Tab navigation and multi-cell paste, with per-row validation and recalculation. Complex OS can use structured forms within the same shared framework.
- Blank newly inserted rows do not count as completed Butiran and do not contribute to financial totals. They must be handled clearly before formal submission.

### 5.2 Drafts, validation and completion
- **Incomplete Butiran Anggaran can be saved as Draf**, including during a multi-row editing session. Saving a draft never implies completeness.
- Butiran can be considered **Lengkap** only when its applicable inputs and validations pass.
- **Anggaran status is automatic**; there is no separate *Tandakan Lengkap* button:
  - **Belum Mula:** no retained Butiran Anggaran;
  - **Belum Lengkap:** one or more retained Butiran are incomplete/invalid (or there are no qualifying complete records);
  - **Lengkap:** at least one retained Butiran exists and **all retained Butiran are complete and valid**.
- Revalidate after edits, deletion, duplication, activation changes or applicable data/rule changes. A previously complete Anggaran returns to **Belum Lengkap** if an edit makes any retained Butiran invalid.
- Totals derive from applicable saved valid granular records; incomplete/blank drafts must not inflate totals.

### 5.3 Saving and safety
- **Autosave** after a short pause, with visible **Menyimpan / Disimpan / Gagal Disimpan** states and manual **Simpan** fallback.
- Do not navigate away while there are unresolved unsaved changes. OS switching must await a successful save or explicitly block and explain failure.
- Saving and dependent recalculation should be transactional/atomic.
- Protect against concurrent edits overwriting another user's changes without notice.
- Audit attribution includes user, timestamp, operation and relevant changes.

### 5.4 Deletion
- A blank, never-saved row can be removed immediately.
- Deleting a saved draft that has never been formally submitted requires confirmation and audit.
- A previously submitted Butiran may be removed from the **current editable version** only when authorised, but remains in earlier immutable submission snapshots; the subsequent Pindaan comparison records **Dipadam**.
- Recalculate totals and completeness after deletion. Deletion does not rewrite historical snapshots.

## 6. Navigating between OS wizards

Within the **same Bidaan**, users can navigate without repeatedly returning to the Senarai Anggaran page:

1. An **OS selector** at the top allows direct jumps to **all applicable OS**, including those with no Butiran yet (subject to activation/access rules).
2. **Sebelumnya** and **Seterusnya** move sequentially through the applicable OS order.
3. **Kembali ke Senarai Anggaran** returns to the main OS list.

Each switch retains the correct Anggaran ID, Bidaan, Aktiviti Kewangan, PTJ and—where entry was programme-led—the **Program/ Aktiviti association**. For programme-led entry, the navigation scope is the OS associated with that programme, not unrelated OS. A failed autosave blocks switching until resolved; navigation must not silently discard unsaved inputs.

## 7. Audit, history and compatibility

- Keep immutable submitted snapshots and granular Butiran IDs for version comparisons under AD-006 (Ditambah, Dipinda, Dipadam, Tiada Perubahan).
- Apply role and scope checks on both listing and opening/editing a record; UI filters alone are not security controls.
- Maintain Log Tindakan for editing, copying, deletion, activation and relevant status changes.
- Follow Fasa Dibuka/Ditutup and returned-submission editability rules from AD-003/AD-006.
- No **Bakul Bidaan** or duplicate Program/ Aktiviti financial store.

## 8. Deferred implementation details

The following are **not** approved OS-specific specifications in AD-004:
- individual OS fields, rates, formulas, eligibility or source data;
- exact responsive layout of editor cells/panels and optional keyboard shortcuts;
- OS-specific Excel imports and specialised calculations;
- detailed Program/ Aktiviti guided workflow (AD-007);
- review/approval mechanics (AD-009).

**Decision status:** AD-004 is formally finalised and closed. OS-specific specialisation belongs to AD-008, and the detailed Program/ Aktiviti workflow belongs to AD-007. This records architecture decisions, **not implementation or deployment**.
