# Roadmap

## Milestone 0 — Dataset readiness

Status: **Complete**.

Dataset sintetis 500 rows, provenance, publication permission, schema, target, feature semantics, hash, cleaning decision, dan evaluation contract telah ditetapkan.

## Milestone 1 — Foundation

Status: **Complete**.

Laravel, Blade, Vite, Pest, Pint, Python environment, health check, safe errors, dan CI tersedia.

## Milestone 2 — Employee dan database

Status: **Complete**.

Employee lifecycle, original salary-record schema, portable migrations, relation, demo seed, dan focused tests pernah menjadi fondasi awal. Employee Management kemudian dinonaktifkan pada Milestone 8; tabel dan relasi lama tetap dipertahankan untuk compatibility.

## Milestone 3 — Reproducible ML

Status: **Complete**.

Strict validator, deterministic training/evaluation, atomic JSON artifact, stable model version, parity references, Model Information, dan ML tests tersedia.

## Milestone 4 — Prediction

Status: **Complete, revised 2026-10-09**.

Laravel inference, artifact validation, OOD detection, two-decimal output rounding, direct-name snapshot, Post/Redirect/Get, dan failure states tersedia. Salary calculation behavior telah dikeluarkan dari scope.

## Milestone 5 — Prediction history

Status: **Complete, revised 2026-10-09**.

History newest-first, employee filter, pagination, detail input/output, model version, dan OOD status tersedia. Monthly report dan exports telah dikeluarkan dari scope.

## Milestone 6 — Portfolio release

Status: **Deployment-ready locally; live deployment pending**.

Public demo warning, rate limit, security headers, accessible navigation, license, documentation, CI, dan Laravel Cloud runbook tersedia.

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

Completion evidence saat milestone ditutup: 69 Pest tests dan 311 assertions lulus; Pint, 16 Python tests, Ruff lint/format, Vite build, Composer validation/audit, dan NPM audit lulus. Removed report paths memiliki explicit 404 regression tests.

## Milestone 8 — Direct employee name

Status: **Complete locally (2026-10-09)**.

- Nama employee diketik langsung pada form prediction.
- Setiap record menyimpan `employee_name` sebagai immutable snapshot.
- `employee_id` tetap nullable sebagai legacy compatibility; data lama dibackfill tanpa dihapus.
- Employee CRUD, navigation, public-demo guard, dan demo seed dihapus.
- History menggunakan pencarian nama, bukan data master employee.
- Rollback migration ditolak bila direct-name records tidak dapat memenuhi schema lama.

Verification: 66 Pest tests / 260 assertions lulus; termasuk backfill, empty rollback/forward, guard rollback, validasi nama, dan history. Dua migration tertunda berhasil diterapkan pada SQLite lokal. Pint, 16 Python tests dengan `.venv`, Ruff, Vite build, Composer validation/audit, dan NPM audit lulus. PostgreSQL/live deployment tetap belum diverifikasi.

## Future improvements

- Authentication dan authorization sebelum data nyata.
- Dataset dunia nyata dengan provenance, consent, representativeness, dan fairness review.
- Model monitoring serta manually approved retraining.
- Additional algorithm hanya sebagai experiment terpisah.

Payroll calculation, attendance, overtime, tax, BPJS, payment, dan external HR integration tetap di luar roadmap.
