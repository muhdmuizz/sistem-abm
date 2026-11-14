# Reference 06 — Teras Strategik → Strategi → Prakarsa (RPM 2026–2035)

**Status:** Confirmed hierarchy and approved validation configuration; committed reference decision.  
**Source:** User-supplied `senarai_teras_strategi_dan_prakarsa_rpm_2026_2035.txt`.  
**Scope:** Sistem ABM — Program/ Aktiviti.

## 1. Reference hierarchy

The source contains **7 Teras Strategik**, **25 Strategi**, and **44 Prakarsa**. Retain the supplied labels and numbering under their respective parents. A repeated `Strategi 1` or `Prakarsa 1` is not a duplicate when it belongs to a different parent.

The Program/ Aktiviti interface uses dependent selections:

`Teras Strategik → Strategi → Prakarsa`

Selecting Teras Strategik filters Strategi; selecting Strategi filters Prakarsa. Preserve the exact source descriptions. Do not infer additional official identifiers beyond the source numbering.

## 2. Mandatory fields and Superadmin controls

| Field | Initial system behaviour | Superadmin configuration |
| --- | --- | --- |
| **Teras Strategik (TS)** | **Always mandatory** | **No option to permit blank** |
| **Strategi** | **Mandatory by default** | Superadmin may toggle **Allow blank Strategi** on or off |
| **Prakarsa** | **Mandatory by default** | Superadmin may toggle **Allow blank Prakarsa** on or off |

**Confirmed rule:** Teras Strategik **must always have a value**. Only Strategi and Prakarsa may be made optional later through explicit Superadmin-controlled settings. Initially, both of those optionality switches are **off**, so all three fields are required.

- Blank Strategi/Prakarsa values must not be accepted unless the corresponding Superadmin setting permits them.
- When a Strategi or Prakarsa is selected, it must belong to the selected parent in the supplied hierarchy.
- Changing a parent selection invalidates any child choice that no longer belongs to it.
- This is a **form-validation configuration**, not a change to the underlying RPM reference hierarchy.
- Do not automatically enable blank selections, or treat the future ability to allow blanks as an approval to leave either field blank now.
### Approved dependency and optionality behaviour

1. **Teras Strategik is always required.** No Superadmin switch can make it optional.
2. **Allow blank Strategi** is independently configurable by Superadmin. Enabling it **automatically permits blank Prakarsa**; do not require Prakarsa if Strategi is blank.
3. **Allow blank Prakarsa** can be enabled independently while Strategi remains mandatory.
4. **Prakarsa cannot be chosen unless a Strategi has been chosen**; if Strategi is blank under a permitted configuration, Prakarsa must also be blank and its selection control disabled.
5. **Changing Teras Strategik** clears any previously selected Strategi and Prakarsa that are incompatible with the new Teras Strategik.
6. **Changing Strategi** clears the previously selected Prakarsa.
7. **Initial state:** both Superadmin optionality toggles are **OFF**; all three fields are mandatory until explicitly configured otherwise.

| Superadmin optionality settings | Teras Strategik | Strategi | Prakarsa |
| --- | --- | --- | --- |
| Both OFF (default) | Mandatory | Mandatory | Mandatory |
| Allow blank Prakarsa ON only | Mandatory | Mandatory | Optional |
| Allow blank Strategi ON | Mandatory | Optional | Optional when Strategi blank; selection requires a chosen Strategi |
| Both ON | Mandatory | Optional | Optional |

These rules were explicitly agreed by the user. They define **form validation and UI behaviour**, not new RPM reference codes or a different hierarchy.

## 3. Supplied RPM hierarchy (original wording)

