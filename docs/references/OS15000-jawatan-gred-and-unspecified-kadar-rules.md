# OS15000 — Jawatan, Gred and Kadar reference decisions

**Confirmed:** 12 November 2026. **Scope:** Reference-data and input-design decisions only, not Laravel implementation.

## Reference source

See [OS15000-kemudahan-gred-kadar-source.md](OS15000-kemudahan-gred-kadar-source.md) for the **125 original rows** of `Jenis Kemudahan | Gred | Kadar`. This legacy Excel reference frequently expresses eligibility/rates by **numerical SSPA grade level**, with special values such as `C`. No unprovided equivalence is inferred for `C`.

## Universal Jawatan and Gred entry

- Wherever personnel attributes are needed in an Anggaran (OS), users should choose **Jawatan** and the **full SSPA Gred** (e.g. `Pegawai Tadbir dan Diplomatik`, `M9`). This is a reusable input principle across OS, not just OS15000.
- The system interprets the selected grade into the attributes required by the applicable reference (e.g. `M9` has numerical level `9`; use a full grade where the reference explicitly requires the full grade). **Do not ask users to manually select a redundant numerical grade level** merely to mimic a legacy spreadsheet lookup.
- The legacy special grade `C` remains a distinct source classification until its specific mapping or eligibility is supported; do not automatically equate it to any numbered grade.
- Preserve the actual per-Kemudahan and grade/rate references; do not infer additional rates or eligibility from shared numerical levels alone.

## When a Kemudahan has no specified Kadar

**Approved user rule:** For **Bayaran Balik Yuran**, and **any analogous Kemudahan with no specific prescribed Kadar**, the system **must require the user to enter both:**

1. **Description** — required free-text description of the expenditure/claim.
2. **Kadar** — required user-entered rate/amount.

- Missing reference Kadar does **not** mean RM0, a default amount, or permission to submit without these fields.
- This rule applies to any equivalent **no-prescribed-Kadar** case, not just Bayaran Balik Yuran.
- No description or amount is invented for the source's blank Bayaran Balik Yuran entry.
- The user has **not yet established** additional approval/eligibility checks, rate caps, or detailed formulae for these cases; do not introduce them as decisions.

## Status

This record documents the supplied OS15000 reference and user-confirmed input rules. **No changes to existing Architecture Decisions, source salary catalogue, database, or Laravel application are implied.**
