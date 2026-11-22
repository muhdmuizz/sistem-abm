# Batch 051 — Anggaran totals and completeness

Status: owner-approved, implemented and CI-verified; merge pending. Baseline: `b332bf37579f795e06ee88d583156bbf8ff74d81`. Batch 050 application merge: `3ee2b3d628c0b91eef4ec7b9207c1625b9b1e94a`. Merge pending.

## Authority and proposed scope

Follow [AD-003](../architecture/AD-003-bidaan-activation-and-submission.md), [AD-004](../architecture/AD-004-anggaran-wizard-architecture.md) and the approved per-OS decisions in [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md). Start with the reusable server-side calculation and completeness contract for saved Butiran Anggaran. Use OS26000 Bukan Pukal and OS27000 Bekalan Alat Pejabat as the first integration cases because they exercise both approved catalogue rates and Batch 050 proposals.

- Derive amounts from retained source records and applicable approved references. Keep existing precision and rounding rules. Recalculate following save, edit, deletion and reference resolution.
- Report Belum Mula, Belum Lengkap or Lengkap with actionable row-level reasons. Preserve incomplete drafts. An unresolved or rejected item proposal prevents completeness; an approved proposal resolves to its approved rate while preserving source identity.
- Keep activation distinct from completeness. Exclude inactive OS from the current Bidaan total and readiness. Make unsupported active OS explicit so partial coverage cannot imply submission readiness.
- Verify exact totals, partial and empty drafts, approval and rejection, deletion, inactivity and pre-existing saved sources against isolated PostgreSQL. All applicable OS still require subsequent end-to-end verification.

Formal Hantar Bidaan, immutable submission snapshots, Excel export, full programme inheritance and rollout to other OS remain separate implementation work. Open policy details remain governed by the approved references.

The editor summary is scoped to the user's permitted workplaces and evaluates all saved rows in that scope before pagination. OS26000 Pukal does not contribute to this slice; retained Pukal rows prevent a complete status in a mixed Anggaran. No persisted official outputs, schema changes or submission changes.

## Batch 051 CI evidence

Verified application head `c0c064e72839c1a51d88afd852e548751af151b9` in [CI 45011396691](https://github.com/muhdmuizz/sistem-abm/actions/runs/45011396691/job/137174293875): 587 tests / 7,520 assertions, PHP 8.4.28 / PostgreSQL 18.7 against isolated `sistem_abm_testing`, Vite build and Chromium layout/screenshot checks passed. The first run at `bea5402` failed because the installed Brick Math version lacked the decimal formatting method; final head uses the existing exact string formatting. Manual visual review and local acceptance remain pending. The retained-row summary is a read-time calculation scoped to permitted workplaces; it does not represent a whole-Bidaan readiness decision. [PR #115](https://github.com/muhdmuizz/sistem-abm/pull/115) is open; no migration.
