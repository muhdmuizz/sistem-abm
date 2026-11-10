# AD-004 — Anggaran Wizard Architecture

**Status:** Baseline reka bentuk dipersetujui (boleh diperhalus semasa pembangunan)  
**Skop:** Pengalaman Penyedia Anggaran dan interaksi ruang kerja Anggaran/Butiran Anggaran  
**Rujukan:** AD-001, AD-002, AD-003

## 1. Tujuan

Mewujudkan satu ruang kerja Anggaran yang konsisten merentas OS tanpa memaksa semua OS menggunakan medan, formula atau aliran input yang sama. Dokumen ini ialah baseline permulaan, **bukan** spesifikasi UI atau wizard OS yang dimuktamadkan.

## 2. Perjalanan Penyedia Anggaran

1. Log masuk dan buka **Bida Peruntukan**.
2. Lihat kad Bidaan bagi Aktiviti Kewangan yang dibenarkan mengikut PTJ dan skop akses. Kad belum aktif dipaparkan malap.
3. **Penyedia Bidaan** mengaktifkan Bidaan; Penyedia Anggaran tidak boleh mengaktifkan atau menghantar Bidaan semata-mata kerana peranannya.
4. Buka Bidaan aktif dan lihat katalog OS yang dibenarkan, termasuk OS **Belum Aktif** yang dipaparkan malap.
5. **Penyedia Anggaran** mengaktifkan OS yang diperlukan. Pengaktifan mewujudkan rekod Anggaran (OS), berasingan daripada status kelengkapannya.
6. Buka ruang kerja OS, masukkan dan pinda Butiran Anggaran melalui editor khusus OS; simpan draf secara berperingkat.
7. Semak kelengkapan, pengiraan dan jumlah. Setelah semua syarat OS dipenuhi, statusnya **Lengkap**.
8. Kembali ke Bidaan untuk melihat kesediaan keseluruhan. **Penyedia Bidaan** menghantar apabila sekurang-kurangnya satu OS aktif lengkap dan semua OS aktif berstatus Lengkap.

## 3. Common Anggaran Workspace

Empat kawasan fungsi yang konsisten:
- **Maklumat** — konteks Kitaran, PTJ, Aktiviti Kewangan dan OS, serta maklumat khusus OS.
- **Butiran Anggaran** — senarai rekod granular, penambahan dan pindaan melalui editor khusus OS.
- **Semakan** — hasil validasi, ralat, kelengkapan dan jumlah yang dikira.
- **Log Tindakan** — jejak perubahan data, status, pengaktifan dan tindakan pengguna.

Empat kawasan ini **tidak semestinya empat tab tetap** atau wizard langkah demi langkah. Navigasi, susun atur dan pembahagian ruang boleh disesuaikan selepas ujian sebenar.

## 4. Interaksi Butiran Anggaran

- **Tambah Butiran:** buka editor OS khusus untuk merekod input sumber.
- **Simpan Butiran:** sahkan input, simpan rekod dan kira semula nilai terjejas.
- **Pinda:** buka rekod sedia ada bersama input asal; selepas disimpan, jana semula nilai atau baris terbitan yang berkaitan.
- **Salin:** boleh dipertimbangkan untuk memudahkan entri berulang; rekod baharu mesti mempunyai identiti tersendiri.
- **Buang:** tindakan yang dibenarkan perlu disahkan dan direkod dalam audit; jangan hilangkan sejarah yang perlu dikekalkan.
- **Senarai:** paparkan rekod dengan carian, penapisan dan pagination apabila jumlah data besar.
- **Kembali:** kembali ke senarai tanpa kehilangan perubahan yang telah disimpan.

Satu input wizard boleh menghasilkan **satu atau banyak baris Butiran Anggaran**. Pengguna mengubah input sumber, bukan mengubah amaun terbitan secara bebas. Jumlah Anggaran dikira daripada data granular yang sah.

## 5. Pengiraan, penyimpanan dan integriti

- Medan, kadar, kelayakan, formula, kaedah pengisian dan bilangan baris berbeza mengikut OS.
- Pengiraan semula hanya menyentuh data berkaitan dan mengemas kini jumlah serta status kelengkapan.
- Penyimpanan perubahan dan pengiraan terbitan hendaklah atomik/transactional supaya tiada rekod kewangan separa dikemas kini.
- Log Tindakan menyimpan identiti pengguna, masa, tindakan dan perubahan relevan.
- Lindungi pindaan serentak daripada menimpa perubahan pengguna lain tanpa disedari.
- Draf boleh disimpan sebelum lengkap; pengaktifan OS tidak bermaksud OS sudah lengkap.
- Nyahaktifkan OS sebelum penghantaran tidak memadam data; data dikecualikan daripada jumlah dan semakan Bidaan, dan divalidasi semula apabila diaktifkan.
- Selepas penghantaran, Bidaan dan OS terlibat baca sahaja. Jika dikembalikan untuk pembetulan, semua OS aktif boleh dipinda; penghantaran semula menghasilkan snapshot kewangan baharu. Ikut AD-003.

## 6. OS14000 sebagai kes rujukan awal

OS14000 (Elaun Lebih Masa) digunakan untuk menguji fleksibiliti seni bina: input pegawai, jawatan, gred, tujuan, tempoh dan kadar; satu set input mungkin menghasilkan banyak baris granular. **Contoh nama, amaun dan formula dalam mockup terdahulu hanyalah ilustrasi**, bukan formula kewangan yang diluluskan. Perincian sebenar OS14000 dan OS lain akan ditentukan semasa pembangunan.

## 7. Perkara yang sengaja belum dibekukan

- Sama ada editor menggunakan paparan sebaris, panel, dialog atau halaman penuh bagi OS kompleks.
- Medan dan formula tepat bagi setiap OS, termasuk sumber kadar.
- Susun atur desktop/mudah alih, interaksi bulk dan import Excel.
- Bentuk akhir paparan dan kaedah navigasi kawasan fungsi.

Perincian ini boleh diperhalus tanpa menambah cabang keputusan seni bina yang tidak diperlukan.

## 8. Prinsip kesinambungan

AD-004 melaksanakan hierarki **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran** daripada AD-002 dan pemisahan kuasa serta peraturan penghantaran daripada AD-003. Tiada **Bakul Bidaan**. Nama dalaman Anggaran dan paparan OS merujuk entiti yang sama.
