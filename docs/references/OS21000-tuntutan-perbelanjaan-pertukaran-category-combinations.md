# OS21000 — Tuntutan Perbelanjaan Pertukaran: User-supplied category combinations

**Recorded:** 14 November 2026  
**Authority:** Eight exact rows supplied directly by the system owner in conversation, after identification of a documentation gap.  
**Scope:** Kod Objek 21000, Butiran Perbelanjaan **Tuntutan Perbelanjaan Pertukaran** only. Not OS21000 Perjalanan Sara Hidup (Umum, Sukan, Kokurikulum); not OS22000.

## Original four-field combinations (preserved)

| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah / Berpindah Rumah | Bujang / Keluarga | Jenis Tuntutan |
| --- | --- | --- | --- |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Bujang | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Keluarga | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Tambang Perjalanan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Tambang Perjalanan |

**Exact value semantics:** 1 first-field value; 2 residence-move states; 2 Bujang/Keluarga statuses; contextual Jenis Tuntutan with 1 option for Tidak Berpindah Rumah, and 3 for Berpindah Rumah. There are exactly eight source combinations.

## Unresolved mapping and scope boundary

These are **four input dimensions in the source**, not automatically a confirmed assignment of `KATEGORI1`, `KATEGORI2`, `KATEGORI3`, and an invented `KATEGORI4` export column. The canonical Perbelanjaan Excel structure has 27 columns including KATEGORI1–3, so **mapping of these four source dimensions into canonical columns and/or structured input metadata remains to be decided**. Do not collapse or discard any field.

Do not invent rate reference mappings, financial formulas, extra category choices, eligibility rules or a relationship to OS22000 from these rows.

**Status:** Category combinations documented. Canonical column mapping, applicability and calculation rules still pending. Documentation-only checkpoint; no Laravel/database edits.
