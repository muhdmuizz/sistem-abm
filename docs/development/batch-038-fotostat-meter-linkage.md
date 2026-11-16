# Batch 038 — Fotostat/Riso rental and dependent Bacaan Meter

**Status:** In progress; owner approved 16 November 2026.
**Baseline:** `55c2143430ccbb888f5225bc2ec0935dbf1c0be2`. Batch 037 merged/verified. Owner acknowledged and proceeded; local sync/financial/device acceptance remains unconfirmed.

## Approved scope

OS24000 Sewaan Mesin Fotostat dan Riso and OS29000 Bacaan Meter remain distinct financial identities with their four existing activity mappings. Activate the rental and meter together atomically, preserving permission/Fasa/role/catalogue checks for both; block direct meter activation in UI/backend. Deactivate both together while retaining drafts; reactivation retains source-only drafts. Submitted/finalised and generated-output guards remain.

Each saved rental item owns one linked meter draft. Share workplace, print category, location and current/overall contract dates; rental is authoritative for those fields. Meter copies/per-copy rate/months remain independent and initially blank. Repeated saves update, not duplicate; explicit rental copy creates an independent pair. Generic source services must enforce invariants, including internal copy/delete.

Existing meter drafts stay separate; no guessed matches. Provide explicit linking to a chosen rental, reviewing shared-field replacement while retaining meter financial inputs. Replacing an automatically created meter is allowed only while its financial inputs are blank, with explicit review/confirmation and audit. Populated linked meters cannot be silently replaced. Removing a rental confirms its linked meter values and revision before atomic audited removal; direct removal/copy of linked meter is routed through rental. Stale writes/partial permission failures must roll back the entire operation.

Implement the approved rental spreadsheet (manual Bil. Unit × Kadar × months; independent inclusive current/overall calendar durations) reusing verified meter date machinery without copying money semantics. New database linkage stores identities only, with unique/restrictive references, no financial merge or reset.

## Program/ Aktiviti owner note

OS24000 can also have Program/ Aktiviti as an entry pathway. This does not restrict direct entry. Record in AD-007 and OS24000 references; actual programme association/inheritance remains a separate implementation scope. Existing Pakej-only proposal unchanged.

## Verification

Pending dedicated activation/link/save/copy/delete/deactivation/permission/concurrency tests; full isolated PostgreSQL, build, formatter/Chromium and relevant desktop/mobile state review. No official outputs/totals/completeness/submission or export-schema/rate/reference changes. Record actual execution evidence before merge; migration/local acceptance pending.
