# Batch 025 — OS42000 TASKA Spreadsheet Definition

**Status:** Verification passed; merge pending. Authorised continuation 15 November 2026.
**Baseline:** `258fe6f14262cea08355c5c2f9c5ae4a2a97adf7` on dev.

## Scope and authority

Implement [the owner-approved TASKA source specification](../references/OS42000-subsidi-yuran-taska-reference.md) in the existing shared spreadsheet: authorised Tempat Bertugas; Nama Ibu/ Bapa (parent name, not child name); BIL. ANAK; user-entered budgeted months; automatic read-only UNIT Anak and RM180.00 per child per month from the existing single CSV. Exact row preview = BIL. UNIT × KADAR × BILANGAN BULAN. PPN / Kod Aktiviti 010102 only through its existing mapping; preserve SBK distinction and AMal exclusion.

Do not force one child, invent one-parent-per-row uniqueness, add child names/categories/entitlement rules, alter reference data or rename canonical export headers. Add ordinary text-cell support and definition-specific reference keys to the common renderer, retaining existing OS25000/SBK behaviour. Source quantities remain exact draft decimal text; no new financial minimum, precision, month cap, eligibility or rounding policy is introduced. Parent name and quantities are required for completed budgeting records, but partial drafts remain saveable without completeness certification.

Keep live access/Fasa/session/revision/audit guards, legacy/cross-definition protection, independent row IDs, duplication/removal and numeric alignment. No migration/seed, official outputs/totals/completeness, programme inheritance, submission or exporter implementation.

## Verification — passed 15 November 2026

Tested application head `9c4bce154c684ec232416e9e4d99572ddc64cee2`: [CI 43653498708](https://github.com/muhdmuizz/sistem-abm/actions/runs/43653498708), **438 tests / 3449 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`; production build and Chromium regression passed.

Seven new PostgreSQL HTTP tests cover parent-name fields, the exact single reference, multi-child/repeated-parent independent rows, save/reopen/edit/no-op, exact large values, PPN/010102 and variant boundaries, fixed/derived input tampering, malformed inputs, partial/blank drafts, legacy/cross-definition privacy, stale/Fasa/read-only guards and audited removal. Existing common-grid regression covers broader scope/session/audit rollback boundaries.

Browser checks cover ordinary text entry, Tab to child quantity, fixed Anak/180 display, source-only TSV paste with explicit destination focus, exact multi-factor and signed-factor draft arithmetic, independent local duplication, literal HTML-like name handling and retained field errors. Static fixture save failures use intercepted responses; real successful persistence uses PostgreSQL HTTP tests. Preview support for signed draft factors is technical consistency, not a financial eligibility decision.

The early all-pass run 43652809363 at f6a6674ccb01d4e8c4a2d2074304beeb9dbabd96 provided 12 new TASKA captures, all visually reviewed (empty/populated/partial/legacy/read-only/validation at 1440/390). Final artifact `14136764322` contains 141 PNGs; final populated and validation desktop/mobile captures were rechecked after the arithmetic/test-focus correction. Other final captures were generated/browser-checked but not individually re-reviewed after that non-layout change. Detailed columns retain internal horizontal scrolling on narrow workspaces; far-right scrolled positions were not separately visually captured. No new filtered/expanded/asynchronous interface is introduced. No physical-device, Windows, full-design or production acceptance is claimed.

No migration/seed, reference/mapping/permission changes, official output/totals/completeness or programme inheritance. Draft previews do not certify entitlement or submission readiness.

## Local follow-through — pending

Keep the running server. From a clean working tree switch to dev, pull --ff-only and run php artisan optimize:clear, then Ctrl+F5. No migration, seeder, npm build or restart required.

Open an authorised active OS42000 — Subsidi Yuran TASKA under PPN, then Buka Jadual Butiran. Check Nama Ibu/ Bapa (not child name), BIL. ANAK, user-entered months, UNIT Anak and read-only RM180/month/anak. Example: 2 children × RM180 × 12 months previews RM4320. Check save/reopen/edit and duplication/removal; repeated parent names and multiple children remain allowed. Existing SBK/OS25000 grids should still open normally. Live role/Fasa/central access rules continue to apply.

## Remaining work

Official financial output/totals/completeness, programme association/inheritance and remaining OS definitions stay pending. Exact previews are source-draft feedback, not entitlement/eligibility or submission readiness. Keep paused OS14000 detailed work and other unresolved policy gaps isolated.
