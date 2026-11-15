# Batch 027 — Pakaian Seragam and Consistent UI Headers

**Status:** Merged; local acceptance pending. Owner authorised 15 November 2026.
**Baseline:** `8a165cb8a39cc94f363e68767fa7b7b0cc805de1` on dev.

## Approved scope

Implement [OS27000 Pakaian Seragam](../references/OS27000-pakaian-seragam-reference.md) in the shared spreadsheet under existing PPN/010808 mapping. Jenis Bekalan = Pakaian Seragam/Kasut; Jawatan is a separate personnel attribute using the single [Reference 07](../references/REF-07-jawatan-gred-gaji-kgt.md), not a category. Reuse the 131-row source and four category/job rates; no Gred/salary/eligibility rule or duplicate personnel dataset. Unit stays Pasang; inputs Bil. Pengisian and Bil. Pasang; exact draft preview = Bil. Pengisian × Bil. Pasang × Kadar. Unsupported prescribed-rate combinations remain incomplete drafts with no fabricated price; partial drafts remain saveable.

Fulfil AD-013 Section 06.1 across existing OS25000/SBK/TASKA/OS28000 and this new definition: actual UI/accessibility header labels in consistent title case (Unit, Kadar, Bil. Anak, Bil. Pengisian, Bil. Pasang), preserving acronyms. Do not alter official Excel headers, machine keys, references or hide bad labels with CSS text-transform. Keep the shared exact-safe accounting presentation.

Retain live access/Fasa/session/revision/audit guards, source identities, duplication/removal and legacy protection. No migration, seeder, reference/catalogue/mapping/permission changes, payroll logic, official output/totals/completeness, programme inheritance or submission.

## Verification

## Verification — passed before merge

Tested application head: `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`. [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): **452 tests / 3626 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, exact money formatter and Chromium regression passed. Seven new HTTP tests cover four approved rates, exact source persistence/edit/no-op, partial and unpriced shared jobs, tampering, context mapping, guards/removal and legacy protection.

Chromium asserts actual headers/accessibility labels at 1440/390 across all five configured grids, independent Jawatan retention, rates, accounting display, paste/copy and validation preservation. Actual database persistence is covered by HTTP tests; browser validation responses use static-fixture interception, not a claimed live browser save.

Artifact **14141858050** contains 197 PNGs, including 32 new captures (22 uniform states and ten header captures). Representative visual review covered 16 captures: uniform empty/populated/partial/unpriced/read-only/validation desktop/mobile, populated desktop left/right, TASKA desktop/mobile and maintenance mobile. Internal horizontal scrolling is intentional; no outer overflow in browser assertions. Review confirms padded empty/error/read-only states and right-aligned grouped two-decimal money. Not every artifact capture or physical device was visually reviewed.

No application edits after the tested head. No migration/seed, official financial outputs/totals/completeness or new eligibility policy. Local acceptance remains pending.



## Local follow-through — pending

Keep the server running; from a clean tree switch to dev, pull --ff-only and run php artisan optimize:clear, then Ctrl+F5. No migration/seeder/npm build or restart.

Confirm title-case headers across existing grids: Unit, Kadar, Bil. Anak (RM/OS/PTJ acronyms retained), with grouped/two-decimal amounts unchanged. Open an eligible active PPN OS27000 Pakaian Seragam Anggaran, then Buka Jadual Butiran. Choose Pakaian Seragam + PEMBANTU KHIDMAT AM; 5 pengisian × 2 pasang × RM230 previews **2,300.00**. Changing to Kasut retains Jawatan and gives RM150; PEMBANTU MAKMAL gives RM250. Unit stays Pasang. Check save/reopen/edit/copy/remove and horizontal scrolling. Shared jobs without a prescribed combination show no rate/amount and remain incomplete drafts; no fabricated eligibility/rate policy. Official outputs/completeness/programme inheritance remain pending.

## Merge checkpoint

PR #64 merged into dev at `dffc2c44ebcf50f757cb1588488b6924f0b95c55`. Final evidence-only head `528cd178bf4fcd0d95c84ee2c3e67a7cc0ccdfc5` preserves the tested application code. This close-out changes Markdown only; no new test run or local acceptance is claimed.

## Local confirmation and subsequent correction

User reported “Clean” after supplied sync/cache-clear instructions: record those commands as user-confirmed, not complete financial/role/device acceptance. Owner then required Bil. Pasang fixed at 2. Batch 028 / PR #66 at `496dbfb0d9cb30f982bc02f8470f6b46773b5269` implements that correction; supersedes Batch 027 editable multiplier behaviour. Batch 028 local checks remain pending.
