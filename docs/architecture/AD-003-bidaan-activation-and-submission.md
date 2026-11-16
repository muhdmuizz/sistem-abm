# AD-003 — Pengaktifan Bidaan dan Kesediaan Penghantaran

**Projek:** Sistem ABM  
**Tarikh:** 10 November 2026  
**Status:** Dimuktamadkan — tujuh keputusan diluluskan; perincian wizard OS boleh diperhalus semasa pembangunan  
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

## Keputusan tambahan diluluskan — Penguncian, pembetulan dan Log Tindakan

- Selepas **Hantar Bidaan**, Bidaan dan Anggaran yang disertakan menjadi **baca sahaja**.
- Apabila penyemak **mengembalikan Bidaan untuk pembetulan**, **semua Anggaran aktif** dibuka semula untuk suntingan oleh Penyedia Anggaran yang diberi kuasa, bukan hanya OS yang disebut penyemak.
- Setiap perubahan penting direkodkan dalam **Log Tindakan** supaya dapat dikenal pasti siapa mengubah apa dan bila, termasuk rekod/medan terlibat serta nilai sebelum dan selepas untuk perubahan data kewangan.
- Log meliputi peristiwa peringkat Bidaan (pengaktifan, penghantaran, pemulangan, penghantaran semula, keputusan penyemak) dan Anggaran (pengaktifan/nyahaktif, tambah/pinda/buang butiran, pengiraan semula dan perubahan status).
- Penghantaran semula dilakukan oleh Penyedia Bidaan selepas semua Anggaran aktif lengkap dan sah.
- Setiap penghantaran rasmi mengekalkan **snapshot kewangan** dan sejarahnya; pembetulan tidak menulis ganti nilai penghantaran terdahulu.
- Reka bentuk teknikal log bagi data granular yang besar perlu mengelakkan salinan penuh pada setiap ketukan; rekod perubahan berstruktur dan snapshot ketika penghantaran disyorkan.

## Keputusan 7 diluluskan — Ruang kerja Anggaran bersama

- Semua Anggaran (OS) menggunakan **ruang kerja Anggaran bersama** yang konsisten.
- Empat tanggungjawab fungsian dikongsi: **Maklumat**, **Butiran Anggaran**, **Semakan**, dan **Log Tindakan**.
- Ini **bukan** keputusan untuk mewajibkan empat tab tetap atau wizard langkah demi langkah; susun atur dan navigasi boleh diperhalus.
- Setiap OS boleh mempunyai medan input, kaedah kemasukan butiran, peraturan pengiraan, pengesahan dan komponen khusus yang berbeza.
- Penghalusan wizard individu dibuat semasa reka bentuk dan pembangunan OS sebenar, tanpa menambah cabang keputusan AD-003.
- Penyimpanan draf dan pengesahan kelengkapan mesti mematuhi aliran AD-001 dan syarat penghantaran AD-003.

## Perincian pelaksanaan untuk penghalusan kemudian

- Peraturan pengiraan, struktur input, import Excel dan prestasi bagi setiap wizard Anggaran diperhalus semasa pembangunan, bukan keputusan tambahan AD-003.

**Nota:** Contoh status, jumlah OS dan jumlah kewangan dalam lakaran UI terdahulu ialah ilustrasi sahaja, bukan data atau peraturan sistem yang telah disahkan.

## Owner-approved Fotostat dependency and entry pathway — 16 November 2026

OS24000 can also use Program/ Aktiviti as an entry pathway; direct entry is not restricted by that note. Programme linkage/inheritance implementation remains separate.

OS24000 Sewaan Mesin Fotostat dan Riso activates its paired OS29000 Bacaan Meter atomically in the same Bidaan, respecting both permissions/catalogue mappings/Fasa/role guards. Direct meter activation is blocked. Paired deactivation retains drafts and links; explicit reactivation retains source-only drafts. Each saved rental item creates/updates one linked meter item, sharing workplace/category/location/current and overall contract dates. Meter copies, per-copy rate and months remain independent/initially blank. Rental is authoritative for shared details; no automatic financial copying or duplicate creation on repeated saves.

Existing unlinked meter drafts remain separate until explicit user selection/review. Linking preserves chosen meter financial inputs; only a financially blank generated meter may be explicitly replaced and deleted. Deletion of a rental requires confirmation displaying linked meter values and current revisions, then atomically audits/removes both sources/link. Direct copy/removal of linked meters uses rental instead; rental copy creates an independent pair. No name-based matching. Submitted/finalised and generated-output safeguards remain. These approved dependency rules supersede earlier standalone activation/source-ownership boundaries only for this pair; the financial identities/formulas and reference CSVs remain separate.

Implementation/verification is recorded in the Batch 038 checkpoint below; local acceptance remains pending.

## Batch 038 implementation checkpoint — 16 November 2026

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. The approved rental editor and dependency/linkage rules above are implemented and cloud-verified at `75809c99be402bf0d0805837286557e2519d247a`: [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), 521 tests / 5,233 assertions, build/exact-money/Chromium passed. See [Batch 038](../development/batch-038-fotostat-meter-linkage.md) for guard coverage and reviewed/uninspected states. One additive linkage migration and local acceptance remain pending. Programme association/inheritance and official financial outputs/totals/completeness/submission remain deferred; direct entry remains available. No reference/rate/catalogue/mapping/export-schema changes. AD-008 remains Draft; this does not mark the broader AD implemented.
