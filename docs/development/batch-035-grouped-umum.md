# Batch 035 — Grouped OS29000 Umum activities

**Status:** In progress; owner explicitly approved following Pakej's expandable activity/component pattern on 15 November 2026.
**Baseline:** `d7038551bd543a847cf2892fe1de64a45e307ef3` on dev. Batch 034 merged/CI verified; local sync/financial acceptance remains unconfirmed.

## Approved scope

Enter workplace, Nama Program/ Aktiviti and Kitaran-year dates once per Umum activity. Expand its spreadsheet to add multiple services, e.g. Penceramah and Makan Minum; each retains its own categories, conditional Nama/Jawatan/Gred, derived speaker classification, quantity, automatic/manual rate and exact preview. The activity shows a draft subtotal; incomplete component previews withhold the subtotal. Preserve the approved 22 reference rows (18 automatic/four manual), shared personnel reference, quantity/rate meanings, accounting-style money and title-case headers.

Reuse Pakej's established grouped source-envelope pattern and shared grouped renderer, now driven by each definition's component-column metadata. One existing Butiran/revision/workplace owns the complete activity and component list with group-local UUIDs; whole-activity pagination, atomic save/audit and live authorisation/Fasa/revision guards remain. Existing Umum v1 sources reopen individually as one-component activities; no matching or automatic merge by activity name. Explicit edits write grouped v2 through existing audited saves. No migration/reset, new financial hierarchy or preservation panel. Flat v1 source requests remain supported consistently with Pakej; the grouped editor writes v2.

Add/copy/remove/undo both components and activities; retain error/pending-save recovery and exact precision. Pakej must retain its existing behaviour. No rate/reference CSV/catalogue/mapping/permission/eligibility/rounding/export-column changes, programme ID linking/inheritance, official financial outputs/totals/completeness or submission. Pakej programme-only entry remains a proposal. [Umum specification](../references/OS29000-umum-reference.md) and [Batch 033](batch-033-grouped-pakej.md) remain authoritative for unchanged rules/patterns.

## Verification

Pending grouped persistence/partial/guard/precision regressions, full isolated PostgreSQL/build/formatter/Chromium and desktop/mobile expanded/collapsed/empty/partial/read-only/error review. Record actual SHA/counts before merge. Local update remains pending.
