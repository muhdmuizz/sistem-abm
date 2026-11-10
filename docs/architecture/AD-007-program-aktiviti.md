# AD-007 — Program/ Aktiviti

**Status:** Foundational principle recorded; detailed design pending AD-004 finalisation  
**Date:** 10 November 2026  
**Related:** AD-002 — Canonical Financial Data Hierarchy; AD-004 — Anggaran Wizard Architecture

## Approved foundational principle

**Program/ Aktiviti is a guided workflow façade (a lobby) into the existing Anggaran (OS) wizards.** It is not an alternative financial hierarchy, a separate calculation engine or a duplicate financial management module.

**Anggaran = OS** is the main financial entity and stores **Butiran Anggaran**. Butiran Anggaran can be entered through (1) the Anggaran (OS) wizard directly or (2) Program/ Aktiviti, which guides and requires the Penyedia Anggaran to address all related OS.

The canonical financial hierarchy is unchanged:

**PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**

## Responsibilities of the façade

1. Establish a single, uniquely identified Program/ Aktiviti record, rather than using free-text names as the linking key.
2. Require selection of all relevant OS and enforce any OS requirements determined by the programme's characteristics.
3. Direct the Penyedia Anggaran to the **same existing OS-specific wizards** to enter the related Butiran Anggaran, allowing work to be saved progressively.
4. Link programme-related Butiran Anggaran to the unique Program/ Aktiviti ID and their parent Anggaran (OS), irrespective of entry point.
5. Show completion progress for the programme's required OS and validate required work before the relevant Bidaan is submitted.
6. Allow the same programme to span multiple OS without text-match errors, duplicate Anggaran records or duplicated Butiran Anggaran.

## Direct-entry safeguard

When an expenditure belongs to an existing Program/ Aktiviti, entry directly through the Anggaran wizard must preserve that association (e.g. select the existing programme); free-text spelling variations must not create separate programme identities.

## Design boundary

**Finalise AD-004 — Anggaran Wizard Architecture before continuing the detailed AD-007 design.** This document records the approved principle only; it does not prematurely finalise Program/ Aktiviti fields, detailed UI, or OS-specific wizard calculations.
