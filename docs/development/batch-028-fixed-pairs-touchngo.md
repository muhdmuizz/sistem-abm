# Batch 028 — Fixed Bil. Pasang and Touch ’n Go

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `8ca2c50ce2e026d1e1f404f676c3962d58b39cc6` on dev.

## Scope and authority

Owner correction: Pakaian Seragam **Bil. Pasang = 2**, defaulted and locked; enforce server-side, for the existing Pakaian Seragam/Kasut choices without inventing different category limits. Supersedes prior editable multiplier wording in [uniform reference](../references/OS27000-pakaian-seragam-reference.md). UI fixed output and server-derived source value; reject posted kekerapan. Version v2, retaining read/edit compatibility with valid v1 drafts. Read does not rewrite existing stored inputs; current preview uses two and an explicit save upgrades that row through existing audit/revision guards. No bulk data rewrite or migration.

Next bounded definition: approved [Touch ’n Go](../references/OS27000-bekalan-dan-bahan-lain-touch-n-go-reference.md), canonical variant Bekalan dan Bahan-bahan Lain, existing PPN/010809, Pendidikan Menengah/030304, Pengurusan Asrama/030316 only. Tujuan, Bil. Unit, manual Kadar Bulanan, Bilangan Bulan; exact preview = quantity × monthly rate × months. No frequency/categories/unit/card operations or reference rate; partial drafts allowed. Shared title-case headers, tabular right-aligned grouped/two-decimal money with exact precision retained. No extra month/quantity cap or financial rounding policy.

Retain existing source identities, access/Fasa/session/revision/audit guards, copy/removal and private legacy quarantine. No migration/seed, role/mapping/CSV changes, item proposals, official totals/outputs/completeness, programme inheritance or submission.

## Verification

Pending isolated PostgreSQL regression, production build, formatter/Chromium and representative desktop/mobile state inspection. Record actual tested SHA and limits before merge. Local update pending.
