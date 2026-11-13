# Sistem Anggaran Belanja Mengurus (ABM)

Laravel application for JPNT's approved ABM architecture. Active development is on `dev` in `muhdmuizz/sistem-abm`; this is the clean rebuild, not the abandoned legacy application.

## Start here

- [Development progress and verified checkpoints](docs/development/README.md) — current implementation state, batch records, test evidence and local synchronisation status.
- [Architecture decisions](docs/architecture/README.md) — the single master AD index; approval of a specification does not imply its implementation.
- [Organisational reference register](docs/references/README.md).
- [Security baseline](docs/security/security-baseline.md) — requirements and verification boundaries, not a production certification.
- [Repository working instructions](AGENTS.md) — required progress-recording and batch close-out procedure.

## Current delivery boundary

Batches 001–007 provide database/reference foundations, PTJ permission resolution, user/role-scope storage and evaluation, and the transactional audit primitive. They do not yet provide the ABM application interface, Google sign-in, complete administration services, financial preparation/review workflows or production readiness. See the progress register for the exact evidence and limitations.

The canonical financial hierarchy remains **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**. Reference and permission tables do not introduce extra financial layers.

## Development safety

The user's Windows installation and PostgreSQL database are separate from cloud CI. Use isolated `sistem_abm_testing` for automated database tests. Do not commit credentials or environment files, access the abandoned legacy project, or run fresh/reset migrations against the development database. Local synchronisation is recorded only after execution evidence or the user's explicit confirmation.

Laravel framework licensing remains MIT; see the installed framework package for its licence.
