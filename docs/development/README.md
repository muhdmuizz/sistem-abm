# Sistem ABM — Development Progress Register

**Updated:** 18 November 2026 (Asia/Kuala_Lumpur).  
**Repository / development branch:** `muhdmuizz/sistem-abm` / `dev`.  
**Latest verified application-code checkpoint:** `77219f10ccedf872c13e3ec4b087469d0de498b0` (Batch048, PR107; tested head `9eebfba5da255e5908fa3368221e91d74d62d0e7`; local sync pending). Documentation close-out does not change application code.
**Current application verification:** 586 tests passed, 7484 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`.  
**Local state:** Batch048 merged; local sync/financial/device acceptance pending; no new migration/seed. Batch047 merged; local sync/financial/device acceptance pending; no new migration/seed. Batch 046 merged; local sync/financial/device acceptance pending; no new migration/seed. Batch 045 merged; local sync/financial/device acceptance pending; no migration/seed. Batch 044 merged; local sync/financial/device acceptance pending; no migration/seed. Batch 043 withdrawn before PR/merge; OS26000 purpose retained. Batch 042 merged; local sync/targeted migration/reference import/financial/device acceptance pending. Batch 041 merged; local sync/financial/device acceptance pending; no new migration/seed. Batch 040 merged; local sync/targeted migration/reference import/financial/device acceptance pending. Batch 039 merged; local sync/financial/device acceptance pending, no new migration/seed. Batch 038 merged; additive migration/local sync/financial/device acceptance pending, no reset/seed. Batch 037 merged; local sync/financial acceptance pending, no migration/seed. Batch 036 merged; local sync/financial acceptance pending, no migration/seed. Batch 035 merged; local update/financial acceptance pending, no migration/seed. Batch 034 merged; local update/financial acceptance pending, no migration/seed. Batch 033 merged; local update/financial acceptance pending, no migration/seed. Batch 032 merged; local update/financial acceptance pending, no migration/seed. Batch 031 targeted cleanup migration and supplied local synchronisation user-confirmed by “Clean, proceed.”; detailed financial/role/device checks remain unverified. Preservation of obsolete Pukal test budgets is superseded by owner-authorised cleanup. Batch 030 merged; local update/selector/card checks pending. Batches 028/029 local confirmation remains unreceived. Batch 029 merged; local update/checks pending. The requested Jawatan restriction is implemented; Batch 028 local confirmation remains unreceived. Batch 027 synchronisation/cache clear user-confirmed by “Clean”; Bil. Pasang defect corrected in Batch 028, whose local acceptance remains pending. Batch 026 synchronisation/cache clear user-confirmed by “Everything else is clean.”; supplied TASKA screenshot confirms grouped/two-decimal monetary presentation at that viewport. Header-casing inconsistency is corrected by Batch 027; its sync/cache clear are user-confirmed; broader functional/role/device checks remain unverified locally; no migration/seed. Batch 025 local synchronisation/cache clear user-confirmed by “The previous is clean.”; detailed TASKA functional checks remain unverified locally; no migration/seed. Batch 024 synchronisation/cache clear user-confirmed by “Clean, continue.”; detailed SBK functional checks remain unverified locally; no migration/seed. Batch 023 synchronisation/cache clear user-confirmed by “Clean, proceed.”; detailed OS25000 functional checks remain unverified locally; no migration/seed. Batch 022 local update and requested UI checks user-confirmed by the report that the UI is clean; visual design refinement remains future work. No migration/seed. Batch 021 local synchronisation and requested programme create/edit/objective/headcount/date smoke checks user-confirmed; no migration/seed. Batch 020 migration/RPM reference import and existing-page smoke checks user-confirmed; programme interface now added by Batch 021. Batch 019 synchronisation/existing-page smoke check user-confirmed; direct copy service execution unverified; no migration/new copy UI. Batch 018 synchronisation and requested Log Tindakan inspection user-confirmed; no migration. Batch 017 synchronisation/active-OS opening user-confirmed; other navigation checks remain unverified. Batch 016 synchronisation user-confirmed; direct multi-row service execution remains unverified; no migration or new UI. Batch 015 synchronisation, Fasa correction migration and requested Fasa/activation/persistence/feedback/spacing checks are user-confirmed. Batch 014 synchronisation and requested existing-page smoke checks are user-confirmed; no migration was required. Batch 012 storage and Batch 013 status migrations are user-confirmed as locally applied; actual financial service operations remain unverified locally. Batch 011 local synchronisation/migration and requested list/year-only form inspection are user-confirmed; actual phase mutations remain unverified locally. The user identified an empty-state padding defect, tracked below. Batches 001–007 remain user-confirmed. After Batch 008 OAuth setup and PR #12 account-registration instructions, the user reports successful entry into the local web app using the intended MyGovUC identity. Initial setup, real login, four reference-page totals and logout/re-login are user-confirmed; narrow-window menu/cards/overflow are also user-confirmed; actual-device/pagination/session/manual acceptance remains pending. Batch 009 local migration, operator/add-user form inspection and own all-device logout/re-login are now user-confirmed. Other administration mutation/device checks remain unverified locally. Batch 010 local synchronisation and requested editor inspection are user-confirmed; actual local permission mutations remain unverified. The cloud agent did not inspect Windows or query its audit records directly.

**Development context (owner-confirmed 15 November 2026):** No real business data exists yet. Do not add legacy-data preservation interfaces by default for superseded development entries; perform the authorised targeted cleanup. This is not authority to reset unrelated data.

This is the implementation/execution register. [The architecture README](../architecture/README.md) remains the single master AD index; design approval is not implementation completion. Batch numbers are bounded delivery checkpoints, not replacements for the previously documented six workstreams.

## Completed implementation and evidence

| Batch / correction | Delivered scope | Merged PR / application checkpoint | Verification at that checkpoint | Local status |
| --- | --- | --- | --- | --- |
| [001](batch-001-foundation.md) | PTJ, Aktiviti Kewangan and Kitaran models/migrations; integrity tests | [#1](https://github.com/muhdmuizz/sistem-abm/pull/1) / `893c0d0`; subsequent test-isolation commit `4388220` | Successful local validation reported in the supplied handover; foundational cases also pass in later CI. No initial dedicated CI count is claimed. | Completed per supplied handover |
| [002](batch-002-organisational-references.md) | Kod Aktiviti and approved organisational reference import | [#2](https://github.com/muhdmuizz/sistem-abm/pull/2) / `89f9cbf` | Handover reports local 16 tests / 47 assertions; original raw console output is not independently recovered here. Later CI covers these tests. | Completed per supplied handover |
| [003](batch-003-workplaces.md) | 579 workplaces and organisational PTJ relationships | [#3](https://github.com/muhdmuizz/sistem-abm/pull/3) / `3541052` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43367136902): 25 tests / 274 assertions | User confirmed “Completed without errors” |
| [004](batch-004-os-references.md) | 33 OS catalogue entries and 105 source mappings | [#4](https://github.com/muhdmuizz/sistem-abm/pull/4) / `15e5898` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43368361719): 39 tests / 631 assertions | Superseded by corrected checkpoint below before local sync |
| TASKA correction to 004 | Assign `010102` under PPN; upgrade prior blank mapping in place | [#5](https://github.com/muhdmuizz/sistem-abm/pull/5) / `4352dc4` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43368901971): 41 tests / 641 assertions | User confirmed “Done” after revised synchronisation instructions |
| [005](batch-005-ptj-permissions.md) | PTJ permission storage/resolution; 15 approved category/activity defaults | [#6](https://github.com/muhdmuizz/sistem-abm/pull/6) / `a8a8136` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43401547856): 59 tests / 711 assertions | User confirmed “Clean” |
| [006](batch-006-user-role-scopes.md) | User organisation and six-role assignment/scope foundation | [#7](https://github.com/muhdmuizz/sistem-abm/pull/7) / `f498f51` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43403240690): 81 tests / 781 assertions | User confirmed “Clean” |
| [007](batch-007-audit-foundation.md) | Transactional, attributable, append-only audit primitive | [#8](https://github.com/muhdmuizz/sistem-abm/pull/8) / `54ae2f7` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43405028148): 108 tests / 837 assertions | User confirmed “Clean” |
| [008](batch-008-first-interface.md) | Public Google login, responsive shell and read-only references; first local operator bootstrap | [#10](https://github.com/muhdmuizz/sistem-abm/pull/10), [#12](https://github.com/muhdmuizz/sistem-abm/pull/12) / `f6e785a` | 134 tests / 1002 assertions at `5afed49`; build/browser checks | User confirmed login, totals, logout/re-login and narrow-window layout; remaining manual checks tracked |
| [009](batch-009-user-administration.md) | Audited user/status/role administration, stale-write protection and all-device revocation | [#17](https://github.com/muhdmuizz/sistem-abm/pull/17) / `2247627` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43427826563): 153 tests / 1143 assertions at `b8ea428`; build/browser checks and visual review | User-confirmed local migration, operator/add-user inspection and own all-device logout/re-login; other mutations/devices unverified locally |
| [010](batch-010-central-permissions.md) | Audited category/PTJ activity/OS permission editors, inheritance/reset and stale-state protection | [#20](https://github.com/muhdmuizz/sistem-abm/pull/20) / `d514ae5` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43432337237): 168 tests / 1571 assertions at `32ba5ef`; build/browser checks and visual review | Local synchronisation/requested inspection user-confirmed; actual local permission mutations unverified |
| [011](batch-011-kitaran-fasa-administration.md) | Year-only Kitaran, Fasa dates/scopes, Pindaan numbering and audited opening actions | [#23](https://github.com/muhdmuizz/sistem-abm/pull/23) / `6c6e777` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43523750417): 187 tests / 1730 assertions at `4314865`; build/browser checks and desktop/mobile visual review | Local synchronisation/migration and requested inspection user-confirmed; phase mutations unverified; empty-state padding defect tracked |
| [012](batch-012-canonical-financial-storage.md) | Canonical financial storage, same-PTJ workplace integrity and traceable source/output records | [#26](https://github.com/muhdmuizz/sistem-abm/pull/26) / `0ff960d` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43531677184): 206 tests / 1858 assertions at `6fd0a01`; build/browser regression | Local synchronisation/migration user-confirmed; storage only |
| [013](batch-013-audited-financial-activation.md) | Audited initial Bidaan/OS activation services, live scope/Fasa/session checks and stale-revision protection | [#28](https://github.com/muhdmuizz/sistem-abm/pull/28) / `bf69e80` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43535741061): 239 tests / 2005 assertions at `c91a712`; build/browser regression | Local synchronisation/migration user-confirmed; financial operations unverified |
| [014](batch-014-audited-butiran-drafts.md) | Audited source draft creation/editing, shared financial guards, old/new workplace checks and three-level stale revisions | [#30](https://github.com/muhdmuizz/sistem-abm/pull/30) / `12fc145` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43547530349): 272 tests / 2178 assertions at `3abec85`; build/browser regression | Local synchronisation/existing-page smoke checks user-confirmed; financial operations unverified |
| [015](batch-015-bida-peruntukan-workspace.md) | Authorised Bida Peruntukan context/activity/OS catalogue and audited activation UI | [#32](https://github.com/muhdmuizz/sistem-abm/pull/32) / `96e3507` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43552003016): 288 tests / 2293 assertions at `daf17e1`; build/Chromium and 16 desktop/mobile screenshots reviewed | Requested local inspection/activation/persistence user-confirmed; later Fasa correction migration also user-confirmed |
| [016](batch-016-atomic-multirow-drafts.md) | Atomic multi-row source draft saves with stable IDs, indexed validation and complete rollback | [#37](https://github.com/muhdmuizz/sistem-abm/pull/37) / `47f2230` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43557005407): 302 tests / 2403 assertions at `781338b`; build/existing browser regression | Local synchronisation user-confirmed; direct service execution unverified |
| [017](batch-017-anggaran-read-workspace.md) | Shared active-OS read workspace, workplace-scoped saved-row metadata/counts and same-Bidaan navigation | [#39](https://github.com/muhdmuizz/sistem-abm/pull/39) / `fab380c` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43559544013): 315 tests / 2505 assertions at `7e52730`; build/Chromium and twelve reviewed screenshots | Requested local update/inspection user-confirmed; no migration |
| [018](batch-018-scoped-anggaran-history.md) | Read-only scoped OS/source Log Tindakan with independent audit pagination | [#41](https://github.com/muhdmuizz/sistem-abm/pull/41) / `30713fd` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43561176414): 325 tests / 2596 assertions at `48b245a`; build/Chromium and sixteen reviewed screenshots | Local synchronisation/inspection pending; no migration |
| [019](batch-019-audited-draft-duplication.md) | Internal independent draft copy with live guards, private lineage audit and scoped event integration | [#43](https://github.com/muhdmuizz/sistem-abm/pull/43) / `c2ab85c` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43562510603): 342 tests / 2722 assertions at `ade9a9a`; build/Chromium and new event screenshots reviewed | Local update/existing-page smoke check user-confirmed; direct copy execution unverified |
| [020](batch-020-program-aktiviti-foundation.md) | Programme metadata/RPM hierarchy, scoped audited drafts, budget-year and headcount validation | [#46](https://github.com/muhdmuizz/sistem-abm/pull/46) / `97733ab` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43579320487): 393 tests / 3003 assertions at `99bdfa6`; build/existing browser regression | Local migration/RpmReferenceSeeder user-confirmed; programme interface now added by Batch 021 |
| [021](batch-021-program-aktiviti-interface.md) | Scoped programme list and four-section audited draft form; dependent objectives, dates and headcounts | [#49](https://github.com/muhdmuizz/sistem-abm/pull/49) / `981b766` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43583261810): 408 tests / 3129 assertions at `e9da548`; build/Chromium; desktop/mobile state review | Local update and requested programme checks user-confirmed; no migration/seed |
| [022](batch-022-shared-ui-refinement.md) | Scrollable sidebar/drawer, compact login and shared spacing/control/empty/read-only refinement | [#51](https://github.com/muhdmuizz/sistem-abm/pull/51) / `f8d0cbf` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43585733643): 410 tests / 3141 assertions at `9422886`; build/Chromium and representative visual review | Requested local checks user-confirmed; broader design refinement remains future work; no migration/seed |
| [023](batch-023-spreadsheet-anggaran-editor.md) | Shared inline source-draft grid; OS25000 definition; audited removal and contract/readiness reconciliation | [#54](https://github.com/muhdmuizz/sistem-abm/pull/54) / `683629b` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43632464587): 423 tests / 3245 assertions at `11255c0`; build/Chromium and 11 new reviewed screenshots | Synchronisation/cache clear user-confirmed; detailed functional checks unverified; no migration/seed |
| [024](batch-024-sbk-spreadsheet-definition.md) | Context-aware SBK template for two education activities; manual monthly rate/fixed 12 and numeric alignment | [#56](https://github.com/muhdmuizz/sistem-abm/pull/56) / `68a8f46` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43635684953): 431 tests / 3354 assertions at `5ebdce2`; build/Chromium and visual correction review | Synchronisation/cache clear user-confirmed; detailed functional checks unverified; no migration/seed |
| [025](batch-025-taska-spreadsheet-definition.md) | TASKA parent/child/month source definition under PPN/010102; shared text and reference-key cells | [#58](https://github.com/muhdmuizz/sistem-abm/pull/58) / `0eb73bc` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43653498708): 438 tests / 3449 assertions at `9c4bce1`; build/Chromium and desktop/mobile state review | Synchronisation/cache clear user-confirmed; detailed functional checks unverified; no migration/seed |
| [026](batch-026-maintenance-money.md) | OS28000 dependent/mixed-rate sources; shared grouped/two-decimal money display preserving exact values | [#61](https://github.com/muhdmuizz/sistem-abm/pull/61) / `0343ac9` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43662888155): 445 tests / 3544 assertions at `d9f2742`; build/formatter/Chromium and representative visual review | Sync user-confirmed; TASKA money screenshot reviewed; header casing correction queued; broader checks unverified; no migration/seed |
| [027](batch-027-uniform-headers.md) | Pakaian Seragam source grid and title-case headers across five definitions | [#64](https://github.com/muhdmuizz/sistem-abm/pull/64) / `dffc2c4` | 452 tests / 3626 assertions at `bea2bed`; build/formatter/Chromium and representative desktop/mobile review | Sync/cache clear user-confirmed; broader functional checks unverified |
| [028](batch-028-fixed-pairs-touchngo.md) | Fixed two pairs and OS27000 Touch ’n Go monthly source grid | [#66](https://github.com/muhdmuizz/sistem-abm/pull/66) / `496dbfb` | 458 tests / 3701 assertions at `c780d2d`; build/formatter/Chromium and representative desktop/mobile review | Local acceptance pending; no migration/seed |
| [029](batch-029-uniform-jobs-utilities.md) | Applicable uniform Jawatan and seven-path OS23000 utility source grid | [#68](https://github.com/muhdmuizz/sistem-abm/pull/68) / `4c5a112` | 464 tests / 3802 assertions at `f7cdd25`; build/formatter/Chromium and representative desktop/mobile review | Local acceptance pending; no migration/seed |
| [030](batch-030-ptj-selector-utility-card.md) | Searchable PTJ and one OS23000 preparation card, preserved old-record access | [#70](https://github.com/muhdmuizz/sistem-abm/pull/70) / `4458d0e` | 468 tests / 3842 assertions at `24e40b2`; build/formatter/Chromium and 15 new state captures reviewed | Local acceptance pending; no migration/seed |
| [031](batch-031-utility-cleanup-mystep.md) | Remove obsolete utility test budgets/preservation UI; shared MySTEP source grid | [#72](https://github.com/muhdmuizz/sistem-abm/pull/72) / `8ac02d1` | 474 tests / 3925 assertions at `005776c`; build/formatter/Chromium and representative state review | Supplied local synchronisation/targeted migration user-confirmed; detailed financial/role/device checks unverified |
| [032](batch-032-pakej-spreadsheet.md) | Four Pakej components with manual rate bases and independent charged nights/days | [#74](https://github.com/muhdmuizz/sistem-abm/pull/74) / `3494a31` | 480 tests / 4030 assertions at `5dfb64a`; build/formatter/Chromium and 11 representative state captures reviewed | Local update/financial acceptance pending; no migration/seed |
| [033](batch-033-grouped-pakej.md) | Expandable Pakej activities with shared fields, component spreadsheets and draft subtotals | [#76](https://github.com/muhdmuizz/sistem-abm/pull/76) / `a9c51a2` | 485 tests / 4137 assertions at `54ddebf`; build/formatter/Chromium and 15 representative state captures reviewed | Local update/financial acceptance pending; no migration/seed |
| [034](batch-034-umum-spreadsheet.md) | Umum service/personnel dependencies, derived classification and exact mixed-rate previews | [#79](https://github.com/muhdmuizz/sistem-abm/pull/79) / `b77a97d` | 491 tests / 4570 assertions at `44a27bf`; build/formatter/Chromium and 12 representative state captures reviewed | Local update/financial acceptance pending; no migration/seed |
| [035](batch-035-grouped-umum.md) | Expandable Umum activities with shared fields and independent service components/subtotals | [#81](https://github.com/muhdmuizz/sistem-abm/pull/81) / `c5bb009` | 496 tests / 4688 assertions at `27791b5`; build/formatter/Chromium and 17 state captures reviewed | Local sync/financial acceptance pending; no migration/seed |
| [036](batch-036-bacaan-meter-spreadsheet.md) | Bacaan Meter monthly-copy grid and independent calendar contract periods | [#83](https://github.com/muhdmuizz/sistem-abm/pull/83) / `0aba6d0` | 503 tests / 4830 assertions at `ef1b7c2`; build/formatter/Chromium and 20 state captures reviewed | Local sync/financial acceptance pending; no migration/seed |
| [037](batch-037-sewaan-umum-spreadsheet.md) | Sewaan Umum three-level categories, conditional dates and exact manual-month previews | [#85](https://github.com/muhdmuizz/sistem-abm/pull/85) / `d95b860` | 510 tests / 5119 assertions at `29932d8`; build/formatter/Chromium and 17 captures reviewed; checkout recovered | Local sync/financial acceptance pending; no migration/seed |
| [038](batch-038-fotostat-meter-linkage.md) | Fotostat/Riso rental grid and dependent meter activation/linkage/sync/deletion | [#87](https://github.com/muhdmuizz/sistem-abm/pull/87) / `9f294dc` | 521 tests / 5233 assertions at `75809c9`; build/exact-money/Chromium passed; 13 earlier + 2 refreshed review captures inspected | Local sync/additive migration/financial acceptance pending; no reset/seed |
| [039](batch-039-pertukaran-spreadsheet.md) | OS21000 Pertukaran eight paths, exact component-specific previews and matched rates | [#89](https://github.com/muhdmuizz/sistem-abm/pull/89) / `874f7bf` | 528 tests / 5330 assertions at `1c150db`; build/exact-money/Chromium passed; 14 captures reviewed | Local sync/financial acceptance pending; no new migration/seed; Batch 038 migration unconfirmed |
| [040](batch-040-unified-os22000-spreadsheet.md) | Unified OS22000 two-path grid and targeted catalogue reconciliation | [#91](https://github.com/muhdmuizz/sistem-abm/pull/91) / `a45ec93` | 537 tests / 5435 assertions at `457cc94`; build/exact-money/Chromium passed; 14 captures reviewed | Local migration/reference import/financial acceptance pending; no broad reset |

Tests/assertions above are full-suite totals at each tested revision, not the number added by a batch. Merged PR metadata and the successful CI log summaries were rechecked during this reconciliation. Batches 001–002 local evidence comes from the user-supplied 13 November handover; subsequent local confirmations come from this conversation. Neither is presented as direct cloud access to Windows.

## Current reference inventory

| Reference | Current count / state |
| --- | --- |
| PTJ | 69: 1 JPNT, 8 PPD, 60 SMK PTJ |
| Aktiviti Kewangan | 5 |
| Kod Aktiviti | 38 after TASKA correction; original Batch 002 imported 37 |
| Workplace references | 579: 15 JPNT organisational entries + 56 PPD Sektor + 448 non-PTJ schools + 60 SMK PTJ self-workplaces |
| OS catalogue | 33: 31 eligible references + 2 exclusions (POL and AMal) |
| Source OS mappings | 105: 103 non-excluded + 2 excluded |
| TASKA | OS42000, Kod Aktiviti `010102`, PPN |
| Permission defaults seeded | 15 category/activity defaults only |
| OS permission choices / individual PTJ exceptions | Not selected or seeded; do not guess |
| Real user activation / role assignments | Foundation seeders create no real users/roles. First local operator registration and Google login are now user-confirmed after the PR #12 bootstrap instructions. |

These are current source/import counts, not hardcoded application limits. Institutional codes for non-PTJ schools remain unverified and must not be fabricated.

## What is implemented versus still pending

| Area | Actual implementation state |
| --- | --- |
| Database and reference foundations | Implemented and tested within Batches 001–004 |
| Kitaran / Fasa | Batch 011 year-only creation, bounded dates/scopes, explicit audited opening actions and stale-write protection implemented/tested/merged; requested local inspection user-confirmed; actual phase mutations unverified and empty-state padding corrected by Batch 022; local refinement inspection pending. Archive/delete and financial integration remain pending |
| PTJ permissions | Batch 005 storage/resolution plus Batch 010 audited category/individual activity/OS editors, reset and stale-write checks; merged; requested local inspection user-confirmed, actual local mutations unverified. No OS defaults or PTJ exceptions chosen/seeded |
| User/role scopes | Exact six-role storage/evaluation plus Batch 009 audited registration/status/scope administration; code merged; requested local smoke checks user-confirmed; other mutations unverified locally |
| Audit | Internal atomic audit primitive and model/PostgreSQL history protections; Batch 018 adds scoped Anggaran activation/draft metadata history; global audit/security viewer and automatic coverage of all future business operations remain pending |
| Audited access administration | Batch 009 user/status/role changes and all-device revocation implemented/tested; requested local smoke checks user-confirmed; other manual mutations/devices unverified. Batch 010 adds central OS-permission editing (requested local inspection user-confirmed); security email/notification delivery remains pending |
| ABM web interface | Batch 008 implements sign-in, shell and protected read-only references; code merged; first local Google login user-confirmed, remaining manual checks pending. Batch 009 adds the editable user/role interface (requested local smoke checks user-confirmed); Batch 010 adds the central permission interface (requested local inspection user-confirmed); Batch 015 adds the preparation context/catalogue/activation interface (requested local acceptance user-confirmed); Batch 017 adds the read-only OS workspace/navigation and Batch 018 adds scoped Log Tindakan (requested local update/opening/log inspection user-confirmed); source editing and later financial workflows remain pending. |
| Google sign-in and approved session policy | Batch 008 implements code flow, registered identity checks and idle/absolute expiry; first real local Google sign-in user-confirmed; Batch 009 adds revision-enforced all-device revocation. Own all-device logout/re-login is user-confirmed; other local administration/device/session acceptance remains pending. |
| Program/ Aktiviti | Batch 020 backend/local import user-confirmed. Batch 021 adds the scoped list and four-section manual-save draft form; 408 tests / 3129 assertions plus build/browser and visual review passed. Local UI acceptance pending. OS selection/association, inheritance/recalculation and financial completeness remain deferred |
| Financial persistence and workflows | Batch 012 adds Bidaan/Anggaran/Butiran source/output storage and structural integrity. Batch 013 adds audited initial activation services and the four approved Bidaan status values; no transition/snapshot workflow. Batch 014 adds audited backend draft source creation/editing; calculated-source changes remain gated until approved recalculation. Batch 015 exposes the context/catalogue/activation workspace; Batch 016 adds atomic multi-row draft saves; Batch 017 adds the scoped read shell/navigation; Batch 018 adds scoped action metadata. Batches 020–021 add programme metadata storage/UI. Batch 023 adds the shared source editor with OS25000 definition and audited removal; Batch 024 adds SBK across the two approved education activities; Batch 025 adds TASKA under PPN/010102; Batch 026 adds OS28000 and shared exact-safe monetary presentation. Other OS definitions, programme association/inheritance, official calculations/completeness/totals, submissions/reviews/snapshots and exports remain pending; Steps 02–03 are in progress |
| Production/security release | Not certified; runtime/DDL privilege separation and other AD-012/security-baseline gates remain outstanding |

The foundation suite does not certify complete security controls, financial outcomes, deployed UI or production readiness. AD-008 remains Draft; catalogue import does not finalise OS financial specifications.

## Batch 008 — first local login confirmed; remaining manual acceptance pending

The user's “Let's continue” instruction after reconciliation is being executed as the next visible-interface deliverable: Google sign-in preparation, a responsive application shell and protected read-only reference screens. Baseline: `2d84e0c`. [PR #10](https://github.com/muhdmuizz/sistem-abm/pull/10) has verified code: head `b4af255`, [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393), 125 tests / 937 assertions plus build/browser checks and visual inspection. Code merged in PR #10 at `e5a93c7ae113f2ea2e4752983d41683642ad698d`. Google OAuth was initially unconfigured; initial local setup and real sign-in are now user-confirmed. See the acceptance updates below for remaining manual checks. No login bypass or guessed user/OS assignments is authorised.

Audited user/permission editing, financial workflows and production release remain outside this first interface slice. Build/browser checks and screenshot review passed. Local/provider acceptance must still be recorded here and in [Batch 008](batch-008-first-interface.md); do not mark the batch fully closed from a Git pull alone.

## Recording progress from now on

Follow [AGENTS.md](../../AGENTS.md). Each batch MD must record scope, baseline, actual tested SHA and evidence, merge checkpoint, local synchronisation and remaining limitations. Update this register at start, verification, merge and local confirmation. Use a documentation-only follow-up where final merge/local metadata arrives later. Do not begin another implementation batch while known close-out information is missing.

For the next session: verify live `dev`, read this register and the relevant batch/AD/reference files, then continue from the actual recorded state. Do not restart completed foundations or confuse the older six-step workstream description with a fresh implementation starting point.

## Local provider acceptance and initial-account correction — 13 November 2026

User reports that OAuth branding/client were created in the dedicated Sistem ABM Cloud project, credentials were saved locally, the Google button is enabled, and Google password/consent completed for `muizz@moe.gov.my`. The callback returned the generic ABM denial; the redacted local log shows `RuntimeException`. A guarded read-only query against PostgreSQL `sistem_abm` returned **Matching accounts: 0**. This confirms missing local registration as a blocker; it does not exclude another identity-provider eligibility issue. Personal `suarafata@gmail.com` manages Cloud only and is not approved as an ABM login. User confirmed **Sektor Pengurusan, JPNT** for the initial operator account.

The merged bounded correction provides: a local-console-only first-Superadmin registration command using an exact existing JPNT/Sektor, explicit real-operator declaration and reason, atomic account/role/audit creation, serialised first-run guard, and refusal to overwrite existing accounts or operate after any Superadmin exists. The audit labels identity assurance as local operator declaration, never as Google verification. No password, browser session, Google subject or verified-email timestamp is supplied by setup. Normal Google validation remains mandatory. No other roles/activity/OS assignments are granted. Production bootstrap and general account administration remain outside this correction.

Baseline: `30146d5599abd26860ecf77937748d820ebdd654`. Code verification is complete; see evidence below. Code merged in PR #12; first successful local login is now user-confirmed (see subsequent acceptance record).

### Initial-account correction verification

- Tested application head: `5afed49b07bc929a966d98773244687bdb7594f5`.
- [CI 43422069980](https://github.com/muhdmuizz/sistem-abm/actions/runs/43422069980): **134 tests passed / 1002 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`; frontend build and existing Chromium checks passed.
- Nine added bootstrap cases cover attributed initial registration, one-time closure (including removed role), existing case-variant refusal, exact organisational references, explicit declaration/reason, production denial, audit-failure rollback and normal verified-Google login after setup.
- First CI attempt caught an inherited test-helper name collision; corrected before successful verification. No unsuccessful revision was merged.
- [PR #12](https://github.com/muhdmuizz/sistem-abm/pull/12); merged at `f6e785a85367c24faee45d37a6c93c6cd6c301ae`. Subsequent evidence-only edits do not change tested PHP source.
- First successful real local login is now user-confirmed after the supplied setup instructions. Full Batch 008 manual acceptance and broader tenant/production approval remain unverified.

## User-confirmed first local login — 13 November 2026

After the guarded pull/migration/bootstrap instructions for PR #12, the user reported: **“I've entered the web app.”** Record local application setup and initial-account/real Google sign-in as user-confirmed for `muizz@moe.gov.my`, JPNT / Sektor Pengurusan, intended Superadmin. This is user-reported acceptance, not cloud inspection of Windows, a database/audit query, or an independently captured browser trace.

The initial missing-account blocker is resolved for this login. Personal Gmail remains Cloud administration only. No further OAuth client or login-policy relaxation was needed. App source remains the verified PR #12 checkpoint `f6e785a` (tested head `5afed49`, CI 43422069980: 134 tests / 1002 assertions).

At the first-login checkpoint, reference-page navigation/totals, pagination, device behaviour, logout/re-login, unregistered-account rejection and session checks were pending. See the subsequent confirmation below for the checks the user has since completed.

## User-confirmed reference pages and repeat login — 13 November 2026

The user replied **“Clean.”** to the requested checks: open all four reference sections, verify totals of PTJ **69**, Aktiviti Kewangan **5**, Tempat Bertugas **579**, OS **33**, then log out and sign in again. Record those specific checks as passed by user report. This does not establish pagination controls, mobile layout, unregistered-account rejection, idle/absolute expiry, renewal, or independent audit/database inspection. Those manual checks remain pending. No application code changed; existing CI evidence remains 134 tests / 1002 assertions at tested head `5afed49`.

## User-confirmed narrow-window layout — 13 November 2026

The user replied **“Clean.”** after narrowing the browser window and checking that the mobile menu opens/closes, reference tables become cards and no horizontal overflow appears. Mark those responsive layout checks passed by user report. This is a resized desktop browser check, not proof of sign-in or layout on an actual mobile device.

Current manual acceptance: initial Google login, four reference-page totals, logout/re-login and the specified narrow-window layout checks are user-confirmed. Pagination controls, actual mobile-device sign-in/layout, unregistered-account rejection and idle warning/renewal/idle/absolute expiry remain pending. Existing CI covers automated role/identity/session and browser-layout cases, but is recorded separately from manual acceptance. Batch 008 remains partially accepted; no complete security or production certification is claimed. Application source/tests are unchanged (134 tests / 1002 assertions at `5afed49`).

## Batch 009 — locally applied; requested smoke checks confirmed

User requested continuation after the recorded local interface checks. [Batch 009](batch-009-user-administration.md) adds the approved Pengguna & Peranan administration slice: explicit audited identity/status/scope changes, exact six-role tuples, stale-editor protection and all-device session revocation. Baseline `c76f52d`. No financial rules, real-user seeds or central OS permissions are changed. Security email delivery/notifications and Security Events viewer remain pending. Verification passed at tested head `b8ea428`: 153 tests / 1143 assertions, PHP 8.4.27 / PostgreSQL 18.7, build/browser checks and visual review. Merged in [PR #17](https://github.com/muhdmuizz/sistem-abm/pull/17) at `22476277c47bcdfdb22aa6b428441528e6b182ec`. Local migration and the requested inspection/own-logout smoke checks are now user-confirmed (see the acceptance record below); other manual mutations/device checks remain unverified. Batch 008's remaining manual checks stay open.

## User-confirmed Batch 009 local smoke checks — 13 November 2026

The user replied **“Clean.”** after the supplied guarded dev pull/database verification/migration/server instructions and the requested checks: open Pengguna & Peranan; inspect the existing operator's JPNT / Sektor Pengurusan / Superadmin record; inspect Tambah Pengguna; use own Log Keluar Semua Peranti from Utama; then sign in through Google again. Record local synchronisation/migration and those specific smoke checks as passed by user report. No direct cloud inspection of Windows, local audit rows or browser trace is claimed.

This does not establish manual creation of another real account, role/status mutations, simultaneous physical-device revocation, forced revocation of another user, stale concurrent browser editors, or notification delivery. Those remain unverified locally; their applicable automated cases are supported separately by CI 43427826563 (153 tests / 1143 assertions at b8ea428). Security email/notification operations and other out-of-scope work remain pending. No application changes or new test execution are claimed by this documentation update.

## Batch 010 — locally applied; requested inspection confirmed

User explicitly approved the central PTJ → Aktiviti Kewangan → OS editor. [Batch 010](batch-010-central-permissions.md) implements category defaults and per-setting PTJ overrides/reset, mapped non-excluded OS choices, atomic attributable saves and content-state stale-editor checks. Baseline 674b80a. No permission selections, source mappings, roles, financial data or OAuth clients are seeded/changed by delivery. Verification reviewed on 14 November: tested head `32ba5ef`, [CI 43432337237](https://github.com/muhdmuizz/sistem-abm/actions/runs/43432337237), 168 tests / 1571 assertions on PHP 8.4.27 / PostgreSQL 18.7, build/browser checks and final visual review passed. Merged in [PR #20](https://github.com/muhdmuizz/sistem-abm/pull/20) at `d514ae56bd5b8e963d921f1fd38cad794b273ac5`. Local synchronisation and requested inspection are user-confirmed; actual local permission-mutation acceptance remains unverified. Earlier manual/security limitations remain tracked.

## User-confirmed local inspection — 14 November 2026 (Asia/Kuala_Lumpur)

The user reported **“Batch 010 is clean.”** after the supplied local update and requested inspection of Pengurusan Aktiviti Kewangan: the 69-PTJ list, JPNT/PPD/SMK PTJ category editors, one individual PTJ's inherited settings, expandable activities and individual OS controls. Record local synchronisation and those requested inspection checks as passed by user report. The instructions asked for inspection without saving permission changes; this does not establish actual local permission writes, reset persistence, audit-row inspection, simultaneous stale editors, or physical-device checks. Applicable automated cases remain supported separately by CI 43432337237 (168 tests / 1571 assertions at 32ba5ef). No new application/test execution is claimed by this documentation update.

The user also clarified that the local server remains running during routine updates: pull/clear necessary caches in another terminal and refresh the browser (Ctrl+F5 when assets change). Restart only for a concrete change that requires it, with the reason explained. This preference is recorded in AGENTS.md for later sessions.

## Batch 011 — local update/inspection confirmed; visual refinement pending

User approved [Kitaran ABM & Fasa administration](batch-011-kitaran-fasa-administration.md). Baseline 35db2e6. Implement year-only creation, explicit closed-by-default phases, per-cycle Pindaan numbering, valid date/scope controls, audited open/close/extend/reopen and stale-editor protection. Overlapping phases remain allowed; no workflow statuses or financial records are changed. Kitaran archive/delete, phase reclassification and financial workflows are outside this bounded slice. Verification passed at `431486581113c0294be1e850134eb21ea4d00697`: [CI 43523750417](https://github.com/muhdmuizz/sistem-abm/actions/runs/43523750417), 187 tests / 1730 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test database, build/browser checks and desktop/mobile screenshot review. Merged in [PR #23](https://github.com/muhdmuizz/sistem-abm/pull/23) at `6c6e777b83e03d85a3ebab02eba45d5d8540fbfc`. Local synchronisation/migration and requested list/year-only form inspection are user-confirmed; actual phase mutations remain unverified. Empty-state padding needs correction (see below). Keep the running local server and use refresh after the update; previous acceptance/security limitations remain tracked.

## Batch 011 local confirmation and design clarification — 14 November 2026

The user replied **“Clean.”** to the supplied guarded dev pull/database check/migration/cache-clear commands and requested Kitaran list/Tambah Kitaran year-only form inspection. Record those checks as user-confirmed, not direct cloud access to Windows. The attached local screenshot shows the empty Kitaran list. Actual cycle/phase creation, scope changes, opening/closing/extending/reopening and local audit/stale-editor checks remain unverified; no such mutation is inferred.

The user also identified **“Tiada Kitaran ditemui.” too close to the table border** and requested stronger system-wide spacing rules while retaining the existing anti-AI-slop direction. AD-013 Sections 01.1, 06, 07 and 12 now make padding, breathing room, responsive grouping and empty/loading/error-state inspection explicit. This is an authorised design clarification, not a new functional batch or financial decision.

**Open visual defect:** Kitaran empty-state padding is insufficient in the user-supplied screenshot. The earlier visual review covered populated fixture screens and did not establish zero-record-state polish. Correct the shared empty-state treatment and inspect all affected desktop/mobile states before declaring this issue resolved. This documentation-only change does not fix application CSS or certify full UI completion. Existing CI evidence remains 187 tests / 1730 assertions at `4314865`; no new test execution is claimed.

## Batch 012 — merged; local migration confirmed

User approved [canonical financial storage](batch-012-canonical-financial-storage.md). Baseline `435a360f8a026c03b8bb7c93645d88e2f5e19415`. Implement storage-only parent/OS/source/output identity, same-PTJ workplace attribution and exact-number safeguards. No financial UI/write service, OS calculations, completeness or submission workflow is delivered by this foundation. Verified at `6fd0a018fc816fa4a4b831d68c98f0b5b35a2f61`: [CI 43531677184](https://github.com/muhdmuizz/sistem-abm/actions/runs/43531677184), 206 tests / 1858 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test database; build and existing browser regression passed. Merged in [PR #26](https://github.com/muhdmuizz/sistem-abm/pull/26) at `0ff960d0744f06256d4025c482047ba6684d36be`; local synchronisation/migration user-confirmed. Step 02 remains in progress. Apply AD-013 to subsequent UI work; the broad visual refinement is deferred by user instruction and the known empty-state defect remains tracked.

## Batch 013 — merged; local migration confirmed

[Audited initial financial activation](batch-013-audited-financial-activation.md) implements the next shared preparation prerequisite: distinct role/scoped Bidaan and OS activation with Fasa/central permission checks, stale-write protection and atomic audit. Baseline `4e42ddaa588cd3a281730fad44440ad54cb5361d`. Backend services only; no OS fields/calculations, draft-save interface or submission workflow. Verified application head `c91a7126bb0d6ef0830d129e75d98b75ff299d9f`: [CI 43535741061](https://github.com/muhdmuizz/sistem-abm/actions/runs/43535741061), 239 tests / 2005 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test database; build/existing browser regression passed. Merged in [PR #28](https://github.com/muhdmuizz/sistem-abm/pull/28) at `bf69e80a2d21712cefe917db033c900c6d043f09`; local synchronisation/migration for Batches 012–013 is now user-confirmed (see below). Step 02 remains in progress.

## User-confirmed Batches 012–013 local update — 14 November 2026

The user replied **“Clean. Proceed.”** to the last supplied guarded dev pull/database verification/config-clear/normal-migration commands. Record local synchronisation and application of any pending Batch 012/013 migrations as passed by user report. No direct cloud access to Windows, local financial/audit-row inspection or activation service execution is claimed. Those batches delivered backend storage/activation services; manual financial operations and the broader device/security checks remain unverified. Existing CI evidence remains 239 tests / 2005 assertions at `c91a712`; no new test execution is claimed by this acceptance note.

## Batch 014 — merged; local synchronisation confirmed

User approved [audited Butiran draft creation/editing](batch-014-audited-butiran-drafts.md). Baseline `afc4d8577f003eca3080015b35c99235fdc6050a`. Reuse financial actor/role/central/Fasa guards, enforce both current and selected workplaces, stable IDs and parent/source revisions, and commit source changes plus audit atomically. Backend draft envelopes only; OS fields/calculations, financial UI, output recalculation and submission workflow remain deferred. Verified at `3abec8519519bbf8b20db393f64003cb5347c856`: [CI 43547530349](https://github.com/muhdmuizz/sistem-abm/actions/runs/43547530349), 272 tests / 2178 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test database, build/existing browser regression passed. Merged in [PR #30](https://github.com/muhdmuizz/sistem-abm/pull/30) at `12fc1459a95775674c3c9ac8a092996e14d4c929`; local synchronisation/existing-page smoke checks are user-confirmed; Step 02 remains in progress. No new migration.

## User-confirmed Batch 014 local update — 14 November 2026

After the supplied guarded dev pull/config-clear commands and requested refresh/open Utama and Kitaran ABM checks, the user reported **“Clean, what's next?”**. Record those local synchronisation and existing-page smoke checks as user-confirmed. No migration was required. This is not evidence of manual draft service execution, financial writes/audit inspection or a new financial interface. Existing automated evidence remains CI 43547530349, 272 tests / 2178 assertions at 3abec85.

## Batch 015 — merged; requested local acceptance user-confirmed

User approved the [Bida Peruntukan workspace and activation UI](batch-015-bida-peruntukan-workspace.md). Baseline `238971301798dd475b87b731fce525744cde1f73`. Add explicit Kitaran/PTJ context, authorised activity/Bidaan cards, OS catalogue, contextual activation confirmations and service-backed POST actions. GET never creates financial records. Apply AD-013 spacing/anti-slop and actual role/central boundaries. OS inputs/calculations, source editor and formal submission remain deferred. Verified head `daf17e186514e4df0d50f52cac5b591e6e73aeee`: [CI 43552003016](https://github.com/muhdmuizz/sistem-abm/actions/runs/43552003016), 288 tests / 2293 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test database, frontend build, Chromium assertions and desktop/mobile review of eight populated/empty/validation states passed. Merged in [PR #32](https://github.com/muhdmuizz/sistem-abm/pull/32) at `96e3507150dfa595ec241fe81517a48ba6777887`; requested local acceptance user-confirmed. No new migration; OS source editing/calculations/submission remain deferred.

## Batch 015 — user-authorised Fasa date correction

[Correction record](batch-015-fasa-budget-year-correction.md): Kitaran is the budget year; Fasa dates may start earlier or span years (2028 bidding starts in 2026). [PR #34](https://github.com/muhdmuizz/sistem-abm/pull/34): application head `a0985fc9fb9a18688a123052d6998f5e12db7b51` verified by [CI 43553865130](https://github.com/muhdmuizz/sistem-abm/actions/runs/43553865130): 290 tests / 2311 assertions, PHP 8.4.27 / PostgreSQL 18.7, isolated test database, build/Chromium passed. Merged at `53d0e45f6c5c2469033f4e6d8333d8cd6b8af588`; local migration/requested acceptance user-confirmed. Bidaan/Anggaran dates remain restricted to the Kitaran year. Previous test totals above describe the prior merged checkpoint.

## User-confirmed local acceptance — 14 November 2026

The user reported **“Clean.”** after the supplied guarded dev pull/database-check/normal-migration/cache-clear commands and the requested local checks: create/save/open a Fasa for Kitaran 2028 starting in 2026; choose Kitaran/PTJ in Bida Peruntukan; activate one Bidaan and one OS; refresh to verify persistence; inspect feedback/spacing and the active-OS preparation-not-yet-open message. Record those bounded checks as user-confirmed. No direct cloud inspection of Windows, audit rows, all roles, stale editors, physical devices or OS calculations is claimed. Earlier security/manual limitations remain tracked. No new application changes or test execution accompany this documentation update.

## Batch 016 — local update confirmed; direct service execution unverified

[Batch 016](batch-016-atomic-multirow-drafts.md) adds one-Anggaran all-or-nothing source draft create/edit batches on the existing audited service. User directed continuation after Batch 015 acceptance. Baseline b376280. Application head `781338b` verified by [CI 43557005407](https://github.com/muhdmuizz/sistem-abm/actions/runs/43557005407): 302 tests / 2403 assertions, PHP 8.4.27 / PostgreSQL 18.7 in isolated test database, build/existing Chromium checks passed. [PR #37](https://github.com/muhdmuizz/sistem-abm/pull/37) merged at `47f223059e020ab6e12abce0b2aee6ff2d45ff87`; local update pending. No route/UI, OS field schema/calculation, migration or seed. Step 02 remains in progress; OS source editor/adapters and calculation integration remain deferred.

## Batch 017 — local update/active-OS opening confirmed; broader navigation checks unverified

[Batch 017](batch-017-anggaran-read-workspace.md) adds active-OS opening, saved Butiran metadata and same-Bidaan navigation without exposing an unapproved source editor. Application head `7e52730` verified by [CI 43559544013](https://github.com/muhdmuizz/sistem-abm/actions/runs/43559544013): 315 tests / 2505 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated test database, build/Chromium and twelve desktop/mobile state screenshots reviewed. [PR #39](https://github.com/muhdmuizz/sistem-abm/pull/39) merged at `fab380ccf69e5c8501bcd520a95a3e9d2f522e81`; local acceptance pending. Batch 016 local update remains unconfirmed. No migration or new OS policy. Step 02 remains in progress; the read shell is a bounded first part of Step 03, not completion of Senarai Anggaran/editor workflows.

## Batch 018 — requested local update/Log Tindakan inspection confirmed

[Batch 018](batch-018-scoped-anggaran-history.md) exposes bounded read-only OS/source-action metadata with current/event workplace scope before counts/pagination. Application head `48b245a` verified by [CI 43561176414](https://github.com/muhdmuizz/sistem-abm/actions/runs/43561176414): 325 tests / 2596 assertions, PHP 8.4.27 / PostgreSQL 18.7 in isolated testing database, build/Chromium and sixteen reviewed state screenshots passed. [PR #41](https://github.com/muhdmuizz/sistem-abm/pull/41) merged at `30713fd5c57244cafd3c7ae0a7a1b227c7c0a2d0`; local checks pending. No migration, seed, global audit viewer or OS financial policy. Batches 016–017 local acceptance remains unconfirmed; Step 02 and the bounded Step 03 work remain in progress.

## User-confirmed local update and inspection — 14 November 2026

The user reported **“It's there. Clean. Proceed.”** after the supplied guarded dev pull/cache-clear commands and the requested active-OS opening/Log Tindakan expansion, activation-entry, actor/time and spacing checks. Record synchronisation through Batch 018 and these bounded UI checks as user-confirmed. This also confirms delivery of Batches 016–017 by the combined pull; it does not establish manual multi-row service execution, all OS navigation, other roles/devices, hidden-row permissions or audit/database inspection. No direct Windows access or new test execution is claimed. Earlier pending local-update statements are superseded only for these checks.

## Batch 019 — local update/existing-page smoke check confirmed

[Batch 019](batch-019-audited-draft-duplication.md) adds an internal same-Anggaran draft copy with a fresh identity, exact saved-input preservation, live guards, stale-retry protection and atomic lineage audit. Calculated sources remain blocked pending approved recalculation. Application head `ade9a9a` verified by [CI 43562510603](https://github.com/muhdmuizz/sistem-abm/actions/runs/43562510603): 342 tests / 2722 assertions, PHP 8.4.27 / PostgreSQL 18.7 in isolated test database, build/Chromium plus duplicate-event desktop/mobile review passed. [PR #43](https://github.com/muhdmuizz/sistem-abm/pull/43) merged at `c2ab85c9ce635b607d6111756454e9f1e5639498`; local update/existing-page smoke check user-confirmed; direct copy execution unverified. No copy button/API, OS policy, migration or seed. The combined update and requested Log Tindakan UI checks through Batch 018 are now user-confirmed; direct draft-service execution remains unverified locally.

## User-confirmed local update — 14 November 2026

The user reported **“Clean, next.”** after the supplied guarded dev pull/cache-clear commands and requested Anggaran workspace refresh. Record local synchronisation and that existing-page smoke check as user-confirmed. There is no public copy action; this does not establish manual duplication-service execution, calculated copying, other roles/devices or direct audit/database inspection. Existing CI remains 342 tests / 2722 assertions at ade9a9a; this documentation update adds no application change or test execution.

## Batch 020 — Program/ Aktiviti foundation merged; local setup user-confirmed

[Batch 020](batch-020-program-aktiviti-foundation.md) implements approved AD-007 metadata storage, RPM reference hierarchy, guarded draft saving/scoped queries and four-section validation. Baseline debac9c; user explicitly approved proceeding with this foundation. Application head `99bdfa6` verified by [CI 43579320487](https://github.com/muhdmuizz/sistem-abm/actions/runs/43579320487): 393 tests / 3003 assertions, PHP 8.4.27 / PostgreSQL 18.7 in isolated test database, build/existing Chromium passed. [PR #46](https://github.com/muhdmuizz/sistem-abm/pull/46) merged at `97733ab16742d0b2e4a57645a78dd82f9d86c670`; local migration/reference import user-confirmed. No programme UI, OS editor/financial association or paused OS-policy work. Programme dates remain in the Kitaran year; Fasa may open earlier. The verified checkpoint above includes this foundation; the programme form and OS integration remain pending.

## Batch 020 local acceptance — 14 November 2026

The user reported “Clean, what’s next?” after the guarded dev pull, config clear, normal migration, explicit `RpmReferenceSeeder` import, cache clear and requested existing Anggaran workspace refresh. These local setup and smoke checks are user-confirmed. The cloud agent did not access Windows or independently query reference counts. Direct programme service execution, programme create/edit UI and financial operations are not confirmed by this report. This acceptance update changes documentation only; the recorded application CI evidence remains unchanged.

## Batch 021 — merged; requested local acceptance user-confirmed

[Batch 021](batch-021-program-aktiviti-interface.md) adds the scoped programme list and four-section draft interface using Batch 020's audited service. Tested application head `e9da548c44ac0ab77a21d470de0d66b235be0654`; [CI 43583261810](https://github.com/muhdmuizz/sistem-abm/actions/runs/43583261810): 408 tests / 3129 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB, build/Chromium passed; desktop/mobile state review completed and mobile objective clipping corrected. [PR #49](https://github.com/muhdmuizz/sistem-abm/pull/49) merged at `981b766d4435724abf6dc7b463bbce6c2a109fa1`; requested local acceptance user-confirmed. No migration/seed, OS entry/association or financial completeness.

## Batch 022 — approved scope and consolidated audit

User approved sidebar scrolling, compact login, spacing/alignment, typography, buttons/actions, forms/tables/mobile and interface-state refinement. [Batch 022](batch-022-shared-ui-refinement.md) records the consolidated audit before implementation. Verification passed and merged at `f8d0cbf9d5875815b354b0de7cba545ba8dfb0a9`; requested local refinement checks user-confirmed; overall design refinement remains future work. No financial policy or workflow changes.

## Batch 022 — merged; requested local checks user-confirmed

[Batch 022](batch-022-shared-ui-refinement.md): tested head `9422886677ab9b0198d3047ec3d6ddefc902e621`, [CI 43585733643](https://github.com/muhdmuizz/sistem-abm/actions/runs/43585733643), 410 tests / 3141 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated test DB, build/Chromium passed. Login fit, short sidebar/drawer scrolling and shared states verified; representative desktop/mobile visual review completed (104 captures, 25 new). [PR #51](https://github.com/muhdmuizz/sistem-abm/pull/51) merged at `f8d0cbf9d5875815b354b0de7cba545ba8dfb0a9`; requested local refinement checks user-confirmed; overall design refinement remains future work. No migration/seed or business-rule changes.

## Batch 023 — authorised scope (15 November 2026)

User authorised MD reconciliation followed by the shared spreadsheet editor, retaining the approved OS decisions. Baseline `88e852623a3bfe6bf83a0133c327a3200025a7f7`. [Batch 023](batch-023-spreadsheet-anggaran-editor.md) records implementation boundaries and evidence. The batch reconciles the 30-column contract/readiness summaries, then introduces the common source-draft grid with OS25000 as its first configured definition. OS14000 detailed work remains paused. No new financial policy or blanket production-readiness approval.

## Batch 023 — merged; local functional checks pending

Shared typed spreadsheet source editor configured first for OS25000; MD contract/readiness reconciliation preserves owner-approved decisions. Tested `11255c04c0c7c0d117c66d58aa475896b7f654e3`, [CI 43632464587](https://github.com/muhdmuizz/sistem-abm/actions/runs/43632464587): 423 tests / 3245 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated test database, build/Chromium passed; 11 new grid screenshots reviewed at desktop/mobile widths. Actual HTTP persistence and audit rollback covered; browser error responses use static-fixture interception. No migration/seed or official totals/completeness. Local update/functional acceptance pending. [PR #54](https://github.com/muhdmuizz/sistem-abm/pull/54) merged at `683629ba734e9104897e8722363bbcaebeb92270`. See [Batch 023](batch-023-spreadsheet-anggaran-editor.md) for limits and follow-through.

## Batch 023 local update — 15 November 2026

The user reported “Clean, proceed.” after the guarded dev pull and optimize:clear instructions. Record local synchronisation/cache clear as user-confirmed. Detailed OS25000 entry/save/reopen/duplicate/delete, other roles/devices and audit inspection remain unverified locally; no independent Windows access is claimed.

## Batch 024 — authorised SBK spreadsheet scope

Authorised continuation of the shared spreadsheet editor. Baseline `250d57f95706e3caf1eb30b9a8a815d7644700e6`. [Batch 024](batch-024-sbk-spreadsheet-definition.md) enables the approved OS42000 SBK source template for Pendidikan Rendah (030228) and Pendidikan Menengah (030310), using the existing four shared Pukal options, manual monthly KADAR and fixed 12-month exact preview. Keep catalogue identities/mappings unchanged, AMal excluded and OS25000 behaviour intact. No migration/seed or official totals/completeness integration planned.

## Batch 024 — merged; local update/functional checks pending

Typed OS42000 SBK definition enabled for the two approved education contexts, with context-aware definition resolution, fixed columns and manual KADAR. Application head `5ebdce2244d8f80aa021df961b1af4efd1c4eb9f`, [CI 43635684953](https://github.com/muhdmuizz/sistem-abm/actions/runs/43635684953): 431 tests / 3354 assertions on PHP 8.4.27 / PostgreSQL 18.7 isolated test DB; build/Chromium passed. Fourteen SBK states reviewed before numeric alignment correction; final representative correction/OS25000 review completed. Actual HTTP persistence is separate from static browser response interception. No migration/seed or official output/completeness. Local update/functional checks pending; see [Batch 024](batch-024-sbk-spreadsheet-definition.md).

Batch 024 [PR #56](https://github.com/muhdmuizz/sistem-abm/pull/56) merged at `68a8f46fb839675f2d83ad7914e9b4cb2f163a19`. This supersedes its earlier merge-pending status only; local acceptance remains pending. Source draft/preview implementation does not establish official financial output/completeness or production readiness.

## Batch 024 local update — 15 November 2026

The user reported “Clean, continue.” after the supplied guarded dev pull/cache-clear instructions. Local synchronisation/cache clear are user-confirmed. Detailed SBK monthly-rate/save/reopen/edit checks, other roles/devices and audit inspection remain unverified locally; the cloud agent did not access Windows.

## Batch 025 — authorised TASKA spreadsheet scope

Authorised continuation from `258fe6f14262cea08355c5c2f9c5ae4a2a97adf7`. [Batch 025](batch-025-taska-spreadsheet-definition.md) implements the owner-approved TASKA source fields and exact row preview under PPN / 010102, preserving SBK/OS25000 and the existing reference/canonical identities. No migration/seed or official outputs/completeness.

## Batch 025 — merged; local update/functional checks pending

TASKA typed source definition under PPN/010102 adds parent text, multi-child quantity, budgeted months and exact reference-rate previews while preserving SBK/OS25000. Tested `9c4bce154c684ec232416e9e4d99572ddc64cee2`, [CI 43653498708](https://github.com/muhdmuizz/sistem-abm/actions/runs/43653498708): 438 tests / 3449 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB; build/Chromium passed. Twelve new TASKA states reviewed in the early pass; final representative populated/validation desktop/mobile review completed after preview/test-focus correction. Static browser response interception is separate from actual HTTP persistence. No migration/seed or official totals/completeness; local update/checks pending. See [Batch 025](batch-025-taska-spreadsheet-definition.md).

Batch 025 [PR #58](https://github.com/muhdmuizz/sistem-abm/pull/58) merged at `0eb73bc132474e1355d22f706104600001c76c06`, superseding its earlier merge-pending status. Local acceptance and official financial integration remain pending.

## Batch 025 local confirmation and monetary display commitment — 15 November 2026

User reported “The previous is clean.” following the Batch 025 update instructions. Record local synchronisation/cache clear as user-confirmed; do not infer independent Windows, detailed TASKA functional, other-role/device or audit inspection. Existing application evidence remains 438 tests / 3449 assertions at 9c4bce154c684ec232416e9e4d99572ddc64cee2; this documentation-only clarification adds no application verification.

User explicitly reaffirmed tabular numeric presentation and monetary `4,320.00` rather than `4320`. [AD-013 Section 05.1](../architecture/AD-013-system-ui-ux-interaction-design.md#051-monetary-display-contract--owner-clarification-15-november-2026) now states comma grouping/two displayed decimals, right alignment and exact-value preservation. The [implementation commitment](monetary-display-formatting-commitment.md) makes this a prerequisite of the next proposed OS28000 batch, including the existing OS25000/SBK/TASKA grids. At that documentation checkpoint raw monetary display remained an implementation gap; Batch 026 subsequently implements the shared correction, with local acceptance pending.

## Batch 026 — authorised OS28000 and monetary scope

Owner authorised proceeding after the explicit monetary display clarification. Baseline `eeca0f056288d62e27d0fb0946c91560d4c54877`. [Batch 026](batch-026-maintenance-money.md) combines the approved maintenance source definition with the mandatory shared format correction for OS25000/SBK/TASKA. Existing Batch 025 local sync confirmation is already recorded. No migration/seed or official financial output/completeness planned.

## Batch 026 — merged; local update/functional checks pending

OS28000 typed maintenance sources and shared accounting-style money presentation implemented for OS25000/SBK/TASKA/OS28000. Tested `d9f2742950eb6e071ad003bc4609971a09173b96`, [CI 43662888155](https://github.com/muhdmuizz/sistem-abm/actions/runs/43662888155): 445 tests / 3544 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB; build, formatter and Chromium checks passed. Representative 16 new desktop/mobile money/maintenance states reviewed, with three final-head rechecks; see [Batch 026](batch-026-maintenance-money.md) for capture/verification limits. No migration/seed or official financial completeness/output. Local update/checks pending.

Batch 026 [PR #61](https://github.com/muhdmuizz/sistem-abm/pull/61) merged at `0343ac972b1f366443e9508088e7d5d7fc2286d6`, superseding its earlier merge-pending status. The monetary commitment is implemented for the four configured spreadsheet definitions; future monetary interfaces retain the same contract. Official financial integration and local acceptance remain pending.

## Batch 026 local confirmation and UI header rule — 15 November 2026

The user reported “Everything else is clean.” after the prior update instructions and supplied a TASKA screenshot showing 180.00 and 4,320.00. Record local sync/cache clear as user-confirmed and that bounded monetary presentation as visible in the supplied screenshot. Do not claim independent Windows access or complete financial/role/device/audit acceptance. The screenshot exposes inconsistent all-cap/title-case headers; [AD-013 Section 06.1](../architecture/AD-013-system-ui-ux-interaction-design.md#061-consistent-ui-header-casing--owner-clarification-15-november-2026) now requires consistent title-case UI headers with recognised acronyms preserved, while canonical Excel/reference headings stay unchanged.

**Next proposed implementation:** correct current shared-grid headers across OS25000/SBK/TASKA/OS28000, preserve the mandatory accounting-style display, and add the approved OS27000 Pakaian Seragam definition (shared Jawatan reference, four category/job rates, fixed Pasang, Bil. Pengisian × Bil. Pasang × Kadar). This is the proposed next bounded scope, not implementation started or completed. Read its current reference and actual catalogue/mappings before implementation. The header correction must accompany the next task rather than being postponed behind a new definition.

This commit changes Markdown only; application code/CI remains 445 tests / 3544 assertions at d9f2742950eb6e071ad003bc4609971a09173b96. Header strings remain unchanged until the next code task.

## Batch 027 — authorised uniform source and header scope

Owner authorised proceeding from `8a165cb8a39cc94f363e68767fa7b7b0cc805de1`. [Batch 027](batch-027-uniform-headers.md) combines the approved Pakaian Seragam template with the shared title-case header correction across existing grids, preserving accounting-style money. Jawatan uses Reference 07 independently of categories. No new policy or official output/completeness; no migration/seed.

## Batch 027 — pre-merge verification evidence

Tested `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`; [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): 452 tests / 3626 assertions on PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium passed. Representative desktop/mobile state review completed; [Batch 027](batch-027-uniform-headers.md) records coverage and limits. No migration/seed or official financial integration. Local acceptance pending.

## Batch 027 implementation checkpoint — 15 November 2026

[PR #64](https://github.com/muhdmuizz/sistem-abm/pull/64) merged at `dffc2c44ebcf50f757cb1588488b6924f0b95c55`. Pakaian Seragam source drafts now use existing PPN/010808 mapping, shared Reference 07 Jawatan, four approved category/job rates, fixed Pasang and exact Bil. Pengisian × Bil. Pasang × Kadar preview. Actual title-case headers are implemented across OS25000/SBK/TASKA/OS28000/Pakaian Seragam, preserving canonical Excel/reference headings and accounting-style money.

Tested application head `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`; [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): 452 tests / 3626 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium and representative desktop/mobile review passed. See [Batch 027](batch-027-uniform-headers.md) for evidence limits. Local acceptance pending. No migration/seed or new policy. Other OS27000 variants, official output/totals/completeness and programme inheritance remain pending; Steps 02/03 remain in progress. AD-008 remains Draft.

## Batch 027 local confirmation and Batch 028 start — 15 November 2026

User reported “Clean” after Batch 027 guarded sync/cache instructions. Record synchronisation/cache clear as user-confirmed, without inferring full financial/role/device acceptance. User corrected Bil. Pasang: mandatory fixed **2**, defaulted and locked. This explicitly supersedes its previous editable-input specification for both existing Pakaian Seragam/Kasut choices. User authorised repair and next definition.

## Batch 028 — authorised fixed-pairs and Touch ’n Go scope

Baseline `8ca2c50ce2e026d1e1f404f676c3962d58b39cc6`. [Batch 028](batch-028-fixed-pairs-touchngo.md): enforce fixed two pairs, server rejection of multiplier injection, preserve old stored source until explicit audited save, and add approved monthly Touch ’n Go source definition in its three existing activity mappings. Keep title-case headers and exact accounting display. No migration/seed, catalogue/reference-rate change, official financial outputs/completeness or programme inheritance.

## Batch 028 — pre-merge verification passed

Tested `c780d2dbb6cb5dcb40d10e6e7e7b78fcb7033a28`; [CI 43669111898](https://github.com/muhdmuizz/sistem-abm/actions/runs/43669111898): 458 tests / 3701 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium passed. Representative desktop/mobile state review and final-head rechecks completed; [Batch 028](batch-028-fixed-pairs-touchngo.md) records limits. Merge/local acceptance pending; no migration/seed or official financial integration.

## Batch 028 checkpoint — 15 November 2026

[PR #66](https://github.com/muhdmuizz/sistem-abm/pull/66) merged into dev at `496dbfb0d9cb30f982bc02f8470f6b46773b5269`. Bil. Pasang is now fixed at **2** on the screen and server, superseding the previous editable source input. Valid v1 drafts remain readable; read does not rewrite stored input, current preview uses two, and explicit audited save upgrades the row to v2. OS27000 Touch ’n Go (canonical Bekalan dan Bahan-bahan Lain) now has source editing under existing PPN/010809, Pendidikan Menengah/030304 and Pengurusan Asrama/030316 mappings, with exact Bil. Unit × Kadar Bulanan × Bilangan Bulan previews. No frequency/category/unit input or new rate policy.

Tested application head `c780d2dbb6cb5dcb40d10e6e7e7b78fcb7033a28`; [CI 43669111898](https://github.com/muhdmuizz/sistem-abm/actions/runs/43669111898): **458 tests / 3701 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, build/formatter/Chromium and representative desktop/mobile review passed. [Batch 028](batch-028-fixed-pairs-touchngo.md) records review limits. No migration/seed; local acceptance pending. Official outputs/totals/completeness, programme inheritance, remaining OS and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

## Batch 029 — authorised applicable Jawatan and OS23000 source scope

Owner authorised correction and next OS on 15 November 2026. Baseline `dffc449c439e9cc5c4181bb1177bffa2a6f8011d`. [Batch 029](batch-029-uniform-jobs-utilities.md): restrict Pakaian Seragam Jawatan to the two existing approved-rate jobs, retaining shared Reference 07 and fixed two pairs; preserve/flag old unrelated-job drafts for replacement. Add seven-path Perhubungan dan Utiliti source grid under current canonical identity/existing mappings; legacy Utiliti Pukal association reconciliation remains deferred. Batch 028 remains merged/CI verified with local sync/checks unconfirmed; the new user request is not a “Clean” report. No migration/seed, role/mapping/CSV changes or official financial integration.

## Batch 029 — pre-merge verification passed

Tested `f7cdd2553615c4620804fa21359348338c7b1887`; [CI 43670963928](https://github.com/muhdmuizz/sistem-abm/actions/runs/43670963928): 464 tests / 3802 assertions on PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium passed. Representative final-head desktop/mobile review completed; [Batch 029](batch-029-uniform-jobs-utilities.md) records coverage/limits. Merge and local acceptance pending; no migration/seed or official financial integration. Batch 028 local confirmation remains unreceived.

## Batch 029 implementation checkpoint — 15 November 2026

[PR #68](https://github.com/muhdmuizz/sistem-abm/pull/68) merged at `4c5a112ff31892df04156c62d69aeb13cb27a360`. Owner-corrected Pakaian Seragam Jawatan now offers only **Pembantu Khidmat Am / Pembantu Makmal**, using the existing rate subset of shared Reference 07. Server and paste guards reject other posts; older valid unrelated-job drafts remain preserved/repairable, with a reselection notice and explicit audited v3 replacement. Bil. Pasang remains fixed at 2.

OS23000 **Perhubungan dan Utiliti** source entry is implemented for its existing canonical mappings: seven dependent choices, manual monthly Kadar, fixed 12 months and exact Kadar × 12 previews. No legacy Utiliti Pukal identity/mapping merge is claimed; that entry remains unconfigured pending reconciliation. Shared SBK Pukal-only behaviour and other definitions remain intact.

Tested application head `f7cdd2553615c4620804fa21359348338c7b1887`; [CI 43670963928](https://github.com/muhdmuizz/sistem-abm/actions/runs/43670963928): **464 tests / 3802 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, production build and formatter/Chromium passed. Representative final-head desktop/mobile visual review completed; [Batch 029](batch-029-uniform-jobs-utilities.md) records limits. No migration/seed or role/mapping/CSV edits. Batches 028/029 local acceptance remains unconfirmed. Official outputs/totals/completeness, programme inheritance, remaining OS definitions and submission remain pending; Steps 02/03 remain in progress, AD-008 remains Draft.

## Batch 030 — authorised searchable PTJ and OS23000 card correction

Owner accepted one searchable PTJ control and reported duplicate OS23000 cards, explicitly reaffirming Batch 029 already covers both paths. Baseline `5c6ce7d30ee38cde15d6e5856506d0b954859c45`. [Batch 030](batch-030-ptj-selector-utility-card.md) records one scoped combobox, one Perhubungan dan Utiliti card, preserved scoped older-record access and blocking further old-identity activation. No migration/seed, permission union, financial record merge, new OS editor or financial-policy change. Underlying legacy data reconciliation and prior local confirmation remain pending.

## Batch 030 — pre-merge verification passed

Tested `24e40b2cc29b5b0a309ca0d9d0ea91f258808578`; [CI 43674654094](https://github.com/muhdmuizz/sistem-abm/actions/runs/43674654094): 468 tests / 3842 assertions on PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Fifteen new final-head desktop/mobile selector/card captures reviewed; [Batch 030](batch-030-ptj-selector-utility-card.md) records limits. Merge/local update pending. No migration/seed or financial/permission data merge; earlier local confirmations remain unreceived.

## Batch 030 checkpoint — 15 November 2026

[PR #70](https://github.com/muhdmuizz/sistem-abm/pull/70) merged at `4458d0ec490de4492e9b6f008dd11615d8322257`. Bida Peruntukan now has one scoped searchable PTJ combobox with explicit selection, keyboard/no-result handling and native-select fallback. OS23000 shows one **Perhubungan dan Utiliti** preparation card using Batch 029's existing seven-path Pukal/Bukan Pukal editor. The older Utiliti Pukal standalone preparation card is removed; authorised old records remain under **Rekod sedia ada** within that card. Active older records retain read access; inactive ones are explicitly preserved without an unusable open link. New old-identity activation is blocked; old-only permissions do not grant canonical permissions. Other OS variants remain separate.

Tested application head `24e40b2cc29b5b0a309ca0d9d0ea91f258808578`; [CI 43674654094](https://github.com/muhdmuizz/sistem-abm/actions/runs/43674654094): **468 tests / 3842 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed and 15 new final-head desktop/mobile/fallback captures reviewed. [Batch 030](batch-030-ptj-selector-utility-card.md) records limits. No migration/seed, financial record/permission merge, CSV edit, new OS editor or formula change. Local execution remains unconfirmed. Underlying legacy data reconciliation, official financial integration and programme/submission work remain pending; this is not a claim of physical-device/accessibility or production readiness.

## Batch 031 — authorised development cleanup and MySTEP scope

Owner explicitly states there is no real data and directs removal of obsolete Utiliti Pukal test data/preservation handling, then continuation to the next OS. Baseline `680aafd7f2a3253909c578230795097c4d149842`. [Batch 031](batch-031-utility-cleanup-mystep.md) targets only the obsolete OS23000 identity and test budgets, repoints four source-code mappings to canonical Perhubungan dan Utiliti, removes Batch 030 preservation UI and adds the approved shared MySTEP source definition. Normal targeted migration required; no whole-database reset or compatibility/data-preservation project. New catalogue count 32/30 eligible/2 excluded; mappings remain 105/103 eligible. Prior local confirmations remain unreceived.

## Batch 031 — pre-merge verification passed

Tested `005776cec1c85ebd3cdbabcb889b04925e31f228`; [CI 43679552938](https://github.com/muhdmuizz/sistem-abm/actions/runs/43679552938): 474 tests / 3925 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Representative final-head desktop/mobile review completed; [Batch 031](batch-031-utility-cleanup-mystep.md) records evidence limits. Merge and local targeted migration pending. Obsolete development data is discarded rather than exposed through preservation UI; official financial integration remains pending.

## Batch 031 checkpoint — 15 November 2026

[PR #72](https://github.com/muhdmuizz/sistem-abm/pull/72) merged at `8ac02d13c0304a115783fa49190634e0b27812f9`. Owner explicitly confirmed there is **no real business data** in the current development system and authorised discarding obsolete OS23000 **Utiliti Pukal** test budgets. Batch 030's preservation panel/compatibility handling is removed. A targeted migration deletes obsolete outputs/Butiran/Anggaran and old OS permissions, repoints four existing Kod Aktiviti mappings to **Perhubungan dan Utiliti**, removes the obsolete reference and bumps affected Bidaan revisions. Current canonical budgets and unrelated records remain; no full reset or reference reseed. Catalogue: **32 identities / 30 eligible / 2 excluded**; mappings: **105 / 103 eligible**, including eight actual OS23000 source mappings. Historical 33/31 counts are superseded by this inventory.

The approved shared **OS29000 MySTEP** source grid is now implemented for all three existing variants/seven mappings, using the unchanged four JUMLAH monthly rates, independent categories/purposes, Kitaran-year dates and manual budgeted months. Exact preview = Bil. Unit × Kadar × Bilangan Bulan; date duration does not determine months. Title-case headers and accounting-style money retained.

Tested application head `005776cec1c85ebd3cdbabcb889b04925e31f228`; [CI 43679552938](https://github.com/muhdmuizz/sistem-abm/actions/runs/43679552938): **474 tests / 3925 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB, production build/formatter/Chromium passed. Twelve representative final-head desktop/mobile captures reviewed; [Batch 031](batch-031-utility-cleanup-mystep.md) records limits. Local targeted migration/update remains pending. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft.

## Local update confirmed — 15 November 2026

User reported **“Clean, proceed.”** after the supplied guarded dev pull, database-config check, config clear, targeted normal migration and optimize:clear commands. Record this local update/cleanup migration as user-confirmed. It does not certify unexecuted financial/role/device checks or independent Windows/database inspection; earlier acceptance limits remain.

## Batch 032 — Pakej source spreadsheet in progress

User authorised continuation after confirming Batch 031 local update. Baseline `26d464660a91464f6bfb22c8bcddeef2a66f0c1c`. [Batch 032](batch-032-pakej-spreadsheet.md) implements four approved Pakej components, manual rate bases and independent chargeable nights/days in the shared source editor. No migration/seed, reference/mapping changes or automatic programme/official financial integration. Apply title-case headers and exact accounting-style monetary display.

## Batch 032 — pre-merge verification passed

Tested `5dfb64a5fbeab18df512772ec89de6812802ceea`; [CI 43689211251](https://github.com/muhdmuizz/sistem-abm/actions/runs/43689211251): **480 tests / 4030 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Eleven representative final-head desktop/mobile captures reviewed from 18 Pakej captures (282 total). [Batch 032](batch-032-pakej-spreadsheet.md) records the resolved browser-interaction failure, final evidence and boundaries. Merge/local update pending; no migration/seed. Official financial integration remains pending.

## Batch 032 checkpoint — 15 November 2026

[PR #74](https://github.com/muhdmuizz/sistem-abm/pull/74) merged at `3494a318fda11ecc8468d26f13e3e2f8f56caff5`. OS29000 **Pakej (Mesyuarat/ Bengkel/ Kursus)** now has the approved direct source spreadsheet across its five existing activity mappings. Four components retain separate manual rate bases: Penginapan uses rooms/nights; Makan Minum people/days; Sewaan Dewan halls/days; Sewaan Peralatan units/days. Exact preview = Bil. Unit × Kadar × chargeable duration. Calendar dates/Tempoh do not set charged nights/days. Partial drafts, source precision, title-case headers and tabular/grouped/two-decimal money are retained. Shared validation keeps rate/duration descriptions alongside errors.

Tested application head `5dfb64a5fbeab18df512772ec89de6812802ceea`; [CI 43689211251](https://github.com/muhdmuizz/sistem-abm/actions/runs/43689211251): **480 tests / 4030 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Eleven representative final-head desktop/mobile captures reviewed from 18 Pakej captures (282 total). See [Batch 032](batch-032-pakej-spreadsheet.md) for evidence and limits. No migration/seed or catalogue/mapping/permission/reference CSV changes. Local Batch 032 update and financial acceptance remain pending. Batch 031 supplied local synchronisation/targeted cleanup migration is user-confirmed; detailed financial/role/device acceptance remains unverified. Official outputs/totals/completeness, programme inheritance and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

## Batch 033 — grouped Pakej implementation started

Owner approved the expandable-activity preview and directed implementation. [Batch 033](batch-033-grouped-pakej.md) records shared activity fields, nested component inputs and draft subtotals, with existing transactional guards. Baseline `b3785d014671ad512fff9289f3a64916b1cad64a`. Direct entry only; Program/ Aktiviti linking remains pending. No migration/seed, rate or canonical-column change. Batch 032 local sync remains unconfirmed.

## Batch 033 — pre-merge verification passed

Tested `54ddebf271de5a979a089beaabd5cb0688ce2d1e`; [CI 43694224425](https://github.com/muhdmuizz/sistem-abm/actions/runs/43694224425): **485 tests / 4137 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Fifteen representative final-head captures reviewed from 39 Pakej captures (303 total). [Batch 033](batch-033-grouped-pakej.md) records grouped persistence, resolved save-recovery integration and evidence limits. Merge/local sync pending; no migration/seed. Batch 032 local confirmation remains unreceived.

## Batch 033 checkpoint — 15 November 2026

[PR #76](https://github.com/muhdmuizz/sistem-abm/pull/76) merged at `a9c51a29720d5d833aef86a51f2af568d075bffd`. A direct Pakej activity now holds shared workplace/name/dates once, with expandable component rows and an exact draft subtotal. Components retain separate quantity, manual rate basis and chargeable nights/days; dates never determine charged duration. Add/copy/remove/undo operate at both levels, nested errors reopen the affected group, and pending saves lock edits then restore controls on failure. Incomplete components leave the subtotal incomplete.

One grouped activity uses one existing Butiran source envelope (v2) with a component list and stable group-local UUIDs. This retains parent workplace ownership, transactional save/audit/revision guards and whole-activity pagination. Existing v1 component sources reopen separately as one-component activities; no name-based merge, new preservation interface, schema migration or reset. No rate/mapping/permission/reference CSV/canonical-column changes. Linking to existing Program/ Aktiviti, inheritance and official financial outputs/totals/completeness/submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

Tested application head `54ddebf271de5a979a089beaabd5cb0688ce2d1e`; [CI 43694224425](https://github.com/muhdmuizz/sistem-abm/actions/runs/43694224425): **485 tests / 4137 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Fifteen representative final-head screenshots reviewed from 39 Pakej captures (303 total). See [Batch 033](batch-033-grouped-pakej.md) for coverage, resolved failures and visual-review limits. Local Batch 033 update/financial acceptance pending; Batch 032 local confirmation remains unreceived. No migration/seed or server restart required.

## Pakej entry-point reminder and next proposed OS — 15 November 2026

Owner requested preserving the future Program/ Aktiviti entry point for OS29000 Pakej and is considering restricting direct entry. Recorded as a proposal pending final decision/implementation in [AD-007](../architecture/AD-007-program-aktiviti.md#os29000-pakej-entry-route-follow-up--15-november-2026) and the [Pakej reference](../references/OS29000-pakej-reference.md#pakej-entry-point-direction--owner-note-15-november-2026). Current grouped direct entry stays available; no code, schema or financial rule changes. Batch 033 application checkpoint/CI and pending local status remain unchanged.

**Next proposed implementation:** OS29000 **Umum**, using its [owner-approved specification](../references/OS29000-umum-reference.md): 22 reference rows (18 automatic-rate / 4 manual-rate), contextual units/quantities, Bil. Unit × Kadar preview, conditional personnel fields and shared Jawatan → Gred → derived speaker/facilitator classification. Preserve exact accounting-style money/title-case headers. This is the recommended next bounded scope, not a batch started or implemented; verify live catalogue/mappings and relevant specs before work. OS29000 Bacaan Meter Mesin Fotostat remains another approved, unimplemented variant. This note does not authorise or begin either implementation.

## Batch 034 — OS29000 Umum started

User authorised the proposed Umum implementation. [Batch 034](batch-034-umum-spreadsheet.md) records the five existing mappings, 22 rates, conditional shared personnel fields/derived classification and exact previews. Baseline `e7fb9632b15798e8137cbd8f338b51f947158154`. No migration/seed, reference/rate/policy or Pakej entry-route changes. Prior local acceptance remains unconfirmed.

## Batch 034 — pre-merge verification passed

Tested `44a27bfda1c2046d322df43f12ff39f5e4111096`; [CI 43697537069](https://github.com/muhdmuizz/sistem-abm/actions/runs/43697537069): **491 tests / 4570 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Twelve representative final-head captures reviewed from 19 Umum captures (322 total). [Batch 034](batch-034-umum-spreadsheet.md) records coverage and limits. Merge/local update pending; no migration/seed or Pakej entry-route change. Official financial integration remains pending.

## Batch 034 checkpoint — 15 November 2026

[PR #79](https://github.com/muhdmuizz/sistem-abm/pull/79) merged at `b77a97df189d2a274d32af633a2712be3bedb746`. OS29000 **Umum** source entry is implemented for its five existing mappings, using the unchanged 22 reference rows (18 automatic/four manual) and shared 131 Jawatan/Gred pairs. Dependent personnel inputs derive the dedicated speaker/facilitator classification/rate; other services retain their approved units and rates. Inactive fields and automatic-rate overrides are rejected. Exact row preview = Bil. Unit × Kadar; partial drafts remain saveable with missing required preview inputs withheld. Title-case headers and accounting-style monetary presentation retained.

Tested `44a27bfda1c2046d322df43f12ff39f5e4111096`; [CI 43697537069](https://github.com/muhdmuizz/sistem-abm/actions/runs/43697537069): **491 tests / 4570 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Twelve representative final-head state captures reviewed from 19 Umum captures (322 total); see [Batch 034](batch-034-umum-spreadsheet.md) for coverage and limits. No migration/seed, CSV/rate/catalogue/mapping/permission changes. Local update/financial acceptance pending; previous local statuses unchanged. Programme inheritance, official outputs/totals/completeness and submission remain pending; Steps 02/03 remain in progress, AD-008 remains Draft. Pakej programme-only entry remains a documented proposal.

## Batch 035 — grouped Umum started

Owner corrected the flat Umum layout: one activity can contain multiple services and must follow Pakej's grouping pattern. [Batch 035](batch-035-grouped-umum.md) records shared activity fields, component-specific personnel/rates, exact subtotals and reuse of established grouped storage/renderer. Baseline `d7038551bd543a847cf2892fe1de64a45e307ef3`. No migration/seed, policy/reference or programme-route change. Batch 034 local sync/financial acceptance remains unconfirmed.

### Batch 035 — pre-merge verification

Verified application head `27791b5f619cb033936178079e5d03a02598054d`: [CI 43700580952](https://github.com/muhdmuizz/sistem-abm/actions/runs/43700580952), job 132770603346, passed on PHP 8.4.27/PostgreSQL 18.7 in isolated `sistem_abm_testing`: **496 tests / 4,688 assertions**, production build, money formatter and full Chromium regression. Initial run exposed a Pakej-only nested whitelist; corrected before this passing head.

Five new HTTP regressions cover a mixed-service activity, exact manual precision/subtotals, shared-only purpose/dates and component-specific personnel, stable parent/component IDs, reopening/no-op auditing, atomic rejection of nested overrides/derived fields/bad grades/UUIDs and invalid dates, partial/component-free drafts, v1-to-v2 updates without name-based merging, live Fasa/role/revision guards, audited activity removal, whole-activity pagination and cross-PTJ denial. Existing Umum/Pakej definition and grouped tests remain in the full suite.

Chromium exercises both earlier one-component and new grouped Umum fixtures, expand/collapse, automatic and manual rates, grade reset/VU7/classification, inactive personnel and fee behaviour, mixed-service subtotals, date/quantity independence, exact-value expansion, distinct component/activity copies, removal/undo, nested validation with retained values, repeated stale/network failures and pending-save locking/recovery. Existing Pakej browser checks run against the same metadata-driven grouped renderer, including copy/undo, nights/day labels, exact calculations, source payloads, error recovery and paste. Browser save routes are intercepted for client checks; real persistence/guards are verified separately by HTTP/PostgreSQL tests. Local Node syntax checks passed; no local PHP/Windows/authenticated Google, physical-device or screen-reader execution is claimed.

Artifact 14156720561 contains 342 PNGs (39 Umum, 39 Pakej). Visually inspected 17 final captures: Umum empty desktop/mobile; populated desktop left/right and mobile right; partial desktop/mobile right; read-only desktop/mobile right; collapsed desktop/mobile; validation desktop/mobile; mobile pending save; desktop exact-value expansion; Pakej populated desktop left/mobile right. Shared fields, expandable components, accounting money, withheld partial totals, error text and mobile horizontal scrolling are readable in those captures. Focused skip-link/fixed-sidebar full-page stitching appears in saving/exact captures; this is not certification of the shell or uninspected screenshots. No blocking grouped-editor defect found in inspected states. Physical devices, screen readers, authenticated owner session, Windows sync and financial acceptance remain unverified.

## Batch 035 checkpoint — 15 November 2026

[PR #81](https://github.com/muhdmuizz/sistem-abm/pull/81) merged at `c5bb00945e5ea10a0b16ccba13b5e67f46bc2b7e`. Owner approved Umum following Pakej's expandable activity/component pattern: workplace, purpose and dates are shared once; multiple service components retain their approved categories, conditional personnel, units, rates and exact calculations. Activity subtotals are withheld when any component preview is incomplete. Add/copy/remove/undo operate on components and whole activities. Existing v1 records reopen separately as one-component activities; explicit edits write v2 through existing atomic/audited guards. No name-based merging, migration, reset or reference/rate/catalogue/mapping/permission changes.

Application head `27791b5f619cb033936178079e5d03a02598054d` passed [CI 43700580952](https://github.com/muhdmuizz/sistem-abm/actions/runs/43700580952): **496 tests / 4,688 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB, production build, formatter and Chromium including Pakej regressions. Seventeen final desktop/mobile captures reviewed from 342 PNGs (39 Umum/39 Pakej); full coverage/limits in Batch 035. Local sync and financial acceptance remain pending, including prior unconfirmed batches. Steps 02/03 remain in progress; AD-008 remains Draft. Programme inheritance, official outputs/totals/completeness and submission remain pending; Pakej programme-only entry remains a proposal.

## Batch 036 — Bacaan Meter started / UI sequencing

Owner authorised OS29000 Bacaan Meter implementation on 16 November 2026. [Batch 036](batch-036-bacaan-meter-spreadsheet.md) records four existing mappings, shared print choices, independent current/overall contract periods and monthly copies × manual per-copy rate × manual months. Baseline `1ca2ac10e93917a33b6681d4cc3db03363566169`; no migration/seed/reference/policy change. Batch 035 local acceptance remains unconfirmed. Complete remaining OS editors, then pause for consolidated editor UI refinement before another major module; fix usability defects as encountered and retain AD-013 throughout.

## Batch 036 — pre-merge verification

Tested application head `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`: [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272), job 133058225694, **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 in isolated `sistem_abm_testing`. Production build, exact-money formatter and full Chromium regression passed. Initial runs exposed two test-fixture errors (incorrect hand-calculated multiplication and unsupported workplace type); corrected and independently checked using decimal arithmetic. The empty-fixture browser wait was also corrected before final verification. No financial formula was changed to satisfy an expected test value.

Seven new HTTP/PostgreSQL regressions cover the two shared print choices and editor labels; all four exact activity mappings and rental/other-variant separation; save/reopen/edit/no-op audit with exact per-copy precision; current-year and overall-contract date validation; independent inclusive calendar durations across same-day, month-end, leap-year and maximum supported date boundaries; blank/partial drafts without invented rates or months; atomic rejection of inactive/derived-field injections; live revision/Fasa/role and cross-PTJ guards, read-only access and audited deletion. Existing source/output separation remains (no generated financial outputs).

Chromium checks empty/populated/partial/read-only fixtures at 1440/390 widths and horizontal endpoints; copy/remove independence; current/overall duration parity and boundary changes without altering months or amount; exact rate focus/blur and derived display/payload precision; nested validation and stale-save recovery with retained edits. Client save routes are intercepted; real persistence/guards are separately exercised by HTTP/PostgreSQL. No new filtered-empty or expanded activity states apply to this flat editor. Local Node syntax/calendar checks passed. Browser checks and screenshots are fixtures, not authenticated owner-device acceptance.

Artifact 14193737345 contains **360 PNGs / 18 meter captures**. All 18 meter captures were visually reviewed: empty/populated/partial/read-only at 1440/390 widths and both horizontal endpoints, plus validation at both widths. Also reviewed MySTEP populated desktop-left and grouped Umum populated mobile-right (20 captures total). Spacing, title-case headers, withheld partial results, read-only rendering, error summary/desktop rate error and accounting figures are readable in the inspected views; no blocking new visual defect found. The long input-help text/table density remain candidates for the scheduled consolidated refinement. Horizontal endpoints do not show every middle column or the mobile rate error cell; automated field/duration/error checks cover those interactions. Pending-save/loading and expanded exact-value details were not newly captured for meter; no filtered-empty state applies. Authenticated local Windows, physical-device, screen-reader and owner financial acceptance remain unverified. This is not a UI-complete or production-readiness claim.

## Batch 036 checkpoint — 16 November 2026

[PR #83](https://github.com/muhdmuizz/sistem-abm/pull/83) merged at `0aba6d03b0ebb16d7137015f172d2c24e28c0836`. OS29000 Bacaan Meter source editing now supports the four existing mappings, unchanged shared print choices, monthly copies × exact manual per-copy rate × independent manual months, and separate inclusive calendar current/overall contract durations. Current dates follow Kitaran year; overall contracts may span years. OS24000 remains separate. No migration/seed/reset or reference/rate/catalogue/mapping/permission/export-schema changes.

Tested `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`; [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272): **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. All 18 meter state captures plus two representative existing-editor captures reviewed from 360 PNGs; [Batch 036](batch-036-bacaan-meter-spreadsheet.md) records coverage and visual limits. Local sync/financial acceptance remains pending, including earlier unconfirmed batches. Programme inheritance, official financial outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

**UI sequencing approved:** Complete remaining OS editors, then pause for consolidated editor UI refinement before another major module. Fix usability defects as encountered and apply AD-013 throughout. This is an implementation checkpoint, not a change to financial or design approval rules.

## Batch 037 — OS24000 Sewaan Umum started

Owner explicitly requested Sewaan Umum next, ahead of the separate photocopier-rental editor. [Batch 037](batch-037-sewaan-umum-spreadsheet.md) records baseline `7a9cc09691fdf2a84e0e31a53fc48dcde466794b`, five existing mappings, 64 paths, conditional contract dates and exact quantity × manual rate × manual months. No migration/reference/policy change. Batch 036 local acceptance remains unconfirmed; UI refinement timing unchanged.

### Verification interruption

Initial application head `5d8827fc3402d46c523bf3b8d5670747c024db23` reached the suite, but all seven new rental tests used an incorrect OS29000 fixture identity. The application correctly rejected that identity. Corrected the test fixture to OS24000 at `f87fac4aa4bc331544f7d9ab9b3adf9e070b98e7`; no application/financial rule changed. CI 43799356712 attempts 1 and 2 then failed during checkout (`Repository not found`, exit 128), before PHP/build/browser execution. This is an infrastructure/access failure, not a test pass. Recording the interruption and starting a fresh workflow event; merge and local update remain pending until full verification succeeds.

### Verification blocked — 16 November 2026

Fresh workflow event [43799773291](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`, job 133076870625, also failed at actions/checkout with `Repository not found` / exit 128. Thus both attempts of run 43799356712 and the fresh event failed before execution. Connected repository reads/writes remain available; the checkout access failure's root cause is not established. Do not change credentials/workflow permissions speculatively or merge unverified work.

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) remains open/unmerged. JavaScript syntax checks passed locally; corrected final PHP/HTTP tests, production build, formatter/browser regression and visual review remain unverified. No new successful test count is claimed. Last verified dev remains Batch 036 (`7a9cc09691fdf2a84e0e31a53fc48dcde466794b`, 503 tests / 4,830 assertions at the recorded application head). No local pull/migration instructions issued for this unmerged batch. Resume by repairing/confirming Actions checkout access, rerunning the corrected head, resolving any test failures and completing desktop/mobile visual review before merge. UI refinement sequencing is unchanged.

## Verification recovered and passed — 16 November 2026

Owner requested investigation/repair. Browser inspection confirmed the private repository exists, Actions is enabled and workflow defaults allow reading repository contents/packages. Failed jobs already declared `contents: read`; both successful/failed runners used Git 2.56.1 and the same checkout action. No credentials, repository permissions, workflow YAML or financial/application code were changed during recovery. Re-running [CI 43799773291](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291), **attempt 2 / job 133082975399**, recovered checkout and passed end-to-end. The precise upstream cause remains unconfirmed; operational recovery is verified, not a claimed configuration repair.

**Tested head:** `29932d87de94f89a018d01c15595a403b3337520`; **510 tests / 5,119 assertions**, PHP 8.4.27/PostgreSQL 18.7 in isolated `sistem_abm_testing`; production build, exact-money formatter and full Chromium regression passed. Compare with later head `9c6dfbd1bf23bd71422bad28e12c276f8725a466` confirms only two documentation files changed; application/workflow/test code is identical. Earlier failed fixture/checkout records above are historical and superseded by this verified result.

Seven new HTTP/PostgreSQL tests exercise all 64 exact reference paths (including both blank Tanah leaves and distinct broadcasting labels), three-level metadata, all five exact activity mappings, photocopier/other-OS separation, source save/reopen/edit/no-op audit and exact manual-rate precision, independent inclusive contract days/manual months, conditional date injection rejection, malformed hierarchy/array/derived/inactive-field rejection with atomic rollback, blank/partial drafts, whole-source pagination and live revision/Fasa/role/PTJ guards, read-only access and audited deletion. Official generated outputs remain untouched.

Chromium exercises empty/populated/partial/read-only fixtures at desktop/mobile horizontal endpoints, three-level cascades, Tanah without a fabricated third category, both broadcasting labels, removal of inactive dates and no date resurrection, leap-year duration/amount independence, copy/remove independence, exact money focus/blur and source payloads, field validation and stale-save recovery. Conditional non-contract and expanded exact-value states are captured. Browser save calls are intercepted for client checks; persistence/guards are independently tested through HTTP/PostgreSQL. Shared editor regressions run in the full browser suite.

Artifact **14196960746** contains **382 PNGs / 22 rental captures**. Visually reviewed 14 rental captures: empty desktop/mobile left; populated desktop left/right and mobile right; partial desktop/mobile right; read-only desktop/mobile right; non-contract desktop/mobile; validation desktop/mobile; expanded exact amount on mobile. Also inspected populated Bacaan Meter desktop-right, grouped Umum mobile-right and utilities desktop-left (17 captures total). No blocking new visual defect found in inspected states; title-case headers, conditional inactive dates, partial values, error summary/cell, spacing and tabular/accounting amounts remain readable. Mobile category cells wider than the viewport require horizontal scrolling; snapshot endpoints do not certify every column at once. Remaining captures, pending-save/loading, physical devices, screen readers and owner-local financial acceptance were not independently reviewed here. Long helper text/table density remain candidates for the consolidated refinement. No filtered-empty state applies. This is not a UI-complete or production-readiness claim.

Merge and local update remain pending. No migration/seed or routine server restart is required.

## Batch 037 checkpoint — 16 November 2026

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) merged at `d95b86029a72a77da72e7e0932b6dadeb3f4be3f`. OS24000 Sewaan Umum source editor implements five existing mappings and all 64 approved paths, three-level dependent categories with cascading resets, blank Tanah leaves, distinct broadcasting labels and Berkontrak-only Kitaran-year dates/inclusive day duration. Exact draft preview = Bil. Unit × manual Kadar × manual Bilangan Bulan; dates never set months. No migration/seed/reset, reference/rate/catalogue/mapping/permission/export-schema changes. At the Batch 037 checkpoint, photocopier rental remained separate and unimplemented; Batch 038 below supersedes its implementation status.

Checkout access recovered without changing workflow/credentials/permissions after browser inspection confirmed existing settings were correct; precise transient upstream cause unconfirmed. [CI 43799773291 attempt 2](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`: **510 tests / 5,119 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Later commits changed documentation only. Reviewed 14 rental and three existing-editor captures from 382 PNGs / 22 rental captures; [Batch 037](batch-037-sewaan-umum-spreadsheet.md) records coverage, earlier failures and review limits.

Local sync/financial acceptance remains pending; prior unconfirmed local statuses are unchanged. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement; usability defects fixed as encountered. No routine server restart required.

## Batch 038 checkpoint — 16 November 2026

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. OS24000 Sewaan Mesin Fotostat dan Riso source editor supports the four existing mappings and its independent manual-unit/rate/month calculation. Rental activation atomically activates OS29000 Bacaan Meter; direct meter activation is blocked. Saving each rental creates/updates one linked meter draft. Shared workplace/print/location/current and overall contract dates follow the rental; meter copies, per-copy rate and months remain independent. Explicit linking reviews existing unlinked drafts, retains their financial inputs and replaces only a financially blank generated meter. Rental deletion confirms linked meter values/revision before audited pair removal; deactivation retains both drafts/links for reactivation.

Tested `75809c99be402bf0d0805837286557e2519d247a`; [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), job 133131406062: **521 tests / 5,233 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB, production build, exact-money formatter checks and Chromium passed. Verification and screenshot review limits are recorded in Batch 038. One additive linkage migration is required; no reset/seed or reference/rate/catalogue/mapping/export-schema changes. Local sync/migration/financial/device acceptance pending; earlier unconfirmed statuses unchanged.

OS24000's Program/ Aktiviti entry pathway is recorded; programme association/inheritance remains separate and direct entry remains available. Official outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement. Keep the development server running.

See [Batch 038](batch-038-fotostat-meter-linkage.md) for exact scope, earlier resolved failures, screenshot coverage and local instructions.

## Batch 039 checkpoint — 16 November 2026

[PR #89](https://github.com/muhdmuizz/sistem-abm/pull/89) merged at `874f7bf923fb9179231c38bae524e46b71f141b5`. OS21000 Tuntutan Perbelanjaan Pertukaran source editing supports eight approved category paths/five existing mappings, shared full-SSPA numeric grade lookup, explicit claim-component/reference-location choices, read-only fixed rates and matched-blank manual rates. Mileage preview = Kadar × Jarak; other approved claims = Kadar × 1. Special C/VU7 mapping remains unassumed. Source-only selectors add no canonical categories/export columns. Shared draft/audit/revision/Fasa/role/PTJ guards remain.

Tested `1c150db0d391d9a3c89deae03df9cc2802194fab`; [CI 43834725263](https://github.com/muhdmuizz/sistem-abm/actions/runs/43834725263), job 133188408042: **528 tests / 5,330 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen Pertukaran and one existing-editor capture inspected from 426 PNGs (20 Pertukaran). Batch 039 records resolved fixture failures and visual/manual limits.

No new migration/seed/reset or reference/rate/catalogue/mapping/export-schema changes. Local sync/financial/device acceptance and Batch 038's additive migration remain unconfirmed. Official outputs/totals/completeness, programme inheritance and submission remain deferred; AD-008 remains Draft, Steps 02/03 remain in progress. Complete remaining OS editors before consolidated UI refinement; keep the development server running.

See [Batch 039](batch-039-pertukaran-spreadsheet.md) for source semantics and full verification/local limits.

## Batch 040 checkpoint — 17 November 2026

[PR #91](https://github.com/muhdmuizz/sistem-abm/pull/91) merged at `a45ec936cb67d565e72446729830fd12f96047f2`. OS22000 now uses one retained catalogue identity and two Kategori1 paths: Pertukaran/ Penempatan (family, claim, Lokasi Pertukaran and personnel/reference lookup) and Pengangkutan Barang (manual Tujuan/Kadar). Both exact draft previews = Kadar × 1; inactive fields are cleared/rejected. No missing grade 11, special C/VU7 mapping or absent location is fabricated. Existing source rates and canonical export headers remain unchanged.

Targeted migration removes only the obsolete OS22000 identity's development graph/overrides, retains current/unrelated records and mapping provenance/IDs, increments affected bid revisions and aborts atomically for non-draft Bidaan. Retains all seven existing codes. Catalogue **31 (29 eligible/two excluded)**; mappings **105 (103 eligible)**. Ordinary migration and idempotent OsReferenceSeeder import are required locally; no broad reset or financial seed.

Tested `457cc94cd01d40280edea8fac2a77936d0d01d13`; [CI 44038128200](https://github.com/muhdmuizz/sistem-abm/actions/runs/44038128200), job 133885875932: **537 tests / 5,435 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen OS22000 and one existing-editor capture reviewed from 446 PNGs (20 OS22000); Batch 040 records resolved fixture/count failures and review limits.

Local sync/migration/import/financial/device acceptance remains pending; earlier unconfirmed statuses unchanged. Use Updates separately from Server; no routine server restart. Official outputs/totals/completeness/submission, programme inheritance and export remain deferred. AD-008 stays Draft; Steps 02/03 in progress. Complete remaining OS editors before consolidated UI refinement.

See [Batch 040](batch-040-unified-os22000-spreadsheet.md) for exact cleanup scope and verification/local limits.


## Batch 041 checkpoint — 17 November 2026

[PR #93](https://github.com/muhdmuizz/sistem-abm/pull/93) merged at `cafacd3a86debdd30a0f67fec0e2c9558d21a909`. OS26000 baseline spreadsheets now cover all 42 approved items (3 Pukal / 39 Bukan Pukal), separate OS identities and nine existing activity mappings. Pukal = quantity × derived rate × frequency × months; Bukan Pukal = quantity × derived rate. Read-only units/rates and contextual quantity labels follow the approved unit table; the new unit CSV is its exact machine-readable transcription. Fuel planning assumptions are limited to explicitly approved 2027/2028 contexts; other-year fuel previews are withheld.

Tested `0680c16ec8b92ffccd7839f5abbee2bee6d70274`; [CI 44046082599](https://github.com/muhdmuizz/sistem-abm/actions/runs/44046082599), job 133911984263: **544 tests / 5,705 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Nineteen OS26000 and one existing-editor capture reviewed from 482 PNGs (36 OS26000). See [Batch 041](batch-041-os26000-spreadsheets.md) for the resolved test expectation and review limits.

No new migration/seed/reset, rate/source-CSV or permission-policy change. Local sync and earlier Batch 038/040 migration/import remain pending. New-item proposal governance (shared with future OS27000 supplies), official totals/outputs/completeness/submission, programme inheritance and export remain deferred. AD-008 stays Draft; Steps 02/03 in progress. Keep Server running separately from Updates; complete remaining OS editors before consolidated UI refinement.


## Batch 042 checkpoint — 17 November 2026

[PR #95](https://github.com/muhdmuizz/sistem-abm/pull/95) merged at `ae59695121bf72c1adc061ada09f1693187c513a`. Supersedes Batch 041's two-card layout: one retained OS26000 identity/Anggaran with Pukal/Bukan Pukal selected per row, including mixed rows. Nine distinct Kod Aktiviti mappings and original provenance remain; available paths follow exact current activity mappings (Pendidikan Awal Bukan Pukal only). Approved 42 references, units/rates and two/four-factor formulas are unchanged; inactive multipliers are cleared/rejected. The retained OS permission controls the single card; obsolete overrides are not promoted.

Targeted migration removes only the obsolete OS26000 development identity's draft/source/output graph and overrides, retains canonical/unrelated records and stable non-duplicate mapping IDs, increments affected bid revisions and aborts atomically for non-draft bids. Normal migration plus idempotent OsReferenceSeeder import required; no reset/fresh. Catalogue now **30 (28 eligible/two excluded)**, mappings unchanged **105 (103 eligible)**; earlier counts are historical.

Tested `4113f1985ecd26153ae39e50e39a55a263d933d9`; [CI 44070143813](https://github.com/muhdmuizz/sistem-abm/actions/runs/44070143813), job 133993980538: **548 tests / 5,719 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/full Chromium passed. Reviewed 14 OS26000 and one existing-editor capture from 488 PNGs / 42 OS26000 captures. Batch 042 records the corrected test-route failure and visual/manual limits.

Local sync/migration/import/financial/device acceptance pending; earlier unconfirmed local statuses unchanged. Keep Server separate from Updates; no routine restart. Proposal governance remains the next agreed slice; official outputs/totals/completeness/submission, programme inheritance and export remain deferred. AD-008 remains Draft; Steps 02/03 in progress. Consolidated editor UI refinement follows remaining OS editors.

See [Batch 042](batch-042-unified-os26000-spreadsheet.md) for full evidence and limitations.


## Batch 044 checkpoint — 17 November 2026

[PR #97](https://github.com/muhdmuizz/sistem-abm/pull/97) merged at `721fe9216b9b0d0f84c806f9188599bed7c9c274`. OS27000 Bekalan Alat Pejabat source spreadsheet covers four categories/194 unchanged approved items, dependent Bekalan, automatic read-only Kadar and exact Bil. Unit × Kadar. Preserve the two specifically approved higher duplicate prices and five exact activity mappings; Pakaian Seragam/Touch ’n Go remain separate. No direct-entry purpose/dates/Unit/Kategori3/multipliers. No migration/seed/reset, catalogue/mapping/CSV/rate or official financial output/export changes.

Tested `b096af2cac1885fef4e3252905549f3ec363862b`; [CI 44077277421](https://github.com/muhdmuizz/sistem-abm/actions/runs/44077277421), job 134018439201: **555 tests / 6,183 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/full Chromium passed. Reviewed 12 office-supply and one existing OS26000 capture from 508 PNGs / 20 office captures. Batch 044 records exact coverage and visual/manual limits.

OS26000 purpose removal was explicitly retracted before merge; Batch 043 is withdrawn, no PR/merge, existing OS26000 purpose remains. Owner-approved sequence supersedes immediate-proposal notes: finish remaining OS editors → consolidated spreadsheet UI refinement → shared Cadangan Item Baharu for OS26000 Bukan Pukal / OS27000 office supplies. Programme inheritance and official outputs/totals/completeness/submission remain deferred; AD-008 stays Draft and Steps 02/03 in progress.

Local sync/financial/device acceptance pending; earlier unconfirmed migrations/import remain pending. Keep Server running separately from Updates.

See [Batch 044](batch-044-office-supplies-spreadsheet.md) for full evidence and limitations.


## Batch 045 checkpoint — 18 November 2026

[PR #99](https://github.com/muhdmuizz/sistem-abm/pull/99) merged at `21fe6d34cdbbd7e95d491979530398b258c6df34`. OS15000 Bayaran Perubatan source spreadsheet implements the owner's two explicit approvals: all nine unchanged categories use **Bil. Unit × manual Kadar × Kekerapan × manually entered Bilangan Bulan**; kidney-treatment contract duration uses the existing inclusive calendar years/months/days convention independently of months/amount. Existing Kitaran-year date limits remain; other categories clear/reject dates. Shared Nama/Jawatan/full SSPA Gred, PPN/010804 only; separate Faedah identity. No purpose, Unit, subordinate category or extra overall-contract inputs. Scoped medical select-refresh metadata enables category/personnel cascades without changing unflagged definitions.

Tested `e203d8fe8fab2092608823ce62c6ff70ef4acbe2`; [CI 44219944569](https://github.com/muhdmuizz/sistem-abm/actions/runs/44219944569), job 134519037935: **562 tests / 6,297 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/full Chromium passed. Reviewed 14 medical and two existing-editor captures from 530 PNGs / 22 medical captures. Batch 045 records exact coverage and remaining visual/manual limits.

No new migration/seed/reset, reference/rate/category CSV, catalogue/mapping or official financial output/export change. Local sync/financial/device acceptance pending; earlier unconfirmed migrations/import remain pending. Keep Server separate from Updates. Programme inheritance, official totals/completeness/submission and proposals remain deferred. Sequence remains remaining OS editors → consolidated UI refinement → shared Cadangan Item Baharu. AD-008 remains Draft; Steps 02/03 in progress.

See [Batch 045](batch-045-medical-spreadsheet.md) for full evidence and limits.


## Batch 046 — Faedah spreadsheet started (18 November 2026)

Owner approved one named recipient/benefit per row, Kadar × 1 for existing rate modes, with percentage KGT unavailable pending remaining rules. Implement all 13 types and three existing mappings, preserving sources, legacy C gap and fixed/manual/KGT boundaries. No new migration/seed. Verification/merge/local acceptance pending; earlier local sync/migrations/import unconfirmed. [Batch 046](batch-046-benefits-spreadsheet.md).


### Batch 046 — verification completed; merge pending

OS15000 Faedah uses approved Kadar × 1 per named recipient/benefit; fixed grade/HPA/exact job-grade KGT and manual refund/territory modes. Percentage KGT remains unavailable. Tested `6bc52911cf169bd09a5da6ac1aad7fa321b7ea89`, run 44224595873/job 134534547451: 570 tests / 6,665 assertions, PHP 8.4.27/PostgreSQL 18.7 isolated database, build/exact-money/Chromium passed. Initial helper visibility collision corrected before tests ran; see batch record. Artifact 14393180199: 552 PNGs/22 benefits; five individual captures reviewed, remaining captures not individually reviewed. No new migration/seed; local acceptance pending.


## Batch 046 implementation checkpoint — 18 November 2026

OS15000 Faedah spreadsheet merged in [PR #101](https://github.com/muhdmuizz/sistem-abm/pull/101) at `0a09e8fab62ee6962d395b5175d2a67bf6d40f5a`: one named recipient/benefit, exact Kadar × 1, prescribed grade rates, manual Yuran/territory rates, HPA and exact job-grade fixed KGT. Percentage KGT remains unavailable pending salary basis/rounding. Tested `6bc52911cf169bd09a5da6ac1aad7fa321b7ea89`, run 44224595873/job 134534547451: 570 tests / 6,665 assertions plus build/exact-money/Chromium on PHP 8.4.27/PostgreSQL 18.7 isolated database. Five captures individually reviewed; remaining captures and local financial/device acceptance unverified. No new migration/seed or CSV changes; local sync pending. AD-008 stays Draft, Steps 02/03 in progress; official financial integration and programme inheritance remain pending. See Batch 046 development record for failed initial test-helper attempt and presentation limits.


## OS14000 decision checkpoint — 18 November 2026

Owner resumed OS14000 discussion and approved hourly Kadar × Jam sehari × Bilangan Hari × Kekerapan, independently entered chargeable days, unrounded hourly-rate calculation and half-up rounding of each final amount to two decimals. See consolidated OS14000 reference. Granular expansion/reconciliation and remaining field rules are unresolved; no editor implementation or new tests in this documentation checkpoint. Baseline `6ddeb013b90e19289edd3ba9845194499f3d31d5`; Batch 046 application merge/test/local status unchanged (local sync/acceptance remains unconfirmed). No migration/seed/source CSV change. Documentation PR/merge pending; no new numbered application batch started.


## Owner-approved single-row output — 18 November 2026

After clarification, the owner confirmed keeping **one entry and one export/output row per saved officer-level Butiran Anggaran**, retaining Jam sehari, Bilangan Hari and Kekerapan as multipliers. Do not expand days × occurrences into daily rows. Example: Ahmad, 3 hours/day, 2 days and frequency 12 remains one row representing 72 annual hours. No individual daily entries or fabricated occurrence dates are required. Each separate saved Butiran retains its own stable identity even when the same officer is named more than once; no deduplication by officer.

Apply the approved unrounded hourly-rate calculation and round the final row amount once to two decimals, half-up. This resolves the prior output-expansion/count and associated daily-rounding question for this model; no daily allocation/reconciliation is needed. Preserve existing canonical field identities and programme/purpose references. Remaining detailed field validation, programme inheritance and official calculation/export integration are not implemented or newly approved by this documentation checkpoint. AD-008 remains Draft.


## Superseding owner approval — monthly frequency and months (18 November 2026)

The owner explicitly approved reconciling the active Bilangan Bulan field with the annual amount by defining **Kekerapan as occurrences per month**:

**Anggaran Setahun = (Gaji Median × 12 ÷ 2504) × Jam sehari × Bilangan Hari setiap kejadian × Kekerapan sebulan × Bilangan Bulan.**

This supersedes the earlier four-factor amount formula and annual-frequency interpretation recorded above; those earlier approvals are historical only. Bilangan Bulan is an independent manual calculation input, not informational and not derived from dates. The repeated-work pattern has the same entered hours/day and days/occurrence for each entered monthly occurrence. Example: 3 hours/day × 2 days/occurrence × 1 occurrence/month × 12 months = **72 annual hours**, multiplied by the unrounded hourly rate.

Retain one saved officer-level Butiran per input row and one attributable output/export row containing the factors; do not expand into daily rows or deduplicate separate entries naming the same officer. Keep exact arithmetic through the hourly-rate division/multiplication and round the final row amount once to **two decimals, half-up**. Display-only grouped/tabular two-decimal money must not become the calculation basis. Dates remain independent of chargeable days and months, subject to existing Kitaran-year limits. Preserve approved personnel and programme/purpose references. No new eligibility, caps or rounding rules.

This checkpoint updates documentation only. Source-editor implementation, remaining field rules, programme inheritance and official calculation/export integration remain pending; AD-008 remains Draft.


## Batch 047 started — 18 November 2026

Decision [PR #103](https://github.com/muhdmuizz/sistem-abm/pull/103) merged at `177917021ab60596f97ae6832d0858b56f9515d3`: monthly Kekerapan × manual months, final-only half-up rounding and one officer-entry/output row. Implement OS14000 source editor only; no official outputs/export. See [Batch 047](batch-047-overtime-spreadsheet.md). Verification/merge/local acceptance pending; prior local status unchanged.


## Batch 047 verified; merge pending — 18 November 2026

Tested `e699bda84f7e44c27bc364e36440eea21764c73d`, CI44269481026/job134682192468: 578 tests/7,020 assertions, PHP8.4.27/PostgreSQL18.7 isolated database, build/exact-money/Chromium passed. Artifact14410989701:572 PNGs/20 overtime; reviewed11 overtime+one medical capture. Initial failed enum/fixture attempt, intermediate successful head and presentation limits recorded in Batch047. No new migration/seed; prior local acceptance/migration/import status unchanged.


## Batch 047 merged checkpoint — 18 November 2026

[PR #104](https://github.com/muhdmuizz/sistem-abm/pull/104) merged at `01c48e99ced8db7c41374ccadb3dc56ca2a97fda`. OS14000 source spreadsheet uses the approved salary-derived hourly rate and monthly hours/day × days/occurrence × frequency/month × manual months. Exact numerator before division, final two-decimal half-up amount, independent Kitaran-year dates, four existing mappings, scoped programme/two fixed-purpose choices and dependent full-SSPA personnel. One saved entry per officer record; no daily expansion or generated financial outputs. Official outputs/totals/completeness/submission/export, context classifications and full programme inheritance/association remain pending.

Verified `e699bda84f7e44c27bc364e36440eea21764c73d`, CI44269481026/job134682192468: **578 tests /7,020 assertions**, PHP8.4.27/PostgreSQL18.7 isolated testing DB; build/exact-money/Chromium passed. Artifact14410989701:572 PNGs/20 overtime; 11 overtime and one medical capture individually reviewed. Batch047 records exact failures, intermediate success and presentation/manual limits. No migration/seed/source CSV change. Local sync/financial/device acceptance and prior pending migration/import remain unconfirmed. AD008 stays Draft; Steps02/03 in progress. Keep Server running separately from Updates; remaining editors → consolidated UI refinement → shared proposals.


## OS21000 travel decision checkpoint —18November2026

Owner approved one officer/component per row and monthly claim formulas/manual fare modes. Record in travel applicability reference and AD008. Documentation only; editor implementation pending remaining category/duration decisions. Baseline `fdc0a0faa48ff5442999f74d470f53bbca2b54dc`; Batch047 merge/578tests/7,020assertions and local-sync/financial/device status unchanged (unconfirmed). No new numbered implementation batch, migration/seed or source CSV change. Decision documentation PR/merge pending.


## Owner-approved row structure and formulas — 18 November 2026

Owner approved the proposed structure/formulas for all three separate travel variants (Umum, Sukan, Kokurikulum): **one named officer and one claim component per source row**. An officer claiming meals, accommodation and vehicle mileage has three separate rows, each with a stable independent Butiran identity. Do not derive individual claim quantities from programme headcounts or silently merge rows by officer.

| Component | Annual amount | Rate mode / quantity meaning |
| --- | --- | --- |
| Makanan dan Minuman | Reference rate × Claimable Days × Trips per Month × Months | Existing grade/location reference rate; manually entered claimable days per trip |
| Penginapan | Reference rate × Nights × Trips per Month × Months | Existing grade/location reference rate; manually entered nights per trip |
| Elaun Perjalanan Kenderaan | Reference rate × Distance per Trip × Trips per Month × Months | Existing supplied reference rate, distance applies only to vehicle mileage |
| Bas/ Teksi | Manually entered fare per trip × Trips per Month × Months | Manual fare, no distance factor |
| Bayaran Tambang Kereta Api | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |
| Bayaran Tambang Kapal/ Bot | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |
| Bayaran Kapal Terbang | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |

Kekerapan means trips per month; Bilangan Bulan is the month multiplier. Bas/ Teksi and vehicle mileage remain under unchanged Kategori1 **Elaun Perjalanan, Bayaran Tambang Bas dan Teksi**, distinguished by a component selector; the supplied 1.00 reference is used for mileage, while the separately approved manual fare applies to Bas/ Teksi. No CSV value is overwritten or reclassified as zero. These approvals supersede only prior unresolved row/formula/manual-fare behaviour, not all remaining field applicability.

Preserve the six Kategori1 labels, three distinct Anggaran identities, existing mappings and all83 source rows including blank rates and special C. No C→VU7 or other absent-grade/rate mapping is approved. Two-decimal grouped/tabular money is display formatting, not a new financial rounding policy. OS14000's specific half-up rounding approval is not automatically extended to travel.

Still unresolved: mapping the approved component selector to canonical Kategori2 versus a source-only field, Kategori3 applicability, inclusive date-duration convention and its independence from claimable days/nights, detailed conditional applicability for day/night factors, and any eligibility/caps or missing reference match policy. No application implementation or source CSV change in this checkpoint; full programme inheritance/official integration remains pending. AD008 stays Draft.


## Owner-approved component mapping and duration —18November2026

Owner explicitly approved both remaining proposals: Kategori2 is conditional, with **Elaun Perjalanan Kenderaan** or **Bas/ Teksi** only under existing Kategori1 **Elaun Perjalanan, Bayaran Tambang Bas dan Teksi**; Kategori2 is unused/blank for the other five categories. **Kategori3 is Inactive**. Preserve all six Kategori1 labels. Tempoh is inclusive calendar days (end minus start plus one), informational and independent of manually entered claimable days, accommodation nights and months. Dates remain subject to existing Kitaran-year constraints. Bilangan Hari is contextual: claimable days for meals, nights for accommodation; no extra day/night multiplier on mileage/fares. The latest dated approvals supersede earlier unresolved category/duration/formula status only; no new rates, C mapping, entitlement/caps or financial rounding policy. Official integration/programme inheritance and implementation verification remain pending.


## Batch048 started —18November2026

Decision PR106 merged at `9f85069a144429d34d0f2ea06bdc7922f8f00f59`. Implement three separate OS21000 travel source spreadsheets using approved component rows/monthly formulas, conditional Kategori2 and inclusive duration independent of manual days/nights/months. Verification/merge/local acceptance pending; see Batch048. Prior local status unchanged; no new migration/seed.


## Batch048 verified; merge pending —18November2026

Tested `9eebfba5da255e5908fa3368221e91d74d62d0e7`, CI44338401373/job134921288935:586 tests/7,484 assertions, PHP8.4.27/PostgreSQL18.7 isolated testing DB; build/exact-money/full Chromium passed. Final artifact14442019034:594 PNGs/22 travel;11 travel+one Pertukaran capture reviewed. Earlier failures/intermediate success/hint correction and visual/manual limits are in Batch048. No new migration/seed/CSV change; local acceptance and earlier migration/import statuses unchanged.


## Batch048 merged checkpoint —18November2026

[PR107](https://github.com/muhdmuizz/sistem-abm/pull/107) merged at `77219f10ccedf872c13e3ec4b087469d0de498b0`. Separate Umum/Sukan/Kokurikulum source editors share83 unchanged rate rows and exact PPN010801/010803/010803 mappings. One named officer/component per source; approved monthly formulas, conditional vehicle/Bas-Teksi Kategori2, inactive Kategori3, manually entered day/night/distance/month factors and independent inclusive Kitaran-year duration. No special C or absent shared-grade mapping guessed. Exact manual fares retained; no new financial rounding policy. Pertukaran remains separate/unchanged.

Verified `9eebfba5da255e5908fa3368221e91d74d62d0e7`, CI44338401373/job134921288935: **586 tests /7,484 assertions**, PHP8.4.27/PostgreSQL18.7 isolated testing DB, build/exact-money/full Chromium passed. Artifact14442019034:594 PNGs/22 travel;11 travel+one Pertukaran capture individually reviewed. Batch048 records failed/intermediate runs, hint correction and visual/manual limits. No migration/seed/CSV/catalogue change; local sync/financial/device acceptance and prior pending migration/import remain unconfirmed. Official totals/outputs/completeness/submission/export, programme inheritance/context integration and unresolved special-grade policies remain pending. AD008 stays Draft; Steps02/03 in progress. Keep Server running separately from Updates. Consolidated spreadsheet UI refinement follows baseline OS editors, then shared proposals.
