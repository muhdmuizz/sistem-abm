# Batch 030 — Searchable PTJ and One OS23000 Card

**Status:** Merged; local acceptance pending. Owner authorised the searchable-selector refinement and reported duplicate OS23000 cards on 15 November 2026, then reaffirmed that Batch 029 already covers both Pukal/Bukan Pukal.
**Baseline:** `5c6ce7d30ee38cde15d6e5856506d0b954859c45` on dev.

## Scope

Replace Bida Peruntukan's separate PTJ search/input and native dropdown with one progressively enhanced searchable combobox over the existing server-scoped options. Keep Kitaran, Papar Bidaan and Set Semula workflow, submitted PTJ IDs, server authorisation and native-select fallback. Support case-insensitive name matching, explicit mouse/keyboard selection, no-results feedback, Escape/Tab/outside close and exact selection validation. No arbitrary typed PTJ, new libraries or changes to other administration selectors.

OS23000 already has one Batch 029 seven-path editor. Remove the older Utiliti Pukal standalone preparation card; show one **Perhubungan dan Utiliti** card. Retain any authorised old Anggaran record under **Rekod sedia ada** inside that card; if only old-identity permission exists, expose only its existing-record access and no canonical activation. Preserve existing read guards/IDs/data/audit, no inferred permission union. Block new activation of the old identity in the UI and transactional service; existing active-record idempotent calls remain non-mutating. Other same-code variants (e.g. OS27000) remain separate.

This corrects presentation and prevents further old-identity activation; it does **not** migrate/combine existing financial records, change catalogue/mapping CSVs, merge roles/permissions or certify official totals/completeness. Underlying old-identity data reconciliation remains pending and is not disguised as completed. No migration/seed, new OS editor, financial formula/rate change, programme inheritance or submission.

Apply AD-013 spacing/title-case/keyboard/mobile rules and preserve tabular accounting amounts. Batches 028/029 local execution remains unconfirmed; the screenshot/question is not a Clean report.

## Verification


## Verification — passed before merge

Tested application head `24e40b2cc29b5b0a309ca0d9d0ea91f258808578`; [CI 43674654094](https://github.com/muhdmuizz/sistem-abm/actions/runs/43674654094), job 132692044846: **468 tests / 3842 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, exact money formatter and Chromium regression passed. Four new HTTP tests cover native/scoped selector markup, one-card presentation, blocked old activation without writes, preserved old source/record access, revoked/old-only permission boundaries, inactive older-record messaging and distinct OS27000 variants. Initial browser selector assertions were corrected to exclude Kitaran options/live announcements; a subsequent Blade directive error in inactive-record presentation was corrected before this verified head.

Chromium checks case-insensitive filtering, explicit keyboard/mouse selection, submitted ID, invalid free text, no-results, Escape, no-JavaScript fallback, single OS23000 card and expanded scoped older-record link at 1440/390. Actual HTTP tests exercise server scope/mutation guards; browser fixtures are static, and selector FormData checks do not claim a successful live financial browser save.

Artifact **14145771525** contains 250 PNGs (15 new). Final-head visual review covered all 15 new captures: selector open/filtered/no-results/selected at desktop/mobile, desktop native fallback, and single-card/existing-record collapsed/expanded states at desktop/mobile. Existing grid regression passed; not every old capture was re-reviewed. Inactive-old/old-only-role visual states, physical touch devices and manual screen-reader behaviour were not separately inspected. One scrolled full-page mobile expanded capture includes the fixed offscreen skip-link in the page image; its negative viewport positioning is inferred from existing CSS, not a new selector/card element. No blanket UI-complete/accessibility certification is claimed.

No application edits after tested head. No migration/seed, data/permission merge or new financial formula. Batches 028–030 local execution remains unconfirmed.

## Local follow-through — pending

Keep the server running; clean dev pull --ff-only, php artisan optimize:clear and Ctrl+F5. No migration/seed/build/restart. Confirm PTJ has one searchable selector: type Nerus, select PPD Kuala Nerus (or use arrows/Enter), then Papar Bidaan. Check no-result wording, explicit selection and Set Semula. Within a Bidaan confirm one **OS23000 Perhubungan dan Utiliti** card whose existing editor covers Pukal/Bukan Pukal. If old Utiliti Pukal records exist, expand **Rekod sedia ada**; active ones retain scoped read access and inactive ones display preserved-record wording. Other OS27000 cards remain separate. No financial data migration is performed.


## Merge checkpoint

PR #70 merged at `4458d0ec490de4492e9b6f008dd11615d8322257`. Evidence-only head `4c0a66a16f45d7d189b72ffad9720be180d2027f` preserves the tested application code. This close-out changes Markdown only; no new test run or local confirmation.

## Preservation approach superseded — Batch 031

Owner clarified there is no real data and explicitly rejected maintaining obsolete Pukal test budgets. PR #72 at `8ac02d13c0304a115783fa49190634e0b27812f9` removes this batch's preservation UI/compatibility helpers and supplies targeted deletion of the obsolete identity/test budgets plus canonical source-mapping reconciliation. The searchable PTJ selector remains. Do not reintroduce Rekod sedia ada based on this historical batch scope. Batch 031 local migration remains pending.
