# Batch 051 — Anggaran totals and completeness

Status: proposed scope. Baseline: `b332bf37579f795e06ee88d583156bbf8ff74d81`. Batch 050 application merge: `3ee2b3d628c0b91eef4ec7b9207c1625b9b1e94a`. No implementation or verification has begun.

## Authority and proposed scope

Follow [AD-003](../architecture/AD-003-bidaan-activation-and-submission.md), [AD-004](../architecture/AD-004-anggaran-wizard-architecture.md) and the approved per-OS decisions in [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md). Start with the reusable server-side calculation and completeness contract for saved Butiran Anggaran. Use OS26000 Bukan Pukal and OS27000 Bekalan Alat Pejabat as the first integration cases because they exercise both approved catalogue rates and Batch 050 proposals.

- Derive amounts from retained source records and applicable approved references. Keep existing precision and rounding rules. Recalculate following save, edit, deletion and reference resolution.
- Report Belum Mula, Belum Lengkap or Lengkap with actionable row-level reasons. Preserve incomplete drafts. An unresolved or rejected item proposal prevents completeness; an approved proposal resolves to its approved rate while preserving source identity.
- Keep activation distinct from completeness. Exclude inactive OS from the current Bidaan total and readiness. Make unsupported active OS explicit so partial coverage cannot imply submission readiness.
- Verify exact totals, partial and empty drafts, approval and rejection, deletion, inactivity and pre-existing saved sources against isolated PostgreSQL. All applicable OS still require subsequent end-to-end verification.

Formal Hantar Bidaan, immutable submission snapshots, Excel export, full programme inheritance and rollout to other OS remain separate implementation work. Open policy details remain governed by the approved references.
