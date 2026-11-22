# Batch 049 — Spreadsheet column order

The rollback baseline is [baseline/pre-batch-049-spreadsheet-ui](https://github.com/muhdmuizz/sistem-abm/tree/baseline/pre-batch-049-spreadsheet-ui), verified against `dev` at `4062edded48aa8c703431e0e4c7b82f05f43f398` after Batch 048.

Order the visible wizard columns by context, categories, person and rate determinants, relevant dates, rate, calculation factors, and annual amount. The grouped Pakej and Umum editors keep shared dates with their activity context. Column definitions still control field applicability. Paste follows the visible input order. The canonical AD-008 Excel mapping and source field keys are independent of this presentation order.

If this batch needs to be rolled back after merge, revert its merge commit so subsequent repository history is preserved. The baseline branch identifies the exact pre-refinement source tree.

## Merge and verification

[PR #109](https://github.com/muhdmuizz/sistem-abm/pull/109) merged into `dev` at `eb1198580fda46e3f50061c780ece72a2f8752bf`. The PostgreSQL tests workflow passed at PR head `de871a9acead5ccb0900edf9ebf684b66ebce0c9` ([CI run](https://github.com/muhdmuizz/sistem-abm/actions/runs/44350318319)).

[PR #110](https://github.com/muhdmuizz/sistem-abm/pull/110) moved keyboard, paste and OS-specific guidance into **Catatan** below both editor layouts. It merged at `bd39dd3fe679163bb3c8e005045474e0faffa5c2`. The PostgreSQL tests workflow passed at PR head `3f89125bbe95fb8c06ffabc9698560da3dde86ba` ([CI run](https://github.com/muhdmuizz/sistem-abm/actions/runs/44354324829)).

Neither PR introduced a migration or seed. Local synchronisation and desktop/mobile visual acceptance for these changes remain unconfirmed. Earlier pending migration and reference import status remains as recorded for the respective batches.
