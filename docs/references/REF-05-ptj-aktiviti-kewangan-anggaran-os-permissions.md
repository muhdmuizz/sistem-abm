# Reference 05 — PTJ ↔ Aktiviti Kewangan ↔ Anggaran (OS) permissions

**Status:** Confirmed framework/defaults; storage and read-only resolution implemented in Batch 005. Detailed OS permission choices remain undecided.  
**Confirmed:** 12 November 2026  
**Source:** Explicit user decisions in the Sistem ABM reference-data conversation.  
**Nature:** Reference configuration and access-control rules, **not a new financial transaction hierarchy**.

## 1. Initial Aktiviti Kewangan defaults

| Aktiviti Kewangan | JPNT | PPD | SMK PTJ |
| --- | --- | --- | --- |
| Pengurusan Pentadbiran Negeri (PPN) | Enabled | Enabled | Enabled |
| Pendidikan Awal | Enabled | Enabled | Disabled |
| Pendidikan Rendah | Enabled | Enabled | Disabled |
| Pendidikan Menengah | Enabled | Enabled | Enabled |
| Pengurusan Asrama | Enabled | Enabled | Enabled |

These are **starting defaults, not immutable permissions**. The organisational reference inventory contains 1 JPNT, 8 PPD and 60 SMK PTJ. The user will refine assignments later; do not infer exceptional PTJ permissions yet.

## 2. Both permission dimensions are mandatory

Superadmin must be able to manage permissions at **both** dimensions:

1. **Aktiviti Kewangan** — whether a PTJ may access an Aktiviti Kewangan.
2. **Anggaran (OS)** — which individual Anggaran (OS) are accessible **within each permitted Aktiviti Kewangan**.

Access to an Aktiviti Kewangan **does not automatically authorise all** of its Anggaran (OS).

Anggaran (OS) identity remains **Kod Objek + canonical Butiran Perbelanjaan**. Access must respect the existing **Kod Aktiviti-to-Anggaran (OS)** reference mappings (Reference 04), without inserting Kod Aktiviti as a newly approved transaction hierarchy level.

## 3. Both management scopes and toggles are mandatory

The Superadmin UI/configuration must support **both sets of toggles** for **both permission dimensions**:

### A. PTJ category defaults

- Set Aktiviti Kewangan and Anggaran (OS) starting defaults independently for **JPNT**, **PPD**, and **SMK PTJ**.
- Category defaults support convenient bulk/general management; differing OS defaults are allowed even within the same Aktiviti Kewangan (e.g. PPN for JPNT vs PPD vs SMK PTJ).
- **Specific Anggaran (OS) category defaults have not yet been selected.** Do not guess, pre-enable, or pre-disable them.

### B. Individual PTJ permissions

- Superadmin can individually toggle Aktiviti Kewangan and Anggaran (OS) **for each actual PTJ**, independently of other PTJ in the same category.
- Two SMK PTJ, two PPD, or any other distinct PTJ may have **different effective OS permissions within the same Aktiviti Kewangan**.
- A PTJ without an explicit override follows its corresponding category default, **per setting**.
- Explicit PTJ overrides take precedence over category defaults, **per setting**, including both enable and disable overrides.
- If Superadmin changes a category default, inheriting PTJ follow the updated value; PTJ with explicit overrides for that setting keep their overrides.
- Superadmin can **reset an individual override to the category default**.
- Neither category-default changes nor PTJ-specific permission changes erase historical Anggaran records or transactions.

## 4. Effective access and dependencies

- An Anggaran (OS) is accessible only when **its parent Aktiviti Kewangan is effectively enabled for that PTJ**, its individual/category OS setting permits access, the source Kod Aktiviti mapping permits that OS, and implementation exclusions are respected.
- Disabling an Aktiviti Kewangan masks access to its OS without removing the stored OS-level configuration.
- The existing Reference 04 decisions apply: **POL** (`030306` / `29000`) and **Asrama Malaysia (AMal)** (`030318` / `42000`) remain in reference data but **must not be offered in implementation**, regardless of toggle values.
- These permission settings **do not modify** the Reference 04 canonical catalogue or original source mappings.

## 5. Deliberately deferred

The following remain **undecided** and must not be treated as approved defaults:

- Exact Anggaran (OS) selections for JPNT, PPD, and SMK PTJ under each Aktiviti Kewangan.
- Specific individual-PTJ overrides or exceptions.
- Further PTJ-by-PTJ refinements.

The user explicitly requested **general rules first**, with detailed refinement later. This record does **not** claim that OS assignments have been completed or implemented.

## 6. Implementation status — reconciled 13 November 2026

Batch 005 implements configuration storage for category defaults and individual PTJ overrides, together with read-only resolution for both permission dimensions. Its seeder creates the 15 approved category/activity defaults from section 1 and preserves later configuration on reruns.

No OS defaults or individual PTJ overrides were chosen/seeded. The full Superadmin management UI/API and audited administrative mutation services are not implemented. Passing configuration tests does not certify complete user/role/workplace authorisation or financial workflows.

See [Batch 005](../development/batch-005-ptj-permissions.md) and the [development register](../development/README.md) for actual CI/merge/local evidence. The approved rules in sections 1–5 and existing Architecture Decisions remain unchanged.
