# OS14000 — Bayaran Lebih Masa: consolidated reference checkpoint

**Recorded:** 12 November 2026.  
**Status:** Confirmed references and financial *Kadar formula* preserved. **Detailed #P7OS design remains paused**; this is **not** a completed OS14000 specification or authority to restart development.

## 1. Shared personnel reference (not OS-specific ownership)

- Shared [Reference 07 — Jawatan, Gred, Gaji dan KGT](REF-07-jawatan-gred-gaji-kgt.md) and its [131-row CSV](jawatan-gred-gaji-kgt.csv) are reusable across **all Anggaran (OS) where applicable**, not just OS14000.
- **Jawatan and Gred are independent personnel attributes, never Kategori1/Kategori2/etc.**
- The already-confirmed OS14000 selection order is **Jawatan → applicable full SSPA Gred**; `Gaji Median` is retrieved from the corresponding Jawatan/Gred reference row.
- No duplicate OS14000-specific salary CSV is required: the shared catalogue is the source.

## 2. Confirmed OS14000 Kadar formula

**User-confirmed on 12 November 2026:**

```text
Kadar = Gaji Median × 12 ÷ 2504
```

- The `Gaji Median` input comes from **Reference 07** according to selected Jawatan and Gred.
- This is the confirmed **Kadar formula**, not a confirmation of any remaining overall Bayaran Lebih Masa cost formula, claim eligibility, quantity, final aggregation, or export-row mapping.
- **Precision and rounding have not been specified**; do not invent a rounding policy.
- Earlier cautions in Draft AD-008 predate this owner confirmation. **This reference checkpoint records the later explicit confirmation but does not modify or finalise AD-008.**

## 3. Program/ Aktiviti/ Tujuan

The existing [OS14000 Program/ Aktiviti/ Tujuan reference](OS14000-program-aktiviti-tujuan-options.md) is retained as written:

1. Actual **Program/ Aktiviti** registered in the system (replacing the legacy static `Aktiviti/ Program/ Mesyuarat rasmi` option).
2. `Kerja-kerja jangka masa terhad`.
3. `Kerja-kerja mustahak yang tidak boleh ditangguh`.

No additional category levels are introduced.

## 4. Existing design decisions already preserved in AD-008

See [AD-008 — OS Catalogue & Calculation Framework](../architecture/AD-008-os-catalogue-calculation-framework.md):

- Officer-level Butiran Anggaran records have stable, independent IDs; multiple officers are supported and output traceability must be preserved.
- The 27-column classification for OS14000 was confirmed at **19 Active, 8 Inactive**; `Kategori1`, `Kategori2`, and `Kategori3` are **Inactive**. `Jawatan`, `Gred`, and `Kadar` are Active reference fields.
- The **#P7OS seven-step OS design process is paused**. In particular, its input design, remaining detailed field rules, overall calculation specification, and granular output mapping are not finalised. Preserve approved work to date; do not reopen the process implicitly.

## 5. Preservation and scope

- **Markdown decision record:** this consolidated checkpoint + the existing Program/ Aktiviti/ Tujuan document + AD-008.
- **Machine-readable reference:** shared Reference 07 salary CSV. A dedicated OS14000 salary CSV would be unnecessary duplication.
- This consolidation makes **no changes** to source CSV values, master OS catalogue, PostgreSQL, Laravel, AD-008 or any other Architecture Decision.
- Further #P7OS work must resume only when separately instructed, preserving the confirmed Kadar formula and previous decisions.
