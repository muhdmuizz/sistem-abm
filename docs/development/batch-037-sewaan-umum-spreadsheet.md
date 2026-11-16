# Batch 037 — OS24000 Sewaan Umum

**Status:** Implemented on feature branch / PR #85; verification blocked by Actions checkout access. Unmerged; local acceptance pending.
**Baseline:** `7a9cc09691fdf2a84e0e31a53fc48dcde466794b` on dev. Batch 036 merge/verification recorded; local sync/financial acceptance remains unconfirmed.

## Scope and authoritative rules

Implement [Sewaan Umum](../references/OS24000-sewaan-umum-reference.md) for the existing Sewaan identity and five mappings (PPN 010801, Rendah 030219, Awal 030101, Menengah 030301, Asrama 030313). Preserve its unchanged 64-path CSV: Kategori1 → Kategori2 → Kategori3, distinct contracted/non-contracted broadcasting labels and intentionally blank Tanah third category. Extend shared metadata-driven dependent selection by one level; clear all dependent descendants when a parent changes. No separate OS24000 photocopier/rental editor in this batch.

Source inputs: purpose, categories, quantity, manual exact Kadar and manual months. Berkontrak alone enables Kitaran-year start/end dates and calculated inclusive Tempoh (Hari), using the established shared date-day presentation. Tidak Berkontrak dates/duration are inactive and nonempty date injection is rejected. No whole-contract fields or borrowing of photocopier-specific year/month/day duration rules. Bilangan Bulan remains manual in both arrangements. Preview = Bil. Unit × Kadar × Bilangan Bulan, independent of dates; partial drafts are saveable and missing category/rate/quantity/month factors withhold preview.

Preserve shared atomic/audited source saves, revision/Fasa/role/PTJ guards, pagination, read-only states, exact precision and AD-013 title-case/accounting presentation. No migration/seed/reset, rate/reference/catalogue/mapping/permission/schema changes or official outputs/totals/completeness/submission/programme inheritance. Interpret historical 27-column reference by field identity under AD-008's 30-column contract; new 08–10 inactive here. UI refinement checkpoint remains after remaining OS editors; usability defects fixed as encountered.

## Verification

Pending full isolated PostgreSQL/build/formatter/Chromium and representative desktop/mobile empty, populated, partial, read-only, conditional, validation and expanded exact-value review. No filtered-empty state applies. Record actual evidence before merge; local acceptance remains pending.

### Verification interruption

Initial application head `5d8827fc3402d46c523bf3b8d5670747c024db23` reached the suite, but all seven new rental tests used an incorrect OS29000 fixture identity. The application correctly rejected that identity. Corrected the test fixture to OS24000 at `f87fac4aa4bc331544f7d9ab9b3adf9e070b98e7`; no application/financial rule changed. CI 43799356712 attempts 1 and 2 then failed during checkout (`Repository not found`, exit 128), before PHP/build/browser execution. This is an infrastructure/access failure, not a test pass. Recording the interruption and starting a fresh workflow event; merge and local update remain pending until full verification succeeds.

### Verification blocked — 16 November 2026

Fresh workflow event [43799773291](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`, job 133076870625, also failed at actions/checkout with `Repository not found` / exit 128. Thus both attempts of run 43799356712 and the fresh event failed before execution. Connected repository reads/writes remain available; the checkout access failure's root cause is not established. Do not change credentials/workflow permissions speculatively or merge unverified work.

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) remains open/unmerged. JavaScript syntax checks passed locally; corrected final PHP/HTTP tests, production build, formatter/browser regression and visual review remain unverified. No new successful test count is claimed. Last verified dev remains Batch 036 (`7a9cc09691fdf2a84e0e31a53fc48dcde466794b`, 503 tests / 4,830 assertions at the recorded application head). No local pull/migration instructions issued for this unmerged batch. Resume by repairing/confirming Actions checkout access, rerunning the corrected head, resolving any test failures and completing desktop/mobile visual review before merge. UI refinement sequencing is unchanged.
