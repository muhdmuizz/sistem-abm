# OS27000 — Bekalan dan Bahan-bahan Lain (Touch ’n Go)

**Owner-approved:** 14 November 2026. **Scope:** OS27000 Bekalan dan Bahan-bahan Lain budgeting specification only. Documentation, not Laravel/database implementation.

## Identity and scope

- For budgeting purposes, this Anggaran is **Touch ’n Go**, with no other expenditure categories inferred.
- Preserve existing distinct Kod Aktiviti mappings: **Pengurusan Pentadbiran Negeri 010809**, **Pendidikan Menengah 030304**, and **Pengurusan Asrama 030316**, each under OS27000 and their existing organisational permissions.
- Focus on **financial forecasting**, not Fleetpass enrolment, card operation, reload triggers, invoices, or service renewal mechanisms. No separate catalogue of Touch ’n Go items or reference KADAR is required.

## Canonical Perbelanjaan 30-column applicability

| Status | Column positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 04, 19, 21, 26, 27, 28 | 9 |
| Inactive | 05, 06, 07, 08, 09, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 22, 23, 24, 25 | 19 |
| Deferred | 29, 30 | 2 |

- 01 **OBJEK SEBAGAI** is from the selected Anggaran; 02 **JPNT/ PPD** from the authorised Bidaan PTJ; 03 **SEKTOR/ SEKOLAH** from controlled Tempat Bertugas; 28 **KATEGORI SUMBER PERUNTUKAN** from Bidaan Aktiviti Kewangan.
- 04 **NAMA PROGRAM/ AKTIVITI/ TUJUAN** is Active, displayed as **TUJUAN**, a user-entered description (which may identify a vehicle).
- 19 **BIL. UNIT** is Active, user-entered quantity of vehicles/cards as applicable.
- 21 **KADAR** is Active, **manual estimated monthly expenditure**, displayed as **KADAR BULANAN (RM)**. There is no reference-driven KADAR or imported price.
- 26 **BILANGAN BULAN** is Active, user-entered budgeted duration.
- 27 **ANGGARAN JUMLAH SETAHUN** is Active, calculated.
- 23 **KEKERAPAN** is deliberately **Inactive**, following explicit owner correction. It must not multiply the annual amount.
- 14–17 **KATEGORI1–3 and UNIT** are Inactive; 08–10 are Inactive and blank (reserved for OS24000 Mesin Fotostat); 29–30 are Deferred. Keep the 30 canonical Excel headers unchanged even where the editor uses contextual display labels.

## Approved annual financial formula

**ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR BULANAN × BILANGAN BULAN.**

This is a **monthly budgeting projection**; KADAR is user-entered and should not be interpreted as an automatic reload transaction amount. Illustration only: 3 units × RM200/month × 12 months = RM7,200. Preserve AD-008's exact-decimal calculations, stable Butiran IDs, source-to-output traceability and historical submission snapshots. No additional multiplier, inferred rate, automatic renewal mechanism or rounding policy is approved.

## Scope and change control

This specification does not change OS27000 **Bekalan Alat Pejabat** or **Pakaian Seragam**, their separate references or Kod Aktiviti. The existing source mappings, CSV files, application, database and actual Excel exporter are unchanged. AD-008 remains Draft.
