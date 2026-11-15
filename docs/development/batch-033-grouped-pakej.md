# Batch 033 — Grouped Pakej activities

**Status:** In progress; authorised by “Let's have a go” after the interactive grouped preview on 15 November 2026.
**Baseline:** `b3785d014671ad512fff9289f3a64916b1cad64a` on dev. Batch 032 merge/CI recorded; its local sync/financial acceptance remains unconfirmed.

## Approved refinement

A direct Pakej activity holds shared Tempat Bertugas, Nama Program/ Aktiviti and Kitaran-year dates once. Expand it to edit multiple component rows with the existing four references, quantities, manual rate bases and independently charged nights/days. Show exact component previews and a draft-only activity subtotal; incomplete components leave the subtotal incomplete. Add/copy/remove component rows and activities with retained draft/error feedback and comfortable desktop/mobile spacing. Title-case headers, tabular/right-aligned comma grouping/two-decimal monetary display and exact source precision remain mandatory.

Technical representation: one grouped activity is one existing Butiran source envelope containing shared fields and an ordered component list with stable group-local UUIDs. This adds no domain entity or alternative hierarchy; the parent source ID/revision, workplace ownership, transactional save, audit and existing 25-source pagination operate on whole activities. Component records remain granular inputs for later output flattening; no actual exporter or official output is introduced. Existing v1 component sources reopen individually as one-component activities; no free-text-name merge or separate preservation UI. Existing flat v1 requests remain readable/saveable through the approved adapter while the grouped editor writes v2. Explicit edits convert that source atomically and audibly through the normal audit. No destructive migration/reset or schema change.

This preview approval does not decide an existing Program/ Aktiviti link: direct grouped entry is implemented here. No automatic programme association/inheritance, headcount/default quantity rules, prescribed rates, role/Fasa changes, new canonical Excel columns or official totals/completeness/submission. Other OS editors remain on their established shared spreadsheet.

## Verification

Pending full isolated PostgreSQL suite, build/formatter/Chromium, grouped persistence/guards/precision/atomicity and desktop/mobile state review. Record actual tested SHA/counts before merge; local update remains pending.
