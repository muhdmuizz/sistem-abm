# Batch 050 — Cadangan Item Baharu

Implements the owner-approved shared proposal workflow for OS26000 Bukan Pukal and OS27000 Bekalan Alat Pejabat. Existing categories and OS permissions continue to govern entry. The central proposal registry keeps the original proposal, reviewer, approved rate and decision history. Approved rows serve as published references, while source CSVs retain their original values.

Draft sources use stable proposal IDs within the existing category fields. Pending items can be selected by the proposer and display temporary rates and amounts. On approval, reopened drafts resolve the same ID to the approved rate. Rejected items cannot be selected for new entries and existing entries must be removed or corrected. The review action and publication are atomic and audited.

Official Anggaran/Bidaan submission and generated outputs are outside this batch and are pending in the application. Wire submission validation to reject unresolved proposal IDs and recalculate approved rates as part of the formal submission implementation.

Requires the additive migration. Baseline: `bd39dd3fe679163bb3c8e005045474e0faffa5c2`.
