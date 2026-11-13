# Batch 005 — PTJ Permission Configuration & Resolution Foundation

Baseline: `4352dc4` on `dev`. Authority: REF-05 category defaults, per-PTJ overrides and effective-access dependencies; REF-04 source mappings/exclusions; AD-011 default-deny and server-side authorisation boundaries. This is a bounded implementation batch, not a new Architecture Decision.

## Storage and interpretation

`tetapan_akses_ptj` stores one explicit boolean setting per target + Aktiviti Kewangan + optional OS reference. A target is exactly one category (`JPNT`, `PPD`, `SMK PTJ`) or one actual PTJ. A null OS identifies an activity-level setting; a non-null OS identifies that OS's setting within the selected activity. These are configuration records, not financial hierarchy entities. PostgreSQL foreign keys, target/category checks and null-aware unique identity prevent missing parents, ambiguous targets and duplicate settings.

Category defaults and individual overrides are independent per setting. An explicit true or false PTJ override takes precedence. With no individual override, resolve the current PTJ category default. Removing an override represents reset to inheritance; it does not delete reference or financial data. Changing a category default affects inheriting PTJ without overwriting explicit overrides.

No setting is different from an explicit false. `AksesPeruntukan::tetapanAktiviti` and `tetapanOs` return null for unconfigured settings. The boolean eligibility methods permit access only when the applicable setting is explicitly true. This default-deny behaviour does not manufacture stored OS disable/default assignments.

OS eligibility additionally requires its activity to be effectively enabled, a source mapping for that activity/OS and a non-excluded catalogue reference. An enabled activity never automatically enables all OS. Disabling an activity masks the OS without deleting its configuration. POL and AMal remain reference-only exclusions even with true category/individual settings. OS identity uses the catalogue ID (Kod Objek + canonical Butiran Perbelanjaan), preserving distinct same-code variants.

The read-only resolver reloads current PTJ/category/settings on every evaluation, with no persistent privilege cache. It resolves PTJ configuration only. It does not authenticate users, grant role permissions, replace workplace-scoped authorisation or assert an OS wizard is ready.

## Initial defaults

`AktivitiPermissionDefaultSeeder` creates the 15 REF-05 category/activity defaults: five activities for each of JPNT, PPD and SMK PTJ. All are initially enabled except Pendidikan Awal and Pendidikan Rendah for SMK PTJ. Missing reference activities abort the import before writes. Repeated runs retain modified defaults, individual overrides, OS settings and unrelated configuration. No individual PTJ overrides or OS defaults are seeded. DatabaseSeeder is unchanged.

## Verification and boundaries

Tests cover the exact initial matrix, unconfigured versus false states, true/false overrides, category inheritance and reset, isolation by category/PTJ/activity/OS variant, activity masking and restoration, immutable reference exclusions under permission toggles, source-mapping requirements, repeat-import preservation, missing references and database constraints. Actual PostgreSQL CI evidence is recorded in the PR.

No public route, API, admin mutation service, role/user assignment or financial operation is added. Test fixture writes demonstrate the storage/resolution semantics, not an exposed administrative interface. Future Superadmin controls must authorise and audit configuration mutations in the same transaction under AD-005/AD-011. Future financial operations must recheck this configuration together with role, workplace, Kitaran/Fasa and transaction state server-side; this batch does not certify those future controls or production readiness.

Approved architecture/reference specifications, source catalogue/mappings, rates/formulas, UI and export contracts are unchanged.

## Local synchronisation after successful CI and merge

From a clean `dev` working tree, pull with fast-forward only, clear cached configuration and verify the actual PostgreSQL database is `sistem_abm`. Run `php artisan migrate`, followed by `php artisan db:seed --class=AktivitiPermissionDefaultSeeder`. Requires Batches 001–004 references already imported. No fresh/reset migration or OS selection is required.
