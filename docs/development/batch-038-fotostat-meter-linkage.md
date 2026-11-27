# Batch 038 — Fotostat/Riso rental and dependent Bacaan Meter

**Status:** Merged; local sync/additive migration/financial/device acceptance pending. Owner approved 16 November 2026.
**Baseline:** `55c2143430ccbb888f5225bc2ec0935dbf1c0be2`. Batch 037 merged/verified. Owner acknowledged and proceeded; local sync/financial/device acceptance remains unconfirmed.

## Approved scope

OS24000 Sewaan Mesin Fotostat dan Riso and OS29000 Bacaan Meter remain distinct financial identities with their four existing activity mappings. Activate the rental and meter together atomically, preserving permission/Fasa/role/catalogue checks for both; block direct meter activation in UI/backend. Deactivate both together while retaining drafts; reactivation retains source-only drafts. Submitted/finalised and generated-output guards remain.

Each saved rental item owns one linked meter draft. Share workplace, print category, location and current/overall contract dates; rental is authoritative for those fields. Meter copies/per-copy rate/months remain independent and initially blank. Repeated saves update, not duplicate; explicit rental copy creates an independent pair. Generic source services must enforce invariants, including internal copy/delete.

Existing meter drafts stay separate; no guessed matches. Provide explicit linking to a chosen rental, reviewing shared-field replacement while retaining meter financial inputs. Replacing an automatically created meter is allowed only while its financial inputs are blank, with explicit review/confirmation and audit. Populated linked meters cannot be silently replaced. Removing a rental confirms its linked meter values and revision before atomic audited removal; direct removal/copy of linked meter is routed through rental. Stale writes/partial permission failures must roll back the entire operation.

Implement the approved rental spreadsheet (manual Bil. Unit × Kadar × months; independent inclusive current/overall calendar durations) reusing verified meter date machinery without copying money semantics. New database linkage stores identities only, with unique/restrictive references, no financial merge or reset.

## Program/ Aktiviti owner note

OS24000 can also have Program/ Aktiviti as an entry pathway. This does not restrict direct entry. Record in AD-007 and OS24000 references; actual programme association/inheritance remains a separate implementation scope. Existing Pakej-only proposal unchanged.

## Verification evidence — 16 November 2026

Final application head: `75809c99be402bf0d0805837286557e2519d247a`. [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), [job 133131406062](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723/job/133131406062): **521 tests passed / 5,233 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, exact monetary presentation checks and Chromium passed. Final artifact **14203067321** contains **406 PNGs / 24 Fotostat captures**.

HTTP/PostgreSQL coverage includes atomic pair activation/direct meter denial, permissions on both OS, all four mapping contexts, one child per rental/no-op idempotency, independent meter money, authoritative shared details, independent rental duplication, linked meter copy/delete rejection, deletion confirmation/revisions, retained deactivation/reactivation, explicit chosen-draft linking, populated/zero-valued replacement rejection, stale writes, cross-PTJ denial, phase/role guards and forced audit-failure rollback. The additive linkage migration's restrictive references are retained; canonical storage reversibility test now drops the child table before its parent. Database uniqueness/restrictive FKs protect identities; same-Bidaan/type/workplace constraints are enforced in the transactional service.

Chromium exercises the existing editor regression suite and new rental/meter fixtures at 1440px/390px horizontal endpoints; shared linked fields are read-only, meter financial fields stay editable, exact preview checks include 12,000 × 0.03 × 12 = 4,320.00 and 12,000 × 0.012345 × 7 = 1,036.98. Delete confirmation checks displayed linked meter financial values and the submitted confirmation/revision; intercepted client-save responses are distinct from actual HTTP/database persistence tests.

Earlier runs: `add83b3` / 43815246539 and `1176dd1` / 43815652579 failed the pre-existing manual rollback-order test after the new restrictive child table was introduced. `e3f2b45` / 43815771176 passed 521 tests / 5,233 assertions and build but failed the final browser dialog check because global and local handlers both accepted it; `6ae5920` removes the duplicate listener. Final `75809c9` additionally widens the review table columns and groups/tabular-aligns review counts. These failures are historical, not waived.

Visual review: 13 representative captures from artifact 14202442956 (406 PNGs, 24 Fotostat captures): empty desktop/mobile left; populated desktop/mobile right; linked meter mobile-left/desktop-right; read-only desktop/mobile right; links-empty mobile; activation desktop; deactivation mobile; linking review desktop/mobile. The linking table's cramped inherited column widths were corrected in the final head; refreshed desktop/mobile review captures from final artifact 14203067321 were inspected and the cramped widths are resolved. Other reviewed states are unaffected by that scoped review-only CSS/number-presentation correction. Comfortable insets/title-case headers/accounting monetary display are retained. Horizontal scrolling is expected for the wide spreadsheet; screenshots at endpoints do not certify every middle column.

Uninspected/limits: remaining screenshots, dedicated new loading/pending-save and validation screenshots, physical devices, screen readers, real Google session, local Windows migration and owner financial acceptance. No filtered-empty or hierarchical expanded-row state exists for this flat editor; exact-value expansion is exercised by shared browser coverage. Helper-text density remains for consolidated UI refinement; no UI-complete/production-readiness claim.

## Delivery boundary and local follow-up

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged into `dev` at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. Tested head remains `75809c99be402bf0d0805837286557e2519d247a`; pre-merge verification commit changes documentation only. One additive migration creates `pautan_fotostat`; use ordinary `php artisan migrate` against the verified development database, with no reset/seed. Local sync/migration/financial/device acceptance remains pending; earlier unconfirmed local statuses unchanged. Keep the development server running and refresh assets.

No official calculation outputs, totals, completeness/submission changes, export-schema changes, reference/rate/catalogue/mapping reassignment or programme association implementation. AD-008 remains Draft; Steps 02/03 remain in progress. Complete remaining OS editors before consolidated UI refinement.

## Nama Syarikat correction — 26 November 2026

Owner approved use of the existing NAMA field for rental-company names in OS24000 and linked OS29000. The rental supplies the shared field; old source drafts lacking it remain readable. No database migration or new export column. The correction is in progress; cloud and local verification pending.

Verification: application head `89d565b7a75bc3c0b0110e0404ed787c48dff166` passed [PostgreSQL tests, Vite build and Chromium checks](https://github.com/muhdmuizz/sistem-abm/actions/runs/45777274252). Local acceptance and merge checkpoint pending. No new migration.
