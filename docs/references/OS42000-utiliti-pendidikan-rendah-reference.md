# OS42000 — Utiliti (Pendidikan Rendah)

**Owner-confirmed:** 12 November 2026. **Scope:** reference reuse, not application implementation.

- **Aktiviti Kewangan:** Pendidikan Rendah.
- **Kod Aktiviti:** `030228` (as recorded in the existing activity-to-OS mapping).
- **Kod Objek:** `42000`.
- **Existing mapped Butiran Perbelanjaan:** Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK).
- **Template:** reuse the [OS23000 — Perhubungan dan Utiliti reference](OS23000-perhubungan-utiliti-reference.md), restricted to **Pukal** only.
- **Kategori1:** `Pukal` (fixed).
- **Kategori2:** `Air`, `Elektrik`, `Telefon/ Faks`, `Pembetungan`.
- **Kadar:** mandatory **user-entered** for each applicable selection. No prescribed rates.

Read the four Pukal entries from [OS23000 reference CSV](OS23000-perhubungan-utiliti-kategori.csv). Do not create a duplicate item dataset. **Bukan Pukal** does not apply to this OS42000 reference.

The existing Pendidikan Menengah OS42000 mapping is not changed by this decision. No changes are made to application code, database or existing financial formulas.
