# OS22000 — Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula

> **Historical reference notice (12 November 2026):** The later [unified OS22000 decision](OS22000-unified-kategori-decisions.md) supersedes this file's former *separate-OS identity and category-level interpretation*. Preserve the data below as supplied; it must not be used to populate the new Kategori1 = Pengangkutan Barang branch automatically. The owner placed the earlier reference material under Pertukaran/ Penempatan. This notice makes no data or calculation changes.

**Recorded:** 12 November 2026. **Status:** User-approved Kategori1–Kategori4 reference. Documentation only; not application implementation.

## Identity

- **Kod Objek:** `22000`
- **Butiran Perbelanjaan:** Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula
- The existing Anggaran (OS) catalogue includes this distinct entry. Do not conflate it with the separately catalogued **OS22000 — Pengangkutan Barang-barang**.

## Four-level category hierarchy

**Kategori1** → **Kategori2** → **Kategori3** → **Kategori4**. The eight approved combinations, in supplied order, are:

| Kategori1 | Kategori2 | Kategori3 | Kategori4 |
| --- | --- | --- | --- |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Bujang | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Keluarga | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Tambang Perjalanan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Tambang Perjalanan |

Machine-readable source: [OS22000-pertukaran-penempatan-kategori.csv](OS22000-pertukaran-penempatan-kategori.csv).

**Structure:** One Kategori1 value; two Kategori2 values (`Tidak Berpindah Rumah`, `Berpindah Rumah`); two Kategori3 values (`Bujang`, `Keluarga`); and four distinct Kategori4 labels in total. Only the eight explicitly supplied paths are allowed by this reference.

## Boundaries

- No Kadar, eligibility criteria, or calculation method was provided. **Do not infer or invent rates or formulas.**
- Retain the original category labels, including the slash typography and combined transport labels.
- This record preserves user-approved reference data; **no changes to the Laravel application, PostgreSQL, architecture decisions, or existing master OS catalogue** are implied.
