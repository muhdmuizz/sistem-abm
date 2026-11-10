# AD-006 — Kitaran ABM & Administration

**Status:** Approved  
**Date:** 10 November 2026  
**Related:** AD-001, AD-002, AD-003, AD-005; detailed review and comparison UI deferred to AD-009 and AD-010.

## Core definitions

- **Kitaran = Tahun**: one ABM planning year, identified by its unique year (e.g. 2028).
- **Fasa = a bounded opening/closing period within that year** for **Bidaan Awal** or **Pindaan**. A Kitaran may have multiple Pindaan phases, numbered sequentially, without a predetermined limit.
- **One complete Bidaan per PTJ + Aktiviti Kewangan + Kitaran**. A Pindaan edits/corrects the existing Bidaan; it never creates a second Bidaan or a separate financial hierarchy.
- Fasa is an access/time control, **not** a separate approval workflow or an owner of submissions. Do not introduce exclusive-active-Fasa constraints or arbitrary overlap restrictions.

## Minimal administrative data

**Kitaran:** required unique Tahun Kitaran. No duplicate year or redundant mandatory name.

**Fasa:** required Jenis Fasa (Bidaan Awal/Pindaan), Tarikh Mula, Tarikh Tamat, and Skop Pembukaan (global or selected PTJ/Aktiviti Kewangan). Dates must be valid and within the Kitaran year. Pindaan numbering is automatic. New Fasa starts **Ditutup** until Superadmin explicitly opens it; reaching the start date does not automatically activate it.

Superadmin may open, close, extend and reopen Fasa, including selectively for PTJ, Aktiviti Kewangan, or a specific PTJ + Aktiviti Kewangan combination. All changes are audited.

## Operational rule: Dibuka / Ditutup

**Fasa Dibuka = Penyedia prepares, edits and submits. Fasa Ditutup = Penyedia read-only, Penyelia and Ketua formally review submitted Bidaan.**

| Role | Dibuka | Ditutup |
| --- | --- | --- |
| Penyedia Anggaran | Prepare/edit Anggaran and Butiran Anggaran within assigned scope | Read-only |
| Penyedia Bidaan | Prepare and formally submit Bidaan within assigned scope | Read-only |
| Penyelia Bidaan | View submitted Bidaan | Formal Semakan, Ulasan/ Kuiri, Kemukakan |
| Ketua Bidaan | View submitted Bidaan | Review and Lulus/Tolak after formal forwarding |
| Pemantau Bidaan | Read-only | Read-only |
| Superadmin | Full authority, audited | Full authority, audited |

No automatic submission, approval, rejection or finalisation occurs at closure. Existing statuses persist. An unsubmitted draft remains saved, read-only, and is shown as **Belum Dihantar** in submission monitoring; this is a compliance classification, not a new workflow status.

Formal submission by Penyedia Bidaan remains mandatory. For missed deadlines or required corrections, Superadmin can selectively reopen the relevant Fasa/scope. **No independent per-Bidaan edit exception** is introduced: Penyedia permissions remain governed by Fasa. Detailed Ulasan/ Kuiri and returned-work mechanics are deferred to AD-009.

## Pindaan and version integrity

- Each new Pindaan continues from the existing financial data of the same Bidaan; unchanged Anggaran need not be recreated.
- **Only a formal submission or resubmission by Penyedia Bidaan advances the version number** (V1, V2, ...). Opening a Fasa or saving/editing drafts does not create a submitted version.
- Each formal submission produces an immutable financial snapshot. Prior snapshots and audit history are retained.
- Resubmission of an unchanged Bidaan is allowed **only with recorded justification**, and the system clearly reports **Tiada Perubahan**; an explanation is not misrepresented as an actual correction.
- Compare any relevant submitted versions down to unique-ID **Butiran Anggaran** and calculation inputs, classifying **Ditambah, Dipinda, Dipadam, Tiada Perubahan**. Show previous/new values, responsible actor and timestamps. Reviewers should see **Perubahan Sejak Penghantaran Terdahulu** by default, while retaining access to the complete Bidaan.
- Version comparison verifies whether claimed amendments actually occurred. Detailed comparison UI belongs to AD-009/AD-010; security and audit implementation to AD-011.

## Deletion and archival

- **A Kitaran containing any Bidaan or associated Anggaran cannot be deleted**, including by Superadmin through ordinary application functions.
- Superadmin may archive it, reopen Fasa or perform audited administrative corrections while preserving historical financial records and snapshots.
- Superadmin may delete an accidentally created **empty** Kitaran, with an audit record.

## Architectural boundaries

This AD defines Kitaran/Fasa controls, submission-version semantics and retention; it does not redefine the AD-002 financial hierarchy, AD-003 preparation/activation rules, or AD-005 roles. The single Bidaan record is stable across Bidaan Awal and every Pindaan.
