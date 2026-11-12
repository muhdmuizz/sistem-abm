# OS22000 — One OS, two Kategori1 paths

**Owner-confirmed:** 12 November 2026. **Status:** Current authoritative OS22000 classification decision. Reference documentation only; no Laravel/DB or #P7OS implementation.

## Confirmed structure

**OS22000 is ONE Anggaran (OS). Kategori1 has exactly TWO choices:**

| Kategori1 | What follows |
| --- | --- |
| **Pertukaran/ Penempatan** | The previously approved category paths and applicable reference data under Pertukaran/ Penempatan are retained. With the new Kategori1 prefix, the earlier four category levels form **Kategori2–Kategori5**. No new category values are introduced. |
| **Pengangkutan Barang** | **No Kategori2 or subsequent categories.** User must enter **Program/ Aktiviti/ Tujuan** and **Kadar**; both mandatory. **Kadar is user-entered.** |

The **Program/ Aktiviti/ Tujuan** field is a required input, **not another Kategori**. Its option list / input mechanism has not been established. Do not reuse OS14000 selections without approval.

The selection of either Kategori1 path **does not create a second OS identity**, and **does not depend on Kod Aktiviti**. Kod Aktiviti belongs to the applicable Aktiviti Kewangan and must not be used to define, hide or split the two Kategori1 choices.

**Jawatan and Gred are not Kategori fields.** Shared Reference 07 remains available where relevant; **no grade-based Kadar lookup is required for Kategori1 = Pengangkutan Barang**.

## Existing references retained without reinterpretation

- [Pertukaran/ Penempatan eight approved source paths](OS22000-pertukaran-penempatan-kategori.md) and its [CSV](OS22000-pertukaran-penempatan-kategori.csv) remain intact. Source category headings reflect the *earlier* four-level layout; under the current unified hierarchy they are nested after Kategori1 = Pertukaran/ Penempatan.
- [Previous Pengangkutan Barang-barang reference document](OS22000-pengangkutan-barang-reference.md), [126 source rate records](OS22000-pengangkutan-barang-kadar.csv), and [historical Kadar-mode reference](OS22000-pengangkutan-barang-kadar-modes.csv) are preserved for traceability only. They **must not** be interpreted as additional category levels or a prescribed rate lookup for the **new** Kategori1 = Pengangkutan Barang path. Earlier material was discussed in the Pertukaran/ Penempatan context; no wholesale reclassification of individual source rows is assumed.

## Catalogue and mapping boundary

The two labels in the **legacy** master OS catalogue and original Kod Aktiviti mapping must **not** be taken as authority to offer two separately selectable OS22000 entities. The authoritative selection rule is **one OS22000 with two Kategori1 choices**, independent of its valid Kod Aktiviti context.

The legacy mapping values are retained as historical source records rather than overwritten with guessed codes. The canonical display name and complete eventual catalogue import representation remain to be reconciled without changing the confirmed Kategori1 structure. **Do not delay or reopen this two-path decision because of historical mapping differences.**

## Scope

This file supersedes earlier *separate-OS* interpretations. It does **not** modify the original source CSVs, the 131-row shared reference, the 27-column output contract, ADs, application code, PostgreSQL, or the existing master mapping data. Further OS-specific design remains subject to the existing paused #P7OS process.
