# Batch 030 — Searchable PTJ and One OS23000 Card

**Status:** In progress; owner authorised the searchable-selector refinement and reported duplicate OS23000 cards on 15 November 2026, then reaffirmed that Batch 029 already covers both Pukal/Bukan Pukal.
**Baseline:** `5c6ce7d30ee38cde15d6e5856506d0b954859c45` on dev.

## Scope

Replace Bida Peruntukan's separate PTJ search/input and native dropdown with one progressively enhanced searchable combobox over the existing server-scoped options. Keep Kitaran, Papar Bidaan and Set Semula workflow, submitted PTJ IDs, server authorisation and native-select fallback. Support case-insensitive name matching, explicit mouse/keyboard selection, no-results feedback, Escape/Tab/outside close and exact selection validation. No arbitrary typed PTJ, new libraries or changes to other administration selectors.

OS23000 already has one Batch 029 seven-path editor. Remove the older Utiliti Pukal standalone preparation card; show one **Perhubungan dan Utiliti** card. Retain any authorised old Anggaran record under **Rekod sedia ada** inside that card; if only old-identity permission exists, expose only its existing-record access and no canonical activation. Preserve existing read guards/IDs/data/audit, no inferred permission union. Block new activation of the old identity in the UI and transactional service; existing active-record idempotent calls remain non-mutating. Other same-code variants (e.g. OS27000) remain separate.

This corrects presentation and prevents further old-identity activation; it does **not** migrate/combine existing financial records, change catalogue/mapping CSVs, merge roles/permissions or certify official totals/completeness. Underlying old-identity data reconciliation remains pending and is not disguised as completed. No migration/seed, new OS editor, financial formula/rate change, programme inheritance or submission.

Apply AD-013 spacing/title-case/keyboard/mobile rules and preserve tabular accounting amounts. Batches 028/029 local execution remains unconfirmed; the screenshot/question is not a Clean report.

## Verification

Pending isolated PostgreSQL regression, production build, formatter/Chromium and representative desktop/mobile selector/card states, including expanded previous records and native fallback. Record actual tested head/results before merge; local follow-through pending.
