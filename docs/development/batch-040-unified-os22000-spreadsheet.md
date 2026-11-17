# Batch 040 — Unified OS22000 spreadsheet

**Status:** In progress; owner approved 17 November 2026.
**Baseline:** `141cc687c45a5fd3d3e8077ed1d8f491fbb174b2`. Batch 039 merged/verified at `874f7bf923fb9179231c38bae524e46b71f141b5` (528 tests / 5,330 assertions). Its local sync/financial/device acceptance and Batch 038 migration remain unconfirmed. Owner requested separate Server and Updates terminals; keep the existing local server running for routine updates.

## Authority and bounded implementation

Follow [OS22000 unified decisions](../references/OS22000-unified-kategori-decisions.md), the unchanged 126-row rate CSV and shared Jawatan/full SSPA reference/universal numerical-grade interpretation. One existing canonical OS22000 identity, two Kategori1 paths independent of activity code: Pertukaran/ Penempatan with family/claim/Lokasi Pertukaran/personnel inputs; Pengangkutan Barang with manual Tujuan and Kadar only. Both previews = Kadar × 1. No quantity/date/distance multiplier, rounding or invented eligibility. Missing grade 11 and special C/VU7 mappings stay unresolved; missing grade matches cannot accept arbitrary manual rates. Only supplied blank rate entries allow manual Kadar. Do not fabricate Sabah freight rows absent from the supplied CSV. Partial drafts remain saveable; official completeness is separate.

Reconcile the obsolete `22000 / Pengangkutan Barang-barang` catalogue row into the retained `Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula` identity. Retain all seven existing code mappings and original-source descriptions; no new activity code. Catalogue becomes 31 identities (29 eligible/two excluded), mappings stay 105 (103 eligible). The two canonical CSV mapping descriptions change, not their original labels/codes. Seeder remains idempotent.

Targeted development migration follows the owner-confirmed no-real-data cleanup direction: delete only estimates/sources/generated test outputs owned by the obsolete OS22000 identity; retain main-identity/unrelated data and increment affected Bidaan revisions. Retain mapping IDs when repointing, deduplicate an existing same mapping, remove obsolete permission overrides without promoting them to the retained OS, and remove the obsolete identity. Non-draft Bidaan stops and rolls back reconciliation. No financial preservation interface or broad reset. Rollback does not fabricate deleted development records or restore the obsolete identity.

Use shared audited atomic source editor/guards and metadata-driven conditional controls. Inactive fields must be cleared in the client and rejected server-side. Preserve exact decimal values and shared tabular/accounting monetary presentation; title-case headers including Lokasi Pertukaran. This display label does not rename the canonical Excel header. No source-rate changes, official outputs/totals/completeness/submission, programme inheritance or export implementation. AD-008 remains Draft; Steps 02/03 in progress. Remaining editors precede consolidated UI refinement.

## Verification

Pending dedicated PostgreSQL/HTTP source/migration/seeder tests, isolated full suite/build/exact-money/Chromium and desktop/mobile state review. Record final tested head/run/counts and review limitations before merge. Actual merge checkpoint and local migration/seeder/financial/device acceptance pending.
