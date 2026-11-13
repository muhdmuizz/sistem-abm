# Batch 002 — Organisational Reference Data

**Status:** Completed; reconstructed progress record, 13 November 2026.  
**Baseline:** `4388220ed5b4631461991117ba7962bd30ddab7d` on `dev`.  
**Merge:** [PR #2](https://github.com/muhdmuizz/sistem-abm/pull/2), `89f9cbfad93f61f040d5b140f79d8885778f5aea`.

Implemented KodAktiviti and its migration, OrganisationalReferenceSeeder and import tests. The original approved source inventory was 69 PTJ, 5 named Aktiviti Kewangan and 37 Kod Aktiviti. Import validation/upserts preserve leading zeroes and unrelated records. The two SMK PTJ parent-district corrections to Kuala Nerus are covered by tests. No permission or financial transaction layer was introduced.

The PR required local runtime verification. The supplied 13 November handover reports four syntax checks passed, successful migration/seeding, 16 tests / 47 assertions, and a clean local `dev` tree at `89f9cbf`. These are recorded as handover-reported local results; no original raw console log or dedicated successful CI run for this initial checkpoint has been independently recovered. Later verified CI includes the organisational reference tests.

**Subsequent inventory correction:** [PR #5](https://github.com/muhdmuizz/sistem-abm/pull/5), `4352dc4`, adds owner-confirmed Kod Aktiviti `010102` for Subsidi Yuran TASKA under PPN. The current inventory is 38 codes. Preserve the original 37-code count as historical, not current.

Local synchronisation was completed per the supplied handover; the later TASKA correction was also user-confirmed locally. See [the development register](README.md) for current evidence and boundaries.
