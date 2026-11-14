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

## Confirmed four-category hierarchy

The system owner explicitly confirms that this Anggaran has **four Kategori levels**:

- **KATEGORI1:** Bertukar Ke Luar Kawasan Pejabat
- **KATEGORI2:** Tidak Berpindah Rumah / Berpindah Rumah
- **KATEGORI3:** Bujang / Keluarga
- **KATEGORI4:** Jenis Tuntutan, dependent on the preceding choices: Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam when Tidak Berpindah Rumah, or Elaun Makan, Bayaran Sewa Hotel/ Elaun Lojing, Tambang Perjalanan when Berpindah Rumah.

**KATEGORI4 is real and approved**, not an invented fourth category. The owner has also approved the following OS21000 Pertukaran-specific export mapping into the existing 27-column Perbelanjaan Excel structure:

| Canonical Excel column | System category | Source values |
| --- | --- | --- |
| 04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN | KATEGORI1 | Bertukar Ke Luar Kawasan Pejabat |
| 11 — KATEGORI1 | KATEGORI2 | Tidak Berpindah Rumah / Berpindah Rumah |
| 12 — KATEGORI2 | KATEGORI3 | Bujang / Keluarga |
| 13 — KATEGORI3 | KATEGORI4 | Dependent Jenis Tuntutan values from the eight approved combinations |

**Applicability confirmed:** columns **04, 11, 12 and 13 are Active** for this Anggaran. The internal KATEGORI1–4 hierarchy remains intact. Do not change canonical Excel header names, introduce a 28th column, or extend this mapping to other OSs.

Do not invent rate reference mappings, financial formulas, extra category choices, eligibility rules or a relationship to OS22000 from these rows.

**Status:** Four-category hierarchy and eight combinations explicitly approved and documented. Export representation for KATEGORI4 approved; other column applicability and calculation rules still pending. Documentation-only checkpoint; no Laravel/database edits.
