# Batch 037 — OS24000 Sewaan Umum

**Status:** In progress, explicitly requested by owner on 16 November 2026.
**Baseline:** `7a9cc09691fdf2a84e0e31a53fc48dcde466794b` on dev. Batch 036 merge/verification recorded; local sync/financial acceptance remains unconfirmed.

## Scope and authoritative rules

Implement [Sewaan Umum](../references/OS24000-sewaan-umum-reference.md) for the existing Sewaan identity and five mappings (PPN 010801, Rendah 030219, Awal 030101, Menengah 030301, Asrama 030313). Preserve its unchanged 64-path CSV: Kategori1 → Kategori2 → Kategori3, distinct contracted/non-contracted broadcasting labels and intentionally blank Tanah third category. Extend shared metadata-driven dependent selection by one level; clear all dependent descendants when a parent changes. No separate OS24000 photocopier/rental editor in this batch.

Source inputs: purpose, categories, quantity, manual exact Kadar and manual months. Berkontrak alone enables Kitaran-year start/end dates and calculated inclusive Tempoh (Hari), using the established shared date-day presentation. Tidak Berkontrak dates/duration are inactive and nonempty date injection is rejected. No whole-contract fields or borrowing of photocopier-specific year/month/day duration rules. Bilangan Bulan remains manual in both arrangements. Preview = Bil. Unit × Kadar × Bilangan Bulan, independent of dates; partial drafts are saveable and missing category/rate/quantity/month factors withhold preview.

Preserve shared atomic/audited source saves, revision/Fasa/role/PTJ guards, pagination, read-only states, exact precision and AD-013 title-case/accounting presentation. No migration/seed/reset, rate/reference/catalogue/mapping/permission/schema changes or official outputs/totals/completeness/submission/programme inheritance. Interpret historical 27-column reference by field identity under AD-008's 30-column contract; new 08–10 inactive here. UI refinement checkpoint remains after remaining OS editors; usability defects fixed as encountered.

## Verification

Pending full isolated PostgreSQL/build/formatter/Chromium and representative desktop/mobile empty, populated, partial, read-only, conditional, validation and expanded exact-value review. No filtered-empty state applies. Record actual evidence before merge; local acceptance remains pending.
