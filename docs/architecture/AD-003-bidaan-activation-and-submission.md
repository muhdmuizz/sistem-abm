# AD-003 — Pengaktifan Bidaan dan Kesediaan Penghantaran

**Projek:** Sistem ABM  
**Tarikh:** 10 November 2026  
**Status:** Keputusan reka bentuk dipersetujui; butiran pelaksanaan tertakluk kepada perbincangan lanjut  
**Berkaitan:** [AD-001](AD-001-anggaran-bidaan-workflow.md), [AD-002](AD-002-canonical-financial-data-hierarchy.md)

## Tujuan

Menetapkan pengalaman pengguna dari Bidaan tersedia sehingga penghantaran rasmi, sebelum reka bentuk terperinci wizard Anggaran (OS).

## Keputusan yang dipersetujui

1. Sistem memaparkan **kad Bidaan yang dimmed / Belum Aktif** berdasarkan Aktiviti Kewangan yang tersedia dan dibenarkan untuk PTJ berkenaan.
2. **Penyedia Bidaan** yang diberi kuasa perlu **mengaktifkan Bidaan** terlebih dahulu.
3. Sebelum pengaktifan, **Penyedia Anggaran** tidak boleh menambah Anggaran (OS) atau Butiran Anggaran kepada Bidaan itu.
4. Selepas pengaktifan, **Penyedia Anggaran** yang diberi kuasa boleh menambah dan menyediakan Anggaran (OS) serta Butiran Anggaran.
5. **Semua Anggaran (OS) yang aktif mesti lengkap** sebelum Bidaan boleh dihantar. OS yang belum diaktifkan tidak menghalang penghantaran.
6. Penghantaran berlaku **pada peringkat Bidaan**, bukan secara berasingan untuk setiap OS.
7. Hierarki AD-002 kekal: **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**.

## Aliran kerja pengguna

1. **Bidaan Tersedia** — Kad berdasarkan Aktiviti Kewangan yang dibenarkan, dipaparkan dimmed dan berstatus Belum Aktif.
2. **Aktifkan Bidaan** — Penyedia Bidaan yang dibenarkan mengaktifkan Bidaan.
3. **Penyediaan Anggaran** — Penyedia Anggaran menambah OS dan mengisi butiran granular; rekod boleh disimpan dan disambung.
4. **Semakan Kelengkapan** — Sistem menyemak kesempurnaan semua OS aktif berdasarkan peraturan wizard masing-masing.
5. **Hantar Bidaan** — Penyedia Bidaan yang diberi kuasa menghantar Bidaan setelah semua syarat dipenuhi.

## Prinsip UI dan kebenaran

- Kad Belum Aktif mesti tetap boleh dibaca dan menjelaskan sebab penyediaan belum dibuka.
- Paparan tidak sama dengan kuasa tindakan: hanya pengguna yang diberi kuasa boleh mengaktifkan atau menghantar Bidaan.
- Peranan **Penyedia Bidaan** dan **Penyedia Anggaran** dibezakan; seorang pengguna boleh memegang kedua-duanya jika diberi kuasa.
- Status kelengkapan dan kebenaran penghantaran ditentukan oleh pengesahan sistem di bahagian pelayan, bukan oleh pilihan manual pada UI.
- Tiada Bakul Bidaan; penyediaan OS berlaku terus dalam Bidaan yang aktif.

## Keputusan tambahan diluluskan — Pengaktifan OS dan katalog

1. Penyedia Anggaran **mengaktifkan OS secara eksplisit** sebelum memasukkan Anggaran dan Butiran Anggaran; pengaktifan mewujudkan rekod Anggaran dalam Bidaan aktif.
2. Semua OS yang dibenarkan bagi Aktiviti Kewangan dipaparkan dalam **katalog kad OS**. Kad yang belum aktif dipaparkan dimmed dengan label **Belum Aktif**.
3. OS yang diaktifkan bermula dengan status **Dalam Penyediaan** dan menjadi **Lengkap** apabila data wajib dan pengiraannya lulus pengesahan.
4. OS belum aktif tidak menghalang penghantaran; **setiap OS aktif mesti Lengkap** sebelum Bidaan boleh dihantar.
5. Tidak wajib mengaktifkan semua OS yang tersedia.

## Keputusan tambahan diluluskan — Tiada Bidaan Sifar

- **Bidaan Sifar tidak dibenarkan.** Bidaan perlu mengandungi sekurang-kurangnya satu Anggaran (OS) aktif dan berstatus **Lengkap** sebelum penghantaran.
- Semua Anggaran aktif yang lain juga wajib Lengkap.
- Bidaan aktif tanpa OS boleh kekal dalam penyediaan, tetapi tidak boleh dihantar.
- Syarat ini dikuatkuasakan di pelayan, bukan sekadar keadaan butang UI.

## Keputusan tambahan diluluskan — Pengasingan peranan

- **Penyedia Bidaan** boleh mengaktifkan dan menghantar Bidaan, tetapi tidak mendapat hak mengaktifkan atau mengedit Anggaran secara automatik.
- **Penyedia Anggaran** boleh mengaktifkan OS serta memasukkan dan mengedit Anggaran dan Butiran Anggaran, tetapi tidak mendapat hak mengaktifkan atau menghantar Bidaan secara automatik.
- Seorang pengguna boleh diberikan **kedua-dua peranan**, tertakluk kepada PTJ dan Aktiviti Kewangan yang dibenarkan.
- Kedua-dua peranan boleh melihat status kesediaan penghantaran bagi skop yang dibenarkan.

## Keputusan tambahan diluluskan — Nyahaktif OS secara boleh balik

- Sebelum Bidaan dihantar, Penyedia Anggaran yang diberi kuasa boleh **Nyahaktifkan OS** tanpa memadam rekod Anggaran atau Butiran Anggaran.
- OS tidak aktif dikecualikan daripada jumlah Bidaan dan semakan kelengkapan penghantaran.
- **Aktifkan Semula OS** memulihkan akses kepada data yang telah disimpan untuk disemak atau disunting.
- Pengaktifan semula wajib menjalankan pengesahan terkini; status Lengkap terdahulu tidak dianggap sah secara automatik.
- **Status pengaktifan** dan **status kelengkapan** ialah dua dimensi berasingan dalam model data.
- Pemadaman kekal bukan tindakan pengguna biasa bagi aliran ini.

## Perkara dicadangkan tetapi belum dimuktamadkan

- Polisi kunci selepas penghantaran, pindaan dan penghantaran semula.
- Peraturan pengiraan, struktur input, import Excel dan prestasi bagi wizard Anggaran.

**Nota:** Contoh status, jumlah OS dan jumlah kewangan dalam lakaran UI terdahulu ialah ilustrasi sahaja, bukan data atau peraturan sistem yang telah disahkan.
