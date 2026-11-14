# Batch 024 — OS42000 SBK Spreadsheet Definition

**Status:** In progress — owner authorised continuation 15 November 2026.
**Baseline:** `250d57f95706e3caf1eb30b9a8a815d7644700e6` on dev.

## Scope and authority

Continue the shared source-draft spreadsheet with the approved SBK template from [OS42000 SBK](../references/OS42000-utiliti-pendidikan-rendah-reference.md) and the shared four Pukal rows in [OS23000 CSV](../references/OS23000-perhubungan-utiliti-kategori.csv). Preserve distinct Pendidikan Rendah 030228 and Pendidikan Menengah 030310 mappings, original historical wording and AMal exclusion. No source CSV/catalogue reassignment or new financial policy.

Resolve definitions from actual OS plus Bidaan activity/mapping context. Reuse the common grid/services; input columns are authorised Tempat Bertugas, Kategori2 utility and manual monthly KADAR. Kategori1 is fixed Pukal, BILANGAN BULAN fixed 12, exact KADAR × 12 row preview. Partial drafts may be retained without claiming completeness; no fabricated rate/default. Reject user overrides of fixed/derived fields and cross-definition payloads. Keep OS25000 source schema/behaviour compatible.

Preserve live role/workplace/central/Fasa/session/revision/audit safeguards, source-only removal and legacy protection. No migration, seeder, official outputs/totals/completeness, programme inheritance or submission. OS23000's legacy identity reconciliation is not bypassed by enabling SBK.

## Verification

Pending isolated PostgreSQL HTTP/regression tests, production build, common-grid/OS25000 Chromium regression and SBK desktop/mobile state review. Record tested head, actual counts and limitations before merge. No local execution claimed.
