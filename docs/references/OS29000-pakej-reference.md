# OS29000 — Pakej: confirmed category reference and Program/ Aktiviti alignment

**Owner-confirmed:** 12 November 2026. **Scope:** Reference design and confirmed change to existing Program/ Aktiviti Pakej component options; no application implementation.

## Kategori1 — Komponen Pakej

| Kategori1 | Kadar |
| --- | --- |
| Penginapan | Mandatory, user-entered |
| Makan Minum | Mandatory, user-entered |
| Sewaan Dewan | Mandatory, user-entered |
| Sewaan Peralatan | Mandatory, user-entered |

- **Kategori1:** the four choices above, in the approved order.
- **Kategori2 onwards:** not required on the provided reference.
- **Kadar:** mandatory user entry for the applicable component; no prescribed rate supplied, no rate formula inferred.
- **Machine-readable reference:** [OS29000-pakej-kategori.csv](OS29000-pakej-kategori.csv).
- **Shared Kadar completeness:** [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).

## Supersession of previous Program/ Aktiviti options

The earlier Program/ Aktiviti design listed **three** package components (`Makan Minum`, `Penginapan`, `Dewan`). The owner explicitly directed: **onward, use these four**.

Therefore, **both OS29000 — Pakej and the Program/ Aktiviti Pakej component selection** must use the four-item list above. `Sewaan Dewan` is the approved label in the new four-option list. **Sewaan Peralatan** is now included.

This changes the component choices, **not** the previously approved rules around Pakej/Bukan Pakej, whether a package triggers OS selection, financial totals, output-field layout, or stored-data migration. Do not invent additional requirements.

## Limits

No Kategori2 or Kategori3, price, unit, quantity rule, or new Excel column is implied. This reference does not affect other OS29000 variants, the master OS catalogue or Kod Aktiviti mappings.
