# Batch 036 — OS29000 Bacaan Meter Mesin Fotostat

**Status:** In progress; owner authorised implementation on 16 November 2026.
**Baseline:** `1ca2ac10e93917a33b6681d4cc3db03363566169` on dev. Batch 035 merged and verified; local sync/financial acceptance remains unconfirmed.

## Approved scope

Implement the existing four distinct OS29000 meter mappings (PPN 010807, Rendah 030221, Menengah 030303, Asrama 030315) in the shared spreadsheet editor. Reuse the existing OS24000 two-choice CSV (Berwarna / Hitam dan Putih); do not merge the financial objects. Authoritative specifications: [meter](../references/OS29000-bacaan-meter-mesin-fotostat-reference.md), [contract periods](../references/OS24000-sewaan-mesin-fotostat-reference.md), AD-008 and AD-013.

Current contract dates stay within Kitaran year. Overall contract dates may span years, as required for Kontrak Keseluruhan. Validate each pair separately; no unapproved containment relation. Calculate inclusive calendar years, months and days independently. Boundary implementation uses complete calendar years clamped to the target month's last day, then complete calendar months with the same clamp, then remaining actual days, against the day after the inclusive end. Verify leap days/month ends and JS/PHP parity. This is calendar presentation, never a source of chargeable months.

Bil. Salinan means monthly copies; manual RM-per-copy Kadar and manual Bilangan Bulan produce exact Bil. Salinan × Kadar × Bilangan Bulan previews. Missing applicable factors withhold preview; partial drafts remain saveable and cannot establish financial completeness. Lokasi Mesin Fotostat is a display label; canonical export fields remain unchanged. Source precision, grouped/two-decimal tabular/right-aligned money and title-case headers apply.

No migration/seed/reset, category/rate/reference/catalogue/mapping/permission changes, OS24000 editor, new mandatory completeness engine, programme inheritance, official output/totals/submission or export schema changes. Existing guards, atomic source save/revisions/audit and read-only scope remain.

## UI sequencing decision

Owner approved completing remaining OS editors before a consolidated editor UI refinement, then pausing before another major module. Fix usability defects as encountered; continue AD-013 during implementation. Refinement remains a future bounded batch, not authority to redesign unfinished workflows here. Existing local confirmations are unchanged.

## Verification

Pending isolated PostgreSQL/full suite/build/formatter/Chromium and representative desktop/mobile empty, populated, partial, read-only and validation review. No filtered-empty state applies (no grid filter). Record actual execution evidence before merge; local update remains pending.
