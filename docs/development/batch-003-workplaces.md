# Batch 003 — Organisational Relationships & Tempat Bertugas Foundation

Baseline: `89f9cbf` on `dev`. Authority: AD-002, AD-004, AD-005, AD-011 and the committed organisational CSV references.

## Implementation boundary

One `tempat_bertugas` reference table represents Sektor/JPNT subordinate organisational entries, non-PTJ schools and SMK PTJ workplaces. Each has a stable database ID and a required PTJ foreign key. `jenis` distinguishes `sektor`, `sekolah_non_ptj` and `smk_ptj`; it is a reference classification, not a role or financial hierarchy. `TempatBertugas::sektor()` selects the organisational subset. Parent deletion is restricted; workplace identity is unique within parent PTJ and type.

The approved dataset produces 15 JPNT organisational entries, 56 PPD Sektor entries, 448 non-PTJ schools and 60 SMK PTJ workplaces: 579 total. A non-PTJ school belongs to its source PPD. An SMK PTJ workplace references its own PTJ; its existing district association is retained. This does not grant PPD financial authority over an SMK PTJ. Pusat Kokurikulum Negeri is retained as a JPNT organisational entry. Generic JPNT and PPD workplaces are not seeded. No institutional school codes are fabricated.

`TempatBertugasReferenceSeeder` requires Batch 002 PTJ records to exist and match the approved source. It validates source parents and identities before writing within a transaction. Repeated imports preserve existing workplace IDs and unrelated records. No default production seeding is added to DatabaseSeeder.

Authentication, user workplace assignments, role/OS permissions, financial persistence, per-Butiran workplace enforcement, UI selectors and filters are outside this batch. Reference queries do not confer access. Future protected application operations must apply the approved server-side scope rules.

## Verification and synchronisation

GitHub Actions runs the application test suite using PHP 8.4 and an isolated PostgreSQL 18 service with `sistem_abm_testing`. Workflow credentials are disposable CI values, not development credentials. The workflow explicitly checks database isolation before running tests.

The new tests cover inventory and parent relationships, individual PPD school counts, self-workplace identity for SMK PTJ, the two Kuala Nerus corrections, excluded generic workplaces, stable IDs on repeat import, preservation of unrelated records, rejection of missing parents before import, same-named sectors across PPD, and database constraints.

Cloud source CSV audit passed. PHP syntax, migrations and Laravel tests must be reported from actual runtime/CI results; writing this document does not certify them.

After successful tests and merge, local synchronisation requires a clean `dev` checkout, fast-forward pull, confirmation of the `sistem_abm` development database, then `php artisan migrate` and `php artisan db:seed --class=TempatBertugasReferenceSeeder`. No fresh/reset migration is required.

The older missing-roster statements in `docs/references/README.md` predate the committed PTJ/Sektor CSV files. Those historical statements have not been modified by this batch.
