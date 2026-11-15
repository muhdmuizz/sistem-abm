# Batch 032 — OS29000 Pakej Source Grid

**Status:** In progress; user authorised continuation 15 November 2026 after Batch 031 local update.
**Baseline:** `26d464660a91464f6bfb22c8bcddeef2a66f0c1c` on dev.

## Approved scope

Implement the approved [OS29000 Pakej](../references/OS29000-pakej-reference.md) direct source template for canonical **Pakej (Mesyuarat/ Bengkel/ Kursus)** under the five existing mappings: PPN/010810, Pendidikan Rendah/030225, Pendidikan Awal/030104, Pendidikan Menengah/030307, Pengurusan Asrama/030317. Reuse unchanged four-row reference and existing shared spreadsheet.

Four components: Penginapan (Unit Bilik, manual RM sebilik semalam, Bil. Malam), Makan Minum (Orang, manual RM seorang sehari), Sewaan Dewan (Dewan, manual RM sedewan sehari), Sewaan Peralatan (Unit, manual RM seunit sehari). Last three use manually entered chargeable Bilangan Hari. Exact preview = Bil. Unit × Kadar × chargeable nights/days. No automatic prices, per-meal conversion, calendar-to-billable duration, or new quantity/rounding rules.

Direct source fields include purpose/name and Kitaran-year ordered dates, inclusive calendar-day Tempoh display; charged nights/days remain separate and are never overwritten by dates. Shared row-level rate-basis hints and contextual accessible/visible charge labels explain mixed components while the table header remains neutral title-case **Bilangan Hari/ Malam**. Preserve accounting-style tabular/right-aligned grouped/two-decimal display and exact source precision; partial drafts remain saveable.

Retain live role/Fasa/session/revision guards, independent row identity, copy/removal and private legacy protection. No migration/seed, catalogue/mapping/permission/reference CSV changes, automatic programme association/inheritance, programme-trigger rules, official financial outputs/totals/completeness or submission. Existing Program/ Aktiviti four-component choices remain unchanged. The owner-confirmed development-only context does not require new compatibility preservation paths.

## Verification

Pending isolated PostgreSQL HTTP/full regression, build/formatter/Chromium and desktop/mobile state inspection. Record tested SHA/counts/evidence before merge. Batch 031 local update and targeted migration are user-confirmed; detailed financial/role/device checks remain unverified locally.
