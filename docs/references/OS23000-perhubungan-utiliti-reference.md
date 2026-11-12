# OS23000 — Perhubungan dan Utiliti: category and rate reference

**Owner-confirmed:** 12 November 2026. **Scope:** Reference classification and Kadar mode only; no application or database implementation.

## Category hierarchy

- **Kategori1:** Kategori — `Pukal` or `Bukan Pukal`.
- **Kategori2:** Utiliti — depends on selected Kategori1.
- **Kadar:** **Mandatory user-entered** for each of the seven permitted combinations. No prescribed rate or Gred-based lookup was provided.

| Kategori1 | Kategori2 |
| --- | --- |
| Pukal | Air |
| Pukal | Elektrik |
| Pukal | Telefon/ Faks |
| Pukal | Pembetungan |
| Bukan Pukal | Pos/ Mesin Franking |
| Bukan Pukal | Bil Telefon |
| Bukan Pukal | Lain-lain |

**Structured reference:** [OS23000-perhubungan-utiliti-kategori.csv](OS23000-perhubungan-utiliti-kategori.csv) — seven rows.

Source normalisation: the source's `Bukan   Pukal` (repeated space) is standardised to `Bukan Pukal`, consistent with the other supplied rows. All seven options, including their original slash wording, are retained.

## Identity and mapping boundary

The owner explicitly approved **one OS23000 — Perhubungan dan Utiliti** whose Kategori1 distinguishes **Pukal** from **Bukan Pukal**. The existing master source catalogue also contains an older `23000 — Utiliti Pukal` description; this must **not automatically create a third category or an additional independent wizard**. Existing Kod Aktiviti mappings are **source records**, and their values are not changed by this category-level decision.

The OS catalogue and legacy mapping CSVs are deliberately left unchanged pending a separate reconciliation of their source associations; this does not change or qualify the **two confirmed Kategori1 values**. No inferred Kod Aktiviti, category, eligibility or formula is introduced.

## Boundaries

- Jawatan and Gred are shared personnel attributes where relevant, **not categories**. They are not required for the OS23000 rate lookup specified here.
- No new quantity, free-text description, date, calculation, authorisation or rounding rule is approved by this reference.
- This commit does **not** change ADs, Laravel, PostgreSQL, or the existing master source data.
