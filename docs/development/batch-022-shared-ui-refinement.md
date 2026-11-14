# Batch 022 — Shared UI Refinement

**Status:** Audit consolidated; implementation starting. User approved all seven refinement areas on 14 November 2026.
**Baseline:** `0e4914c7d62221a460c296039d3318f949bb02bc` on dev. AD-013 is authoritative; existing workflows/roles/financial rules remain unchanged. No migration/seed or resumed AD-008 work.

## Consolidated audit before changes

Reviewed current source and the Batch 021 CI screenshots for login, references, user/role forms, central permissions, cycles/Fasa, preparation catalogue, programme forms/list and Anggaran/history states.

| Area | Finding | Bounded refinement |
| --- | --- | --- |
| Sidebar | Fixed flex shell has no bounded scrolling navigation; short displays hide lower entries. Mobile drawer uses minimum full viewport height without a bounded content region. | Independent keyboard/wheel-scroll navigation; keep brand/toggle/close usable; preserve remembered collapse and drawer semantics. |
| Login | Brand, organisation, forced title break, introduction, secondary heading, explanatory notes and footer stack with large margins. | Compact single-column sign-in panel, natural title wrapping, concise hierarchy, comfortable standard laptop fit; allow natural scrolling on small screens/errors. |
| Spacing | Cycle empty cell inherits zero padding; panel/alert/form insets and section gaps vary. | Shared tokens and explicit padded empty cell; consistent responsive insets without reducing readability. |
| Typography/navigation | Programme routes lack parent active state; several headings/actions wrap awkwardly. | Preserve parent navigation, consistent title/control line height and wrap handling. |
| Buttons/actions | Ordinary controls differ in height; action rows/pagination can crowd/wrap; disabled programme row removal remains visually present. | Consistent control dimensions/focus, responsive action gaps, quieter disabled/read-only presentation. |
| Forms/tables | Programme max-width is invalid (`11var(--space-5)`); some number/read-only controls are inconsistent; dense form rows have excess vertical spacing. | Valid constrained width, comfortable field/row rhythm, numeric alignment and mobile management cards retained. |
| Interface states | Empty/validation/success/read-only states need consistent insets and wrapping. | Refine shared notices and read-only styles; regression screenshots and targeted browser assertions. No invented financial status or new loading requests. |

## Verification and acceptance

Pending: isolated PostgreSQL suite, build, Chromium fit/scroll/keyboard/responsive checks and visual review of applicable existing states. Test login configured, unconfigured, error and status at standard laptop/desktop and mobile sizes; short/zoom-equivalent navigation and expanded/collapsed sidebar/drawer. Existing synchronous pages do not gain synthetic asynchronous loading screens. Real-device and assistive-technology certification remain unverified.

## Prior local acceptance — Batch 021

The user reported “Clean” after the supplied dev pull/cache clear and requested programme create/save/reopen/edit, dependent objective, headcount and budget-year date checks. Record these bounded checks as user-confirmed; no direct Windows/database inspection or broad financial/role/device acceptance is claimed. The user additionally reported the non-scrollable sidebar, addressed in this batch.