```text
TERAS STRATEGIK 1: Memastikan Sistem Pendidikan Malaysia yang Terangkum, Dinamik dan Relevan
- Strategi 1: Meneliti Keberkesanan dan Kecekapan Implementasi Dasar Pendidikan
  * Prakarsa 1: Memperkasakan Pendidikan Sains dan Matematik
  * Prakarsa 2: Memperkukuh Pelaksanaan Dasar Memartabatkan Bahasa Melayu dan Memperkukuh Bahasa Inggeris
  * Prakarsa 3: Mengkaji Semula Dasar Pengambilan dan Penempatan Pegawai Perkhidmatan Pendidikan
- Strategi 2: Memperkasakan Struktur Persekolahan Kebangsaan
  * Prakarsa 1: Memperkasakan Struktur Persekolahan Kebangsaan
  * Prakarsa 2: Menambah Baik Dasar Penawaran Bahasa Tambahan serta Pengoperasian Bahasa Cina dan Bahasa Tamil
- Strategi 3: Memperkasakan Pentaksiran dan Peperiksaan Kebangsaan
  * Prakarsa 1: Memperkasakan Sistem Pentaksiran Pendidikan Kebangsaan

TERAS STRATEGIK 2: Mengoptimumkan Potensi Setiap Murid Melalui Pengalaman Pembelajaran yang Bermakna untuk Menjadi Warganegara Global
- Strategi 1: Reformasi Kurikulum
  * Prakarsa 1: Kurikulum Pendidikan Berorientasikan Kompetensi dan Keperluan Masa Hadapan
- Strategi 2: Meningkatkan Kemahiran Murid
  * Prakarsa 1: Memperkukuh Literasi dan Numerasi Murid Tahap 1
  * Prakarsa 2: Memperkukuh Keterampilan Berbahasa
  * Prakarsa 3: Membudayakan Pembelajaran Sepanjang Hayat untuk Murid
  * Prakarsa 4: Memperkukuh Kemahiran Murid Masa Hadapan
- Strategi 3: Membangunkan Bakat
  * Prakarsa 1: Membangunkan Bakat Murid secara Holistik dalam Pelbagai Bidang STEM, TVET, Sukan dan Seni
- Strategi 4: Membentuk Karakter
  * Prakarsa 1: Memperkasakan Kesejahteraan Murid Merangkumi Kesihatan Fizikal, Mental dan Sosial
  * Prakarsa 2: Memperkukuh Jati Diri dan Kerohanian Murid

TERAS STRATEGIK 3: Mentransformasikan Pendidik agar Berkeupayaan Tinggi dan Berorientasikan Masa Hadapan
- Strategi 1: Meningkatkan Kesejahteraan Pendidik
  * Prakarsa 1: Menumpukan Peranan Guru kepada Pengajaran dan Pembelajaran
  * Prakarsa 2: Memperkasakan Kesejahteraan Pendidik melalui Keseimbangan Holistik
- Strategi 2: Memperkasakan Kompetensi Pendidik melalui Pemberdayaan Pembangunan Profesionalisme Berterusan secara Berstruktur dan Bermutu
  * Prakarsa 1: Memantapkan Pembangunan Profesionalisme Berterusan secara Berstruktur untuk Memastikan Tahap Kompetensi Pendidik Kekal Relevan
- Strategi 3: Mentransformasikan Latihan Keguruan agar Bersifat Adaptif, Dinamik dan Relevan
  * Prakarsa 1: Memperkasakan Latihan Keguruan agar Luwes dan Relevan
  * Prakarsa 2: Memperkukuh Mekanisme Jaminan Mutu Latihan Guru dalam Perkhidmatan
- Strategi 4: Membangunkan Pemimpin Transformatif, Berkeupayaan Tinggi dan Berorientasikan Masa Hadapan
  * Prakarsa 1: Memperkasakan Perancangan Pengganti dengan Membentuk Kumpulan Pelapis Pemimpin Pendidikan yang Berteraskan Nilai dan Berwawasan Sistemik
  * Prakarsa 2: Memperkukuh Fleksibiliti Penempatan Kepimpinan Sekolah Merentas Sekolah Rendah dan Menengah

TERAS STRATEGIK 4: Memantapkan Prasarana Fizikal dan Digital di Semua Institusi Pendidikan KPM
- Strategi 1: Memperkukuh Pembangunan Prasarana yang Terangkum dan Lestari
  * Prakarsa 1: Memperkukuh Pembangunan Prasarana Pendidikan yang Strategik dan Responsif
  * Prakarsa 2: Memantapkan Pengurusan Aset, Penyelenggaraan Proaktif dan Berfasa untuk Kelestarian Prasarana KPM
  * Prakarsa 3: Memperkasakan Prasarana dan Sokongan Pembelajaran Pendidikan Khas
  * Prakarsa 4: Memperkukuh Kemudahan Sedia Ada yang Berfungsi, Terangkum dan Berimpak
- Strategi 2: Memperkasakan Prasarana Digital untuk Pendidikan
  * Prakarsa 1: Mentransformasikan Pengalaman Pembelajaran dengan Inovasi Teknologi
  * Prakarsa 2: Menambah Baik Prasarana Digital di Institusi Pendidikan KPM
- Strategi 3: Menambah Baik Sistem Pintar dan Tadbir Urus Berpandukan Data
  * Prakarsa 1: Memperkukuh Tadbir Urus Pendidikan Melalui Sistem Maklumat dan Data Pintar serta Terhubung

TERAS STRATEGIK 5: Mempergiat Sinergi antara Institusi Pendidikan KPM dengan Pihak Berkepentingan
- Strategi 1: Memperkukuh Dasar Kerjasama antara KPM dengan Pihak Berkepentingan
  * Prakarsa 1: Memperkasakan Dasar Kerjasama Institusi Pendidikan KPM dengan Pihak Berkepentingan
- Strategi 2: Memperkukuh Ekosistem Penyaluran Sumber
  * Prakarsa 1: Memperkukuh Ekosistem Penyaluran Sumber
- Strategi 3: Meningkatkan Pelibatan Pihak Berkepentingan dengan Institusi Pendidikan KPM
  * Prakarsa 1: Mempelbagaikan Mekanisme Pelibatan dan Kerjasama Pihak Berkepentingan

TERAS STRATEGIK 6: Membudayakan Kemampanan dalam Ekosistem Pendidikan
- Strategi 1: Memperkasakan Pendidikan untuk Pembangunan Mampan (PPM) dalam Kalangan Murid
  * Prakarsa 1: Mengintegrasikan PPM dalam Kurikulum dan Kokurikulum
  * Prakarsa 2: Menyediakan Murid dengan Kemahiran Berkaitan Bidang Pekerjaan Lestari
- Strategi 2: Mengupayakan Pendidik dan Pemimpin Pendidikan bagi Menerajui PPM
  * Prakarsa 1: Membangunkan Kapasiti Pendidik dan Pemimpin Pendidikan sebagai Peneraju PPM
- Strategi 3: Meningkatkan Advokasi dan Promosi PPM dalam Ekosistem Pendidikan secara Terangkum
  * Prakarsa 1: Mengoptimumkan Jaringan dan Jalinan untuk PPM
  * Prakarsa 2: Memperkasakan Murid dan Warga KPM sebagai Agen Perubahan Amalan Kemampanan
- Strategi 4: Memperkukuh Pemantauan dan Penilaian Pelaksanaan PPM di Institusi Pendidikan KPM
  * Prakarsa 1: Menilai dan Mengiktiraf Institusi Pendidikan KPM yang Menerapkan Amalan Kemampanan

TERAS STRATEGIK 7: Meningkatkan Kecekapan Tadbir Urus dan Sistem Penyampaian
- Strategi 1: Mengukuhkan Pelan Komunikasi KPM yang Responsif dan Proaktif
  * Prakarsa 1: Memantapkan Pelaksanaan Pelan Strategik Komunikasi KPM yang Berstruktur dan Bersasar
- Strategi 2: Memperkukuh Fungsi dan Struktur Organisasi Bahagian, Jabatan Pendidikan Negeri, Pejabat Pendidikan Daerah dan Institusi Pendidikan KPM
  * Prakarsa 1: Menyusun Semula Fungsi, Struktur dan Perjawatan Bahagian KPM, JPN, PPD dan Institusi Pendidikan KPM
  * Prakarsa 2: Memperkasakan Polisi dan Prosedur Penamatan Pegawai di KPM
- Strategi 3: Meningkatkan Pemberian Autonomi Terpimpin kepada Institusi Pendidikan KPM
  * Prakarsa 1: Memperkukuh Pelaksanaan Autonomi Terpimpin di Institusi Pendidikan KPM
  * Prakarsa 2: Mengoptimumkan Penggunaan Fasiliti dan Prasarana di Institusi Pendidikan KPM
- Strategi 4: Memperkasakan Pengurusan Perubahan dan Risiko
  * Prakarsa 1: Membangunkan Pelan Pengurusan Perubahan KPM
  * Prakarsa 2: Membangunkan Pelan Pengurusan Risiko KPM
```
## Implementation checkpoint — 14 November 2026

Batch 020 adds database-backed reference import, exact parent-FK programme selections and internal draft validation using these confirmed defaults. The approved wording/numbering and optionality policy above are unchanged. The initial implementation requires all three objective selections; progressive draft incompleteness applies to other programme metadata. Superadmin optionality controls are not yet implemented or enabled. [rpm-objectives.json](rpm-objectives.json) is the exact machine-readable source transcription; RpmReferenceSeeder imports it without users or programme/financial rows. Verified by [CI 43579320487](https://github.com/muhdmuizz/sistem-abm/actions/runs/43579320487), 393 tests / 3003 assertions at 99bdfa6; [PR #46](https://github.com/muhdmuizz/sistem-abm/pull/46) merged at 97733ab16742d0b2e4a57645a78dd82f9d86c670. Local migration/import user-confirmed; programme interface remains pending.
