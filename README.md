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

Batch 009 adds audited Pengguna & Peranan registration/status/scope administration, stale-editor protection and all-device ABM session revocation. Its code passed 153 tests / 1143 assertions plus build/browser checks and visual review, and is merged in PR #17. Local migration, operator/add-user form inspection and own all-device logout/re-login are user-confirmed; other administrative mutations and physical-device checks remain unverified locally. Kitaran/Fasa administration, security email/notification operations, financial preparation/review workflows and production readiness remain pending. See the progress register for exact evidence and limitations.

Batch 010 adds audited Pengurusan Aktiviti Kewangan editors for category defaults and individual PTJ activity/OS overrides, inheritance/reset and stale-state protection. It passed 168 tests / 1571 assertions plus build/browser checks and visual review, and is merged in PR #20. Local synchronisation and requested editor inspection are user-confirmed; actual local permission mutations remain unverified; no migration, seed or default OS selection is included.

The canonical financial hierarchy remains **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**. Reference and permission tables do not introduce extra financial layers.

## Development safety

The user's Windows installation and PostgreSQL database are separate from cloud CI. Use isolated `sistem_abm_testing` for automated database tests. Do not commit credentials or environment files, access the abandoned legacy project, or run fresh/reset migrations against the development database. Local synchronisation is recorded only after execution evidence or the user's explicit confirmation.

For routine local updates, keep the existing development server running and refresh the browser after pulling/clearing necessary caches. Restart only when the change requires it.

Laravel framework licensing remains MIT; see the installed framework package for its licence.
