# Batch 022 — Shared UI Refinement

**Status:** Verification passed in PR #51; merge/local acceptance pending. User approved all seven refinement areas on 14 November 2026.
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

Verified application/test head `9422886677ab9b0198d3047ec3d6ddefc902e621`: [CI 43585733643](https://github.com/muhdmuizz/sistem-abm/actions/runs/43585733643), **410 tests / 3141 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`; frontend build and all existing/new Chromium checks passed. Two HTTP rendering/feedback cases added. [PR #51](https://github.com/muhdmuizz/sistem-abm/pull/51). Test login configured, unconfigured, error and status at standard laptop/desktop and mobile sizes; short/zoom-equivalent navigation and expanded/collapsed sidebar/drawer. Existing synchronous pages do not gain synthetic asynchronous loading screens. Real-device and assistive-technology certification remain unverified.

## Prior local acceptance — Batch 021

The user reported “Clean” after the supplied dev pull/cache clear and requested programme create/save/reopen/edit, dependent objective, headcount and budget-year date checks. Record these bounded checks as user-confirmed; no direct Windows/database inspection or broad financial/role/device acceptance is claimed. The user additionally reported the non-scrollable sidebar, addressed in this batch.

## Delivered implementation and QA evidence

- Sidebar brand/toggle remain outside a flex navigation area with min-height:0 and vertical overflow. Shared navigation is keyboard-focusable; focus outlines stay inset. Collapsed preference, labels and role-scoped links remain intact. Mobile modal drawer has a bounded dynamic viewport height, independently scrollable navigation and reachable close control.
- Login retains the original Google route/configuration/feedback contract. Brand/organisation share a row, the title wraps naturally, duplicate introductory/footer copy is removed and spacing reduced. No authentication behavior, domain restrictions, credentials or identity provisioning changed.
- Shared control/panel tokens, button dimensions, action/pagination wrapping, typography, notices and disabled/processing feedback refined. Cycle/reference/user empty cells receive explicit padding. Programme width restored to 1120px; read-only controls styled consistently and row removal hidden; programme routes retain the Bida Peruntukan active indicator.
- Existing functional/state browser regression passed. Login configured/unconfigured/error/status variants fit without vertical scrolling at 1366×768 and 1280×720; 390×844 and 320×568 remain horizontally contained and may naturally scroll vertically when needed.
- Short 1366×480 expanded/collapsed navigation exercises wheel and keyboard scrolling, visible toggle and restored preference. The 390×420 mobile drawer exercises bounded height, keyboard scrolling and Escape. Empty cells retain at least 16px insets; existing mobile table/card behavior remains.
- The final artifact contains 104 screenshots, including 25 added refinement captures. Visual review sampled representative desktop/mobile login, references, user/role and permission forms, cycle/filtered-user empty states, preparation/Anggaran/history, package/programme validation/read-only and saving states. This is representative state review, not a claim that every screenshot or every production record was manually inspected.
- Saving screenshots use a synthetic submit event against static Blade fixtures to inspect disabled/aria-busy/processing presentation without posting data. Actual persistence/guards continue to be exercised by existing PostgreSQL HTTP/service tests.
- No asynchronous retrieval or new loading flow was introduced. Native full-page navigation remains; future asynchronous skeleton/wizard implementations are still pending their own scope. Actual hardware zoom, devices, assistive technology/contrast certification, real Google acceptance and Windows synchronisation are not independently verified here.
- No migration/seed, source adapter, OS entry/calculation/inheritance, role change or workflow/status change. No architecture approval status is amended. Subsequent evidence commits are documentation only.

## Requested local checks after merge

Guarded dev pull/cache clear and Ctrl+F5 with the server running. Inspect sidebar expanded/collapsed scrolling on a short window, mobile drawer, compact login after logout, and representative forms/tables/empty/read-only states. Local Batch 022 acceptance remains pending until reported.
