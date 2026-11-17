# Sistem ABM — Organisational Reference Register

**Source decisions confirmed:** 12 November 2026. **Implementation status reconciled:** 13 November 2026.

**Authority:** user-supplied PTJ master list, JPNT/PPD Sektor list, complete school list and explicit reconciliations in the 12 November 2026 conversation. This is reference-data provenance and implementation tracking, **not an additional Architecture Decision**.

## Confirmed master inventory

| Group | Count | Notes |
| --- | ---: | --- |
| PTJ | **69** | **1 JPNT** (Kod PTJ `41620101`), **8 PPD**, **60 SMK PTJ**, each PTJ identified by its own Kod PTJ |
| JPNT subordinate organisational entries | **15** | Updated 16-entry list **minus Pusat Kokurikulum Daerah**; **Pusat Kokurikulum Negeri remains** |
| PPD Sektor entries | **56** | 7 named Sektor under each of 8 PPD |
| Non-PTJ schools | **448** | Derived from 516 supplied full-list records, excluding **60 SMK PTJ** and **8 PPD**; see `non-ptj-schools.csv` and `non-ptj-exclusion-audit.csv` |

**Do not** assume every school in the non-PTJ list has a verified institutional code: that list contains parent PPD names and school names, not school-code identifiers. The 448-school classification is derived by exclusion from the supplied 60-SMK-PTJ master list.

### Non-PTJ school breakdown

| PPD | Non-PTJ schools |
| --- | ---: |
| Besut | 67 |
| Dungun | 62 |
| Kemaman | 63 |
| Kuala Terengganu | 64 |
| Marang | 36 |
| Hulu Terengganu | 62 |
| Setiu | 53 |
| Kuala Nerus | 41 |
| **Total** | **448** |

## Confirmed corrections to the SMK PTJ master list

The following **two existing SMK PTJ** belong to **PPD Kuala Nerus**, **not PPD Kuala Terengganu**:

| Kod PTJ | SMK PTJ | Confirmed parent PPD |
| --- | --- | --- |
| `41621601` | SMK Bukit Tunggal | PPD Kuala Nerus (`41621001`) |
| `4162C101` | SMKA Dato' Haji Abbas | PPD Kuala Nerus (`41621001`) |

Their PTJ codes and school names **do not change**, and **neither school** is to be included in the non-PTJ school dataset. The PTJ count stays **69** and the non-PTJ count stays **448**.

## Confirmed organisational/access principles

See the committed clarifications in **AD-002, AD-004, AD-005, and AD-011**:

- **PTJ owns Bidaan** (by PTJ, Aktiviti Kewangan and Kitaran), not Sektor or non-PTJ schools.
- **Sektor** is subordinate to JPNT or PPD, with **no independent Kod PTJ**. It may be a user's organisational identity and a selectable **Tempat Bertugas**.
- **JPNT and PPD themselves are excluded as generic selectable Tempat Bertugas**. Butiran Anggaran must identify a permitted specific workplace.
- **Every Butiran Anggaran** has its independently selected permitted Tempat Bertugas.
- **Penyedia Bidaan** acts at PTJ level. **Penyedia Anggaran** at PTJ level may act across permitted workplaces within its authorised PTJ/Aktiviti Kewangan; at Tempat Bertugas level, access is restricted to that user's own workplace.
- Preserve the existing **Aktiviti Kewangan → PTJ → Tempat Bertugas → Status Anggaran** filter order; do not introduce a Sektor filter or financial hierarchy layer.

## Current implementation and evidence

- [ptj-master.csv](ptj-master.csv) contains the 69-PTJ roster. Batch 002 implemented its reference import with five Aktiviti Kewangan and initially 37 Kod Aktiviti.
- [sektor-master.csv](sektor-master.csv) contains 15 JPNT organisational entries and 56 PPD Sektor entries. Batch 003 implemented their workplace references, 448 non-PTJ schools and 60 SMK PTJ self-workplaces: 579 total.
- The owner-confirmed TASKA correction adds Kod Aktiviti `010102` under PPN; the current code inventory is 38. Batch 004 imports 33 OS catalogue entries and 105 source mappings, retaining POL/AMal as exclusions.
- Category/activity permission defaults and configuration resolution are implemented by Batch 005; no OS default choices or individual PTJ exceptions were selected/seeded.
- Imports and constraints were tested in isolated PostgreSQL CI; local migrations/seeding were user-confirmed. See the [development progress register](../development/README.md) and batch records for exact checkpoints, test counts and evidence provenance.
- The former statements that PTJ/Sektor machine-readable rosters were missing are superseded by the committed files and implemented imports above.

## Data limitations / pending verification

- The complete non-PTJ school source lacks verified institutional codes; check them against an authoritative dataset when available. Do not fabricate codes.
- Reference import does not establish complete financial workflows, authentication, user assignment administration, per-Butiran enforcement or production deployment.
- No production deployment or release/security certification is claimed.

## Programme reference implementation — Batch 020 (14 November 2026)

[Batch 020](../development/batch-020-program-aktiviti-foundation.md) imports the exact [Reference 06](REF-06-teras-strategik-strategi-prakarsa.md) source into database-backed objective tables via the reference-only RpmReferenceSeeder, using [rpm-objectives.json](rpm-objectives.json): 7 Teras Strategik, 25 Strategi and 44 Prakarsa. Local numbering remains scoped to its parent; no new official identifier or user/financial record is generated. CI 43579320487 verifies exact Markdown wording, idempotent IDs and parent relationships. Local migration/import user-confirmed. Initial objective validation stays strict. Batch 021 adds the initial programme form; requested Batch 021 local interface checks user-confirmed. Optionality administration remains deferred.

## Historical OS catalogue reconciliation — Batch 040 (17 November 2026)

The initial Batch 004 inventory above is historical. Following Batch 031 utilities and Batch 040 OS22000 consolidation, the current catalogue has **31 identities: 29 eligible and two excluded**. All **105 source mappings (103 eligible)** remain; seven OS22000 mappings now refer to one retained identity, preserving every existing Kod Aktiviti and original source label. No organisational roster/rate change. [PR #91](https://github.com/muhdmuizz/sistem-abm/pull/91) merged at `a45ec936cb67d565e72446729830fd12f96047f2`; [Batch 040](../development/batch-040-unified-os22000-spreadsheet.md) records migration, import, verification and pending local acceptance.


## Batch 042 implementation checkpoint — 17 November 2026

OS26000 single-card correction merged in [PR #95](https://github.com/muhdmuizz/sistem-abm/pull/95) at `ae59695121bf72c1adc061ada09f1693187c513a`. One retained identity supports Pukal/Bukan Pukal per row, preserving nine distinct activity codes and all 42 approved references/units/rates. Supersedes Batch 041's separate identities. Catalogue 30/28 eligible/two excluded; mappings 105/103 eligible. Tested `4113f1985ecd26153ae39e50e39a55a263d933d9`: 548 tests / 5,719 assertions plus build/exact-money/Chromium. Targeted obsolete development-card migration and normal reference import required; local acceptance pending. Proposal governance and official financial integration remain deferred; AD-008 stays Draft, Steps 02/03 in progress. Evidence and limits: [Batch 042](../development/batch-042-unified-os26000-spreadsheet.md).
