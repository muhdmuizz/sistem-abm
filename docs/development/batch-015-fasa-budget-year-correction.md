# Batch 015 correction — Fasa dates independent of budget year

Status: application CI passed; PR #34 merged at `53d0e45f6c5c2469033f4e6d8333d8cd6b8af588`. Local migration and requested acceptance user-confirmed.

Authorised by user on 14 November 2026: bidding for Kitaran 2028 starts in 2026. Baseline dev `b1deaf7bd92dd7d2a3c2e921c514a69782e9e524`.

AD-006 corrected accordingly. Remove year equality from phase create/edit/extend validation, date inputs and extension controls. Add forward migration replacing only the date constraint, preserving order and parent-year FK. Existing records, scopes, status, revisions and audit history remain untouched. Rollback restores the old constraint only if all records satisfy it; incompatible records are never rewritten/deleted and PostgreSQL transactional DDL rejects the rollback atomically.

Regression cases cover 2026 opening for budget 2028, cross-year edits/extensions, real date-window access, invalid dates/reversed order and parent-year integrity. Existing permissions, audit/stale-state tests remain. No Program/ Aktiviti date-rule change, OS calculation work or new batch scope. Existing browser checks passed; fresh human visual review remains unperformed. Existing layout retained with one explanatory sentence.

User clarification: only Fasa access/opening/closing dates are relaxed. Bidaan and Anggaran dates must stay within the Kitaran budget year (2028 for Kitaran 2028). This patch does not relax financial or Program/ Aktiviti dates or claim implementation of deferred date-bearing OS adapters.

Verified application head `a0985fc9fb9a18688a123052d6998f5e12db7b51`: [CI 43553865130](https://github.com/muhdmuizz/sistem-abm/actions/runs/43553865130), 290 tests / 2311 assertions, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; build and Chromium checks passed. Subsequent changes are documentation only. [PR #34](https://github.com/muhdmuizz/sistem-abm/pull/34). New migration required; keep the running server and apply normal migration after guarded dev pull. Normal upgrade migration and the requested Fasa/activation checks are user-confirmed; rollback on the user's existing database remains unverified.

## User-confirmed local acceptance — 14 November 2026

The user reported **“Clean.”** after the supplied guarded dev pull/database-check/normal-migration/cache-clear commands and the requested local checks: create/save/open a Fasa for Kitaran 2028 starting in 2026; choose Kitaran/PTJ in Bida Peruntukan; activate one Bidaan and one OS; refresh to verify persistence; inspect feedback/spacing and the active-OS preparation-not-yet-open message. Record those bounded checks as user-confirmed. No direct cloud inspection of Windows, audit rows, all roles, stale editors, physical devices or OS calculations is claimed. Earlier security/manual limitations remain tracked. No new application changes or test execution accompany this documentation update.
