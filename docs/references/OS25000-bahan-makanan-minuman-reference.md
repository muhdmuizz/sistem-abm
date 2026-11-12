# OS25000 — Bahan-bahan Makanan dan Minuman: approved reference

**Owner-confirmed:** 12 November 2026. **Scope:** Structured source reference and selected-field behaviour only, not financial calculation or application implementation.

## Category and reference values

**Kategori1 = Jenis Bekalan.** The four supplied items are:

| Kategori1 — Jenis Bekalan | Unit (auto-populated) | Kadar (RM; reference) |
| --- | --- | ---: |
| Air Minuman | Kotak | 15.00 |
| Gula-gula | Balang/ Paket | 15.00 |
| Biskut | Kotak/ Paket | 15.00 |
| Kekacang | Kotak/ Paket | 15.00 |

**Machine-readable file:** [OS25000-bahan-makanan-minuman-kategori.csv](OS25000-bahan-makanan-minuman-kategori.csv).

## Confirmed behaviour

- The user chooses a **Kategori1 — Jenis Bekalan** item.
- **Unit is automatically populated** from the corresponding reference row; the user does not need to select it separately.
- **Kadar is automatically populated** from the matching reference row (RM15.00 for each currently supplied item). Its presence remains mandatory under the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).
- Retain the supplied compound unit labels `Balang/ Paket` and `Kotak/ Paket` as a **single reference value** each. Do not split them into additional categories or infer a unit-choice workflow.
- No **Kategori2** or subsequent category levels have been supplied.

Source formatting note: repeated interior spaces from the pasted table were normalised in the CSV without changing substantive labels or values.

## Scope and limits

- No further items, rates, quantity rules, formulas, eligibility constraints, or calculation assumptions are approved by this reference.
- This does not modify the master Anggaran (OS) catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel, or PostgreSQL.
