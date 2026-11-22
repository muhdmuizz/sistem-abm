# Batch 050 — Cadangan Item Baharu

Implements the owner-approved shared proposal workflow for OS26000 Bukan Pukal and OS27000 Bekalan Alat Pejabat. Existing categories and OS permissions continue to govern entry. The central proposal registry keeps the original proposal, reviewer, approved rate and decision history. Approved rows serve as published references, while source CSVs retain their original values.

Draft sources use stable proposal IDs within the existing category fields. Pending items can be selected by the proposer and display temporary rates and amounts. On approval, reopened drafts resolve the same ID to the approved rate. Rejected items cannot be selected for new entries and existing entries must be removed or corrected. The review action and publication are atomic and audited.

Official Anggaran/Bidaan submission and generated outputs are outside this batch and are pending in the application. Wire submission validation to reject unresolved proposal IDs and recalculate approved rates as part of the formal submission implementation.

Requires the additive migration. Baseline: `bd39dd3fe679163bb3c8e005045474e0faffa5c2`.

## Batch 050 merge and verification

[PR #111](https://github.com/muhdmuizz/sistem-abm/pull/111) merged into `dev` at `3ee2b3d628c0b91eef4ec7b9207c1625b9b1e94a`. Tested PR head `e3f1323e1beaad04c786c1bbcaa6964266bb1f38` passed **587 tests / 7,512 assertions** on PHP 8.4.28 / PostgreSQL 18.7 against isolated `sistem_abm_testing`, plus the build and Chromium layout/screenshot checks ([CI job](https://github.com/muhdmuizz/sistem-abm/actions/runs/44994826476/job/137160018564)). The local attempt stopped at PHP's memory limit. The user confirmed local sync and the additive migration on 22 November 2026. The user subsequently confirmed that the Cadangan Item Baharu form opened locally. Local submission, approval and rejection were not verified.
