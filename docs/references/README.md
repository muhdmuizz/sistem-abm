# Sistem ABM — Organisational Reference Register (confirmed 12 November 2026)

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

## Data limitations / pending verification

- The 60 SMK PTJ codes/names and 69-PTJ source roster were supplied in conversation, but **the full machine-readable PTJ roster is not yet stored in this reference directory**. Do not claim it is imported or seeded.
- The JPNT/PPD Sektor names were supplied in conversation, but **the full machine-readable Sektor roster is not yet stored in this reference directory**.
- The complete school source lacked school codes; check these with an authoritative dataset when available.
- **No application database import, migrations, or system deployment is claimed by this register.**
