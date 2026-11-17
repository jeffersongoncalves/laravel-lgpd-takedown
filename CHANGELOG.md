# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-11-17

First release: ECA Digital notice and takedown (Lei 15.211/2025, arts. 27 to 30, 32 and 33; Decreto 12.880/2026).

- Notices must identify the reporter (no anonymous notices, art. 29, §2), with the reporter's role and a category. Content is reported through a `ReportableContent` model or the optional public POST route.
- Grave categories (sexual exploitation, sexual abuse, kidnapping, grooming) are taken down as soon as they are reported. `GraveContentReported` tells you to report them to the Polícia Federal, and `reportedToAuthority()` records that you did.
- `remove()` and `reject()` record the reason and whether the decision was automated. Removal keeps the content as evidence for the Marco Civil period (6 months, extendable) and opens a 30-day appeal window.
- Author appeal and `decideAppeal()` (restore or keep), with the `ContentRemoved` and `AppealDecided` events for notifying the author (art. 30).
- Abusive reporters are flagged by their number of rejected notices (arts. 32 and 33).
- `lgpd:takedown [--json]` lists pending work and `lgpd:takedown-prune` applies the retention period.

## [Unreleased]
