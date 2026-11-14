# Sistem Anggaran Belanja Mengurus (ABM)

Laravel application for JPNT's approved ABM architecture. Active development is on `dev` in `muhdmuizz/sistem-abm`; this is the clean rebuild, not the abandoned legacy application.

## Start here

- [Development progress and verified checkpoints](docs/development/README.md) — current implementation state, batch records, test evidence and local synchronisation status.
- [Architecture decisions](docs/architecture/README.md) — the single master AD index; approval of a specification does not imply its implementation.
- [Organisational reference register](docs/references/README.md).
- [Security baseline](docs/security/security-baseline.md) — requirements and verification boundaries, not a production certification.
- [Repository working instructions](AGENTS.md) — required progress-recording and batch close-out procedure.

## Current delivery boundary

Batches 001–007 provide database/reference foundations, PTJ permission resolution, user/role-scope storage and evaluation, and the transactional audit primitive. Batch 008 adds the Google sign-in/shell/read-only reference interface and first local operator setup; the user confirmed local Google login, reference totals, logout/re-login and narrow-window layout checks. Remaining manual checks are tracked separately.

Batch 009 adds audited Pengguna & Peranan registration/status/scope administration, stale-editor protection and all-device ABM session revocation. Its code passed 153 tests / 1143 assertions plus build/browser checks and visual review, and is merged in PR #17. Local migration, operator/add-user form inspection and own all-device logout/re-login are user-confirmed; other administrative mutations and physical-device checks remain unverified locally. Security email/notification operations, financial preparation/review workflows and production readiness remain pending. See the progress register for exact evidence and limitations.

Batch 010 adds audited Pengurusan Aktiviti Kewangan editors for category defaults and individual PTJ activity/OS overrides, inheritance/reset and stale-state protection. It passed 168 tests / 1571 assertions plus build/browser checks and visual review, and is merged in PR #20. Local synchronisation and requested editor inspection are user-confirmed; actual local permission mutations remain unverified; no migration, seed or default OS selection is included.

Batch 011 adds year-only Kitaran and Fasa administration, closed-by-default phases, per-cycle Pindaan numbering, valid dates and configured opening scopes, plus explicit audited open/close/extend/reopen actions. Merged in PR #23 at `6c6e777`; 187 tests / 1730 assertions plus build/browser checks and desktop/mobile visual review passed. Local synchronisation/migration and requested list/year-only form inspection are user-confirmed; actual phase mutations remain unverified. The reported empty-state padding defect remains open, with system-wide spacing requirements clarified in AD-013. Financial integration and archive/delete controls remain outside this slice.

Batch 012 adds canonical Bidaan/Anggaran/Butiran storage and traceable generated outputs, unique identities, same-PTJ workplace foreign keys and exact numeric storage. Merged in PR #26 at `0ff960d`; 206 tests / 1858 assertions plus build/browser regression passed at `6fd0a01`. Local synchronisation/migration is user-confirmed. This batch provides storage; later application capabilities are tracked below. No financial UI or real financial data is introduced.

Batch 013 adds audited backend initial Bidaan/OS activation, distinct preparation-role checks, fresh central/Fasa/session validation, revision conflicts and reasoned Superadmin overrides. Merged in PR #28 at `bf69e80`; 239 tests / 2005 assertions plus build/existing browser regression passed. Local synchronisation/migrations for Batches 012–013 are user-confirmed; financial operations remain unverified locally. Financial UI and the later submission/review/snapshot workflows remain pending; Step 02 is in progress.

Batch 014 adds audited backend Butiran draft creation/editing with stable IDs, old/new workplace authorisation, parent/source revision conflicts and shared financial guards. Merged in PR #30 at `12fc145`; 272 tests / 2178 assertions plus build/existing browser regression passed. Local synchronisation remains pending; no migration. OS field adapters, calculations, generated-row recalculation and financial UI remain pending. These source envelopes do not certify completeness or official amounts; Step 02 remains in progress.

The canonical financial hierarchy remains **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**. Reference and permission tables do not introduce extra financial layers.

## Development safety

The user's Windows installation and PostgreSQL database are separate from cloud CI. Use isolated `sistem_abm_testing` for automated database tests. Do not commit credentials or environment files, access the abandoned legacy project, or run fresh/reset migrations against the development database. Local synchronisation is recorded only after execution evidence or the user's explicit confirmation.

For routine local updates, keep the existing development server running and refresh the browser after pulling/clearing necessary caches. Restart only when the change requires it.

Laravel framework licensing remains MIT; see the installed framework package for its licence.
