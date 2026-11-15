# Batch 032 — OS29000 Pakej Source Grid

**Status:** Implementation verified; PR #74 merge/local update pending. User authorised continuation 15 November 2026 after Batch 031 local update.
**Baseline:** `26d464660a91464f6bfb22c8bcddeef2a66f0c1c` on dev.

## Approved scope

Implement the approved [OS29000 Pakej](../references/OS29000-pakej-reference.md) direct source template for canonical **Pakej (Mesyuarat/ Bengkel/ Kursus)** under the five existing mappings: PPN/010810, Pendidikan Rendah/030225, Pendidikan Awal/030104, Pendidikan Menengah/030307, Pengurusan Asrama/030317. Reuse unchanged four-row reference and existing shared spreadsheet.

Four components: Penginapan (Unit Bilik, manual RM sebilik semalam, Bil. Malam), Makan Minum (Orang, manual RM seorang sehari), Sewaan Dewan (Dewan, manual RM sedewan sehari), Sewaan Peralatan (Unit, manual RM seunit sehari). Last three use manually entered chargeable Bilangan Hari. Exact preview = Bil. Unit × Kadar × chargeable nights/days. No automatic prices, per-meal conversion, calendar-to-billable duration, or new quantity/rounding rules.

Direct source fields include purpose/name and Kitaran-year ordered dates, inclusive calendar-day Tempoh display; charged nights/days remain separate and are never overwritten by dates. Shared row-level rate-basis hints and contextual accessible/visible charge labels explain mixed components while the table header remains neutral title-case **Bilangan Hari/ Malam**. Preserve accounting-style tabular/right-aligned grouped/two-decimal display and exact source precision; partial drafts remain saveable.

Retain live role/Fasa/session/revision guards, independent row identity, copy/removal and private legacy protection. No migration/seed, catalogue/mapping/permission/reference CSV changes, automatic programme association/inheritance, programme-trigger rules, official financial outputs/totals/completeness or submission. Existing Program/ Aktiviti four-component choices remain unchanged. The owner-confirmed development-only context does not require new compatibility preservation paths.

## Verification

Tested application head `5dfb64a5fbeab18df512772ec89de6812802ceea`; [CI 43689211251](https://github.com/muhdmuizz/sistem-abm/actions/runs/43689211251), job 132736055950: **480 tests / 4030 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; production build, monetary formatter and Chromium regression passed. JavaScript syntax checks also passed locally. Artifact 14151578355 contains 282 PNGs including 18 Pakej captures.

Eleven final-head Pakej captures visually inspected: empty 1440/390 left; populated 1440 left/right and 390 right; partial 1440/390 right; read-only 1440/390 right; validation 1440/390 right. Comfortable empty-state padding, title-case headers, right-aligned grouped/two-decimal money, separate rate-basis/night/day hints, preserved validation inputs and mobile contained horizontal scrolling checked. At the right edge the leading duration column is partly outside the scroll viewport; it remains available by scrolling left. Remaining artifact captures were generated/automated but not individually visually inspected. No new blocking visual defect found in reviewed states.

Earlier runs 43687933779 and 43688668070 failed the new meal-rate browser assertion because replacing an unfocused formatted input triggered exact-value restoration during Playwright fill. Diagnostic state isolated the interaction; focus-before-fill now matches existing money checks. Shared validation now preserves reference-description IDs alongside error descriptions and the final browser regression verifies this.

### Coverage and evidence boundaries

Six Pakej HTTP regression tests cover the four component/unit/rate-basis definitions, five exact activity mappings, partial drafts, atomic validation failures, Kitaran-year/date ordering, exact manual rates and decimal multiplication, stable row IDs/no-op saves/reopening, stale revisions, closed Fasa/read-only roles and audited removal. Shared official outputs remain empty.

Chromium uses rendered HTML fixtures and intercepts save requests for client validation/payload checks; actual database persistence/authorisation is exercised separately by the HTTP suite. Browser checks cover component changes, nights/day labels and descriptions, date-independent charged duration, manual rates, exact multiplication, duplication and validation recovery. Money editing focuses before replacement because the shared focus handler restores the exact source value.

Final-head screenshots are reviewed separately below. No physical-device, screen-reader, authenticated local Google session or local financial acceptance is claimed. Loading/network-loss states and expanded precise-value disclosures are not newly visually reviewed in this batch; existing shared behaviour remains, and passing regression is not a complete UI/readiness certification.

**Merge/local status:** PR #74 merge pending; no migration/seed required. Local Batch 032 synchronisation/financial acceptance pending. Batch 031 supplied local update/targeted cleanup migration is user-confirmed, without certifying unexecuted financial/role/device tests.
