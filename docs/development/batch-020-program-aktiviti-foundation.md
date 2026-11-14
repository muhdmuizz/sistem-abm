# Batch 020 — Program/ Aktiviti Foundation

**Status:** Code verification passed and merged; local migration/reference import pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `debac9ce7cf0be0bba80dbf3207ece84ed8e1b29` on dev.
**Authorisation:** User accepted the proposed Program/ Aktiviti foundation and explicitly directed “Okay, let's proceed as planned.” AD-007 is finalised; AD-008 detailed OS design remains paused.

## Scope and authority

AD-007: programme IDs, four-section metadata, progressive drafts, specific workplace context, positive classified headcounts and budget-year dates. Reference 06 supplies the later approved 7/25/44 RPM hierarchy and strict initial objective defaults. OS29000 Pakej reference supplies the four approved component labels only. AD-005/006/011 provide scope, live actor/session/Fasa, audit and concurrency controls. No financial policy, default OS permission or paused OS design is reopened.

Import exact Reference 06 wording and local numbering into database-backed Teras Strategik → Strategi → Prakarsa tables via an idempotent, reference-only RpmReferenceSeeder. JSON is a machine-readable transcription of the committed Markdown; no new official identifiers. Preserve parent-specific repeated numbers; import no user/programme/financial records and do not call the default DatabaseSeeder.

Store programme metadata directly within an actual Bidaan context with a stable unique ID, actual PTJ/Kitaran/year, specific workplace, actual original creator and technical revision. Composite FKs protect bid/PTJ/cycle/year, workplace/PTJ and objective parentage. Programme dates stay inside the budget year even when its Fasa opens earlier. Same programme names remain independent IDs. This is non-financial façade metadata, not a new Anggaran/amount store.

Initial objective chain is mandatory on all saves under Reference 06's explicit defaults (both optionality settings OFF); B–D may remain incomplete in Draf. Future Superadmin optionality controls are not implemented or implicitly enabled. Validate supplied Jenis/Kategori/Peringkat/Kaedah independently. Pakej requires at least one of Penginapan, Makan Minum, Sewaan Dewan, Sewaan Peralatan; non-Pakej cannot retain hidden package components. This stores package metadata only; automatic OS selection/locking awaits AD-008 and no public programme workflow is enabled.

Both headcount groups may remain absent in a draft, but every retained row must have a permitted category, classification/note and positive integer quantity. Jemputan allows only Pegawai/AKP. Peserta keeps Pegawai and Guru separate, AKP, Murid Rendah, Murid Menengah and Lain-lain. Grade ranges follow AD-007 (9+, 1–8); year/form 1–6; Lain-lain requires Catatan and no classification. Totals are exact PostgreSQL-derived strings, never separately entered/stored or financial amounts. Numeric storage limits are technical, not new business caps; no arbitrary programme/row cap.

## Services and integrity

Internal DrafProgramAktiviti::simpan accepts explicit Bid/Program IDs, selected workplace, expected Bid/Program revisions, a strict metadata envelope and optional reason. Metadata is a full replacement on edit, not a patch: future callers must send all retained values/lists. The service resolves ownership from the actual Bidaan, preserves programme identity/creator, updates child rows without rewriting unchanged row identities, and audits old/new metadata atomically with parent revisions. Name/number duplicates do not merge. Semantically unchanged state is a no-op only after authority and revision checks.

Use shared financial actor/lock/Fasa/central-activity guards with distinct Penyedia Anggaran authority; require active editable Bid (Dalam Penyediaan/Dikembalikan). Check both previous and selected workplace on moves. Superadmin keeps actual identity and reasoned central/Fasa overrides, without bypassing protected workflow, dates or reference integrity. Audits are program.draft_created/updated and do not create submission versions. Failed audit/write restores programme, component/headcount rows and Bid revision.

RuangProgramAktiviti scopes queries before counts/read access using exact current activity/role/workplace assignments. PTJ-level prep/Bid prep/monitor grants can read permitted workplaces; own-workplace prep remains limited to its actual registered assignment. Reviewer-only roles do not acquire preparation access.

ProgramAktiviti::kekuranganMaklumat reports missing metadata only; an empty result is not programme/OS financial completeness. No stored status, total, submission version or output is created.

## Boundaries

Backend storage/reference/draft validation only. No public programme form/route, OS editor or source-entry unlock, programme OS checklist/autolocking, programme–Butiran financial association, inheritance/recalculation, completeness engine, deletion/submission/snapshot/review or global audit viewer. Those depend on subsequent authorised integration and verified OS specifications. Existing source drafts/outputs/permissions/users are not migrated or seeded.

One forward migration and the explicit reference seeder are required locally. No server restart or destructive fresh/reset is part of the handoff. Prior security/device/production limitations remain tracked. These tables/services do not certify the completed four-section user interface.

## Verification and handoff

Verified application head `99bdfa69d03e31a7f0b0994dcc0203dfda337b84`: [CI 43579320487](https://github.com/muhdmuizz/sistem-abm/actions/runs/43579320487), **393 tests / 3003 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`; frontend build and all existing Chromium checks passed. Fifty-one programme-foundation cases added; one existing storage rollback regression updated for dependent migration order. [PR #46](https://github.com/muhdmuizz/sistem-abm/pull/46), merged at `97733ab16742d0b2e4a57645a78dd82f9d86c670`. Subsequent evidence edits are Markdown only.

The first run passed all initial new cases but exposed an old storage rollback test that attempted to drop Bidaan while the new restrictive programme FK still existed. The test now reverses dependent programme/workflow migrations first and restores them in forward order; no FK was removed or weakened. Non-scalar/boolean input validation was also hardened and tested before final verification. Added cases cover source-text-exact/idempotent RPM import, repeated local numbering, four-section metadata/derived headcounts, strict objectives/progressive drafts, parent mismatch, identity/no-op/stale retries, budget-year dates versus early Fasa, category/classification/positive quantity rules, exact large totals, package labels without OS mutation, mass assignment, scoped reads/writes, old/new workplace, roles/session/central/Fasa, protected workflows, actual Superadmin, actual PostgreSQL audit failure rollback, direct database corruption rejection and migration down/up in isolation.

No UI source changes; browser results will be existing-screen regression, not a new UI acceptance claim. Local migration/reference import/service operation checks pending; real programme entry remains unavailable until its interface is implemented.
