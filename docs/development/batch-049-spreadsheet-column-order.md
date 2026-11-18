# Batch 049 — Spreadsheet column order

The rollback baseline is [baseline/pre-batch-049-spreadsheet-ui](https://github.com/muhdmuizz/sistem-abm/tree/baseline/pre-batch-049-spreadsheet-ui), verified against `dev` at `4062edded48aa8c703431e0e4c7b82f05f43f398` after Batch 048.

Order the visible wizard columns by context, categories, person and rate determinants, relevant dates, rate, calculation factors, and annual amount. The grouped Pakej and Umum editors keep shared dates with their activity context. Column definitions still control field applicability. Paste follows the visible input order. The canonical AD-008 Excel mapping and source field keys are independent of this presentation order.

If this batch needs to be rolled back after merge, revert its merge commit so subsequent repository history is preserved. The baseline branch identifies the exact pre-refinement source tree.
