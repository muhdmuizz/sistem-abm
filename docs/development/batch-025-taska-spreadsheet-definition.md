# Batch 025 — OS42000 TASKA Spreadsheet Definition

**Status:** In progress; authorised continuation 15 November 2026.
**Baseline:** `258fe6f14262cea08355c5c2f9c5ae4a2a97adf7` on dev.

## Scope and authority

Implement [the owner-approved TASKA source specification](../references/OS42000-subsidi-yuran-taska-reference.md) in the existing shared spreadsheet: authorised Tempat Bertugas; Nama Ibu/ Bapa (parent name, not child name); BIL. ANAK; user-entered budgeted months; automatic read-only UNIT Anak and RM180.00 per child per month from the existing single CSV. Exact row preview = BIL. UNIT × KADAR × BILANGAN BULAN. PPN / Kod Aktiviti 010102 only through its existing mapping; preserve SBK distinction and AMal exclusion.

Do not force one child, invent one-parent-per-row uniqueness, add child names/categories/entitlement rules, alter reference data or rename canonical export headers. Add ordinary text-cell support and definition-specific reference keys to the common renderer, retaining existing OS25000/SBK behaviour. Source quantities remain exact draft decimal text; no new financial minimum, precision, month cap, eligibility or rounding policy is introduced. Parent name and quantities are required for completed budgeting records, but partial drafts remain saveable without completeness certification.

Keep live access/Fasa/session/revision/audit guards, legacy/cross-definition protection, independent row IDs, duplication/removal and numeric alignment. No migration/seed, official outputs/totals/completeness, programme inheritance, submission or exporter implementation.

## Verification

Pending isolated PostgreSQL HTTP/full regression, build, Chromium text/paste/reference/preview/retained-error checks, existing definition regression and desktop/mobile state review. Record actual tested head/counts and boundaries before merge.
