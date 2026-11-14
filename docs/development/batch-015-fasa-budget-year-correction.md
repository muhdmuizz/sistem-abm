# Batch 015 correction — Fasa dates independent of budget year

Status: implementation prepared; CI and merge pending. Local migration/acceptance pending.

Authorised by user on 14 November 2026: bidding for Kitaran 2028 starts in 2026. Baseline dev `b1deaf7bd92dd7d2a3c2e921c514a69782e9e524`.

AD-006 corrected accordingly. Remove year equality from phase create/edit/extend validation, date inputs and extension controls. Add forward migration replacing only the date constraint, preserving order and parent-year FK. Existing records, scopes, status, revisions and audit history remain untouched. Rollback restores the old constraint only if all records satisfy it; incompatible records are never rewritten/deleted and PostgreSQL transactional DDL rejects the rollback atomically.

Regression cases cover 2026 opening for budget 2028, cross-year edits/extensions, real date-window access, invalid dates/reversed order and parent-year integrity. Existing permissions, audit/stale-state tests remain. No Program/ Aktiviti date-rule change, OS calculation work or new batch scope. Browser visual review pending; existing layout retained with one explanatory sentence.
