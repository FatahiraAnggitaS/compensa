# Roadmap

## Milestone 0 — Dataset readiness

Status: **Complete**.

Dataset sintetis 500 rows, provenance, publication permission, schema, target, feature semantics, hash, cleaning decision, dan evaluation contract telah ditetapkan.

## Milestone 1 — Foundation

Status: **Complete**.

Laravel, Blade, Vite, Pest, Pint, Python environment, health check, safe errors, dan CI tersedia.

## Milestone 2 — Employee dan database

Status: **Complete**.

Employee lifecycle, original salary-record schema, portable migrations, relation, demo seed, dan focused tests tersedia.

## Milestone 3 — Reproducible ML

Status: **Complete**.

Strict validator, deterministic training/evaluation, atomic JSON artifact, stable model version, parity references, Model Information, dan ML tests tersedia.

## Milestone 4 — Prediction

Status: **Complete, revised 2026-10-09**.

Laravel inference, artifact validation, OOD detection, two-decimal output rounding, active-employee lock, transaction save, Post/Redirect/Get, dan failure states tersedia. Salary calculation behavior telah dikeluarkan dari scope.

## Milestone 5 — Prediction history

Status: **Complete, revised 2026-10-09**.

History newest-first, employee filter, pagination, detail input/output, model version, dan OOD status tersedia. Monthly report dan exports telah dikeluarkan dari scope.

## Milestone 6 — Portfolio release

Status: **Deployment-ready locally; live deployment pending**.

Public demo guard, rate limit, security headers, accessible navigation, license, documentation, CI, dan Laravel Cloud runbook tersedia.

## Milestone 7 — Pure prediction scope reduction

Status: **Complete locally (2026-10-09)**.

Deliverables:

- Hapus periode, prorata, jam kerja, lembur, calculated salary, dan estimated total dari request, services, UI, dan history.
- Hapus Monthly Report, CSV, XLSX, print routes, code, views, tests, dan dependency.
- Simpan hanya employee, empat feature, predicted base salary, currency, model version, OOD flag, dan timestamp.
- Pertahankan old records melalui additive nullable migration tanpa drop column.
- Sinkronkan dokumentasi dan decision log.
- Jalankan seluruh quality gates.

Stop condition: web hanya menjalankan prediction flow, seluruh test lulus, dan tidak ada route aktif untuk salary calculation/reporting.

Completion evidence: 69 Pest tests dan 311 assertions lulus; Pint, 16 Python tests, Ruff lint/format, Vite build, Composer validation/audit, dan NPM audit lulus. Route list hanya memuat prediction, employee, history, model information, dan health routes. Removed report paths memiliki explicit 404 regression tests.

## Future improvements

- Authentication dan authorization sebelum data nyata.
- Dataset dunia nyata dengan provenance, consent, representativeness, dan fairness review.
- Model monitoring serta manually approved retraining.
- Additional algorithm hanya sebagai experiment terpisah.

Payroll calculation, attendance, overtime, tax, BPJS, payment, dan external HR integration tetap di luar roadmap.
