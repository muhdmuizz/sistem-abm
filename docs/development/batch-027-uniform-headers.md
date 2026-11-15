# Batch 027 — Pakaian Seragam and Consistent UI Headers

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `8a165cb8a39cc94f363e68767fa7b7b0cc805de1` on dev.

## Approved scope

Implement [OS27000 Pakaian Seragam](../references/OS27000-pakaian-seragam-reference.md) in the shared spreadsheet under existing PPN/010808 mapping. Jenis Bekalan = Pakaian Seragam/Kasut; Jawatan is a separate personnel attribute using the single [Reference 07](../references/REF-07-jawatan-gred-gaji-kgt.md), not a category. Reuse the 131-row source and four category/job rates; no Gred/salary/eligibility rule or duplicate personnel dataset. Unit stays Pasang; inputs Bil. Pengisian and Bil. Pasang; exact draft preview = Bil. Pengisian × Bil. Pasang × Kadar. Unsupported prescribed-rate combinations remain incomplete drafts with no fabricated price; partial drafts remain saveable.

Fulfil AD-013 Section 06.1 across existing OS25000/SBK/TASKA/OS28000 and this new definition: actual UI/accessibility header labels in consistent title case (Unit, Kadar, Bil. Anak, Bil. Pengisian, Bil. Pasang), preserving acronyms. Do not alter official Excel headers, machine keys, references or hide bad labels with CSS text-transform. Keep the shared exact-safe accounting presentation.

Retain live access/Fasa/session/revision/audit guards, source identities, duplication/removal and legacy protection. No migration, seeder, reference/catalogue/mapping/permission changes, payroll logic, official output/totals/completeness, programme inheritance or submission.

## Verification

Pending isolated PostgreSQL HTTP/full regression, build/formatter/Chromium tests and desktop/mobile states/header/accessibility inspection. Record tested SHA/counts and evidence limits before merge.
