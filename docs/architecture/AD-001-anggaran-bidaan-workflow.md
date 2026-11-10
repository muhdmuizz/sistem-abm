# AD-001 — Aliran Kerja Anggaran dan Bidaan

**Projek:** Sistem ABM  
**Tarikh:** 10 November 2026  
**Status:** Dipersetujui (keputusan reka bentuk)

## Keputusan

**Anggaran ialah unit penyediaan kewangan; Bidaan ialah unit penghantaran rasmi.** Anggaran boleh disimpan secara bebas sebelum dihantar. Tiada konsep *Bakul Bidaan*.

## Aliran kerja utama

1. **Cipta Anggaran** — Pengguna memilih OS, mengisi butiran yang berkaitan dan menyediakan pengiraan kewangan.
2. **Simpan Anggaran** — Anggaran disimpan sebagai rekod beridentiti tersendiri. Rekod tidak lengkap boleh disimpan sebagai **Draf** dan dipinda kemudian.
3. **Senarai Anggaran** — Pengguna melihat, menyemak, meminda dan mengurus Anggaran yang disimpan, termasuk mengenal pasti rekod yang layak dihantar.
4. **Hantar Bidaan** — Anggaran yang lengkap dan lulus validasi dihimpunkan untuk penghantaran rasmi di bawah Aktiviti Kewangan yang berkaitan.

## Takrif domain

| Entiti | Takrif |
| --- | --- |
| Program/ Aktiviti | Maklumat program atau aktiviti yang menyokong Anggaran, jika berkenaan. |
| Anggaran | Rekod keperluan kewangan bagi OS tertentu yang boleh wujud sebagai draf sebelum penghantaran. |
| Butiran Anggaran | Baris pengiraan kewangan granular bagi Anggaran. |
| Bidaan | Penghantaran rasmi yang merujuk kepada satu atau lebih Anggaran yang layak. |
| Pindaan | Perubahan terkawal yang mengekalkan sejarah dan nilai terdahulu apabila berkaitan. |

## Peraturan asas

- Simpan Anggaran tidak sama dengan Hantar Bidaan.
- Anggaran tidak lengkap boleh disimpan sebagai Draf, tetapi tidak layak dihantar.
- Tiada Bakul Bidaan atau langkah perantaraan yang tidak diperlukan.
- Anggaran mempunyai identiti kekal; suntingan tidak sepatutnya menghasilkan pendua.
- Butiran pengiraan granular mesti disimpan dan boleh diaudit.
- Penghantaran rasmi mesti mengekalkan snapshot atau versi nilai yang dihantar.
- Pindaan tidak boleh menulis ganti sejarah penghantaran yang telah dimuktamadkan.
- Pengelompokan Bidaan mengambil kira Kitaran ABM, PTJ, Aktiviti Kewangan dan Fasa/ Pusingan; peraturan kardinaliti terperinci akan dimuktamadkan dalam spesifikasi domain.

## Implikasi UI

Navigasi konseptual: **Program/ Aktiviti**, **Cipta Anggaran**, **Senarai Anggaran**, **Bidaan**.

## Skop keputusan

Dokumen ini mengunci pemisahan Anggaran/Bidaan dan aliran kerja penyediaan-ke-penghantaran sahaja. Peraturan OS khusus, matriks peranan, status semakan dan struktur jadual pangkalan data masih perlu dimuktamadkan sebelum pelaksanaan.
