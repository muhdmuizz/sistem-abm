# OS21000 — Perjalanan Sara Hidup (Umum, Sukan dan Kokurikulum)

**Recorded:** 12 November 2026. **Status:** User-approved reference and classification decisions; not implementation.

## OS variant labels

The legacy Excel workbook uses **three separate sheets**. The user approved treating **Umum**, **Sukan** and **Kokurikulum** as distinguishing **Anggaran (OS) labels**, **not Kategori1 options**:

| Kod Objek | Label Anggaran (OS) | Kategori1 |
| --- | --- | --- |
| 21000 | Perjalanan Sara Hidup (Umum) | Kategori Tuntutan |
| 21000 | Perjalanan Sara Hidup (Sukan) | Kategori Tuntutan |
| 21000 | Perjalanan Sara Hidup (Kokurikulum) | Kategori Tuntutan |

This follows Reference 04's identification of Anggaran (OS) by `Kod Objek + canonical Butiran Perbelanjaan`. The three OS variants may refer to the **same 83-row rate reference**, without duplicating reference values for each variant. **Do not add a required Umum/Sukan/Kokurikulum dropdown as Kategori1.** The three-sheet arrangement is a legacy workbook detail, not a mandate to duplicate the rate catalogue.

## Revised user reference

**Authoritative supplied revision:** User-provided revised 4-column table on 12 November 2026, superseding the earlier legacy table.

**Machine-readable data:** [OS21000-perjalanan-sara-hidup-revised-kadar.csv](OS21000-perjalanan-sara-hidup-revised-kadar.csv) — **83 rows**, preserving all source grade/category/location/rate values, including blank Kadar cells. Internal repeated whitespace in text labels was trimmed for CSV readability, with no semantic label or rate correction.

**Columns:** Gred; Kategori Tuntutan; Lokasi Urusan Rasmi; Kadar.

**Kategori1** corresponds to the distinct `Kategori Tuntutan` labels in the supplied table:

1. Makanan dan Minuman
2. Penginapan
3. Bayaran Tambang Kereta Api
4. Elaun Perjalanan, Bayaran Tambang Bas dan Teksi
5. Bayaran Tambang Kapal/ Bot
6. Bayaran Kapal Terbang

## Approved boundaries and outstanding reconciliation

- Use the **revised values as supplied**. Do not override, 'repair', or augment them based on external interpretation of WP 1.4 without a subsequent user decision.
- Preserve **blank Kadar** cells as blank: no automatic zero, formula, or requirement is approved solely from their presence.
- The user previously approved shared **Jawatan + full SSPA Gred** input where personnel attributes are relevant. The source's special grade `C` is preserved without guessing a mapping.
- Existing `anggaran-os-catalogue.csv` and `kod-aktiviti-anggaran-os-mapping.csv` have Umum/Perjalanan Sara Hidup and Sukan entries, but **a separate Kokurikulum canonical entry and its Kod Aktiviti mapping have not been verified in those committed files**. This is flagged for reconciliation, **not silently added or assigned a guessed activity code**. The standalone reference reflects the user's approved labels.
- No decisions made here about rate formulas, vehicle bands, unit basis, other WP 1.4 categories, eligibility, Laravel, PostgreSQL, or Architecture Decisions.
