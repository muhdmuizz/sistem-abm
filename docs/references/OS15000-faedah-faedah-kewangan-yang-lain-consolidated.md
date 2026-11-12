# OS15000 — Faedah-faedah Kewangan Yang Lain: Consolidated Reference

**Recorded:** 12 November 2026  
**Status:** Consolidated from user-provided references and decisions. Reference documentation only, not implementation.

## 1. Anggaran (OS) identity and scope

**Anggaran (OS) = Kod Objek + canonical Butiran Perbelanjaan** (Reference 04). Therefore:

- **OS15000 — Faedah-faedah Kewangan Yang Lain** is the subject of *this* reference.
- **OS15000 — Bayaran Perubatan** is a **different Anggaran (OS)** despite sharing Kod Objek `15000`. It has its own source/reference file `OS15000-bayaran-perubatan-reference.md` and is **not a category or child of Faedah-faedah Kewangan Yang Lain**.
- **Tambang Ziarah Wilayah** is within **Faedah-faedah Kewangan Yang Lain**, **not** another separate Anggaran (OS).

## 2. Flexible category hierarchy

The user requires a hierarchy of `Kategori`, `Kategori1`, `Kategori2`, `Kategori3`, and further category levels **if needed**. This records the approved classification values below, without requiring every entry to have every level or setting unprovided values.

### Tambang Ziarah Wilayah

| Kategori1 | Kategori2 | Kategori3 |
| --- | --- | --- |
| Tambang Ziarah Wilayah | Wilayah Asal | Sabah |
| Tambang Ziarah Wilayah | Wilayah Asal | Sarawak |

**Kadar:** No Kadar or calculation rule for Tambang Ziarah Wilayah has been supplied yet. Do not invent one or assume a prescribed rate.

### Bayaran Insentif Berasaskan Prestasi (BIPs)

**Named reference:** Bayaran Insentif Berasaskan Prestasi (BIPs).

**Approved rate rule:** `Kadar BIPs = KGT` for the applicable **Jawatan + full SSPA Gred**, retrieved from the existing **Reference 07 — Jawatan, Gred, Gaji dan KGT**.

- No independent BIPs rate table is needed.
- Fixed-RM KGT uses its existing reference values.
- If KGT is percentage-based (e.g. special VU7 rule), retain the calculation treatment documented in Reference 07; **the precise salary basis/rounding and other unresolved percentage-calculation details remain unresolved**. This file does not settle them.
- BIPs is intended to be classified within this OS; its deeper category levels, if any, have not been supplied.

### Hadiah Pengiktirafan Akademik (HPA)

**Kategori1:** Hadiah Pengiktirafan Akademik (HPA).

| Kategori2 | Kadar (RM) |
| --- | ---: |
| Ijazah Kedoktoran (PhD) | 1,250.00 |
| Ijazah Sarjana (Master) | 1,000.00 |
| Ijazah Sarjana Muda | 750.00 |
| Diploma/ Setaraf | 500.00 |

Rates above are predefined by the user, **not** manually entered. No extra Kategori3 was supplied for HPA.

## 3. Earlier committed Jenis Kemudahan reference — retained intact

The original **125-record** grade/rate source table is preserved unchanged in:

- [OS15000-kemudahan-gred-kadar-source.md](OS15000-kemudahan-gred-kadar-source.md).
- [OS15000-jawatan-gred-and-unspecified-kadar-rules.md](OS15000-jawatan-gred-and-unspecified-kadar-rules.md).

These previously agreed rules continue to apply:

- Where personnel attributes are required, use the shared **Jawatan + full SSPA Gred** input, and let the system interpret the grade as required by the corresponding source rate table.
- Do not assume equivalence between special legacy grade `C` and any numerical grade.
- For **Bayaran Balik Yuran** and any other **Kemudahan with no prescribed Kadar**, **Description and user-entered Kadar are both mandatory**. A blank source rate is not RM0.
- Preserve the recorded rates; no other eligibility, rate caps, or financial formulae are established by this document.

## 4. Current boundary and outstanding information

- The original 125 source rows are not rewritten, renamed or reclassified by this consolidation. Mapping their flat `Jenis Kemudahan` rows to category fields can be defined without changing the source evidence.
- Tambang Ziarah Wilayah's Kadar treatment remains unspecified.
- BIPs' rate derives from KGT; no independent rate schedule is introduced.
- No additional HPA qualification levels are inferred.
- No new operational restrictions, eligibility tests, calculations, database changes, Laravel code changes, or changes to existing Architecture Decisions are authorized by this reference commit.
