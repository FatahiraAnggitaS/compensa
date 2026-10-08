# Compensa Planning Documentation

Folder ini menjadi source of truth perencanaan MVP Compensa sebelum implementasi dimulai.

Status seluruh dokumen: **Milestone 6 complete locally / Laravel Cloud deployment-ready pada 2026-10-08**. Scope, implementation contract, dan release runbook telah disetujui. Live deployment serta PostgreSQL smoke masih pending karena belum ada account/environment aktif.

## Urutan baca

1. [Requirements](01-requirements.md)
2. [Architecture](02-architecture.md)
3. [Database Design](03-database-design.md)
4. [Machine Learning](04-machine-learning.md)
5. [Salary Business Rules](05-salary-business-rules.md)
6. [Application Flows](06-application-flows.md)
7. [Testing Strategy](07-testing-strategy.md)
8. [Deployment](08-deployment.md)
9. [Roadmap](09-roadmap.md)
10. [Decision Log](10-decision-log.md)

## Source of truth

| Area | Dokumen utama |
| --- | --- |
| Requirement, scope, acceptance criteria | `01-requirements.md` |
| Arsitektur dan technology stack | `02-architecture.md` |
| Database, constraint, dan index | `03-database-design.md` |
| Dataset, training, evaluation, artifact, inference | `04-machine-learning.md` |
| Periode kerja, prorata, overtime, total salary | `05-salary-business-rules.md` |
| Halaman, validation, history, report, export | `06-application-flows.md` |
| Test dan quality gate | `07-testing-strategy.md` |
| Environment dan deployment | `08-deployment.md` |
| Urutan implementasi dan stop condition | `09-roadmap.md` |
| Alasan perubahan keputusan | `10-decision-log.md` |

## Aturan konflik

1. Requirement terbaru yang disetujui pemilik project menang.
2. `01-requirements.md` mengendalikan scope.
3. Dokumen domain mengendalikan detail pada domain masing-masing.
4. Perubahan material harus dicatat di `10-decision-log.md` dan disinkronkan ke dokumen terkait.
5. Repository aktual, dataset, model artifact, dan hasil test mengalahkan klaim dokumentasi yang sudah kedaluwarsa. Konflik harus diperbaiki, bukan disembunyikan.

## Status implementasi saat ini

- Repository memiliki Laravel application skeleton, Composer/NPM lockfiles, Blade, Tailwind/Vite, Pest, dan Pint.
- Milestone 0 selesai: active synthetic dataset, data contract, cleaning decision, evaluation configuration, publication permission, dan Git planning baseline telah ditetapkan.
- Milestone 1 selesai secara lokal: runtime baseline, pinned Python environment, safe error pages, Laravel health check, environment example, ignore rules, dan cross-runtime CI workflow tersedia.
- Milestone 2 selesai secara lokal: additive employee/salary-record migrations, Eloquent relations/casts, employee lifecycle, search/status filter/pagination, reversible status, idempotent demo seed, dan focused tests tersedia.
- Milestone 3 selesai secara lokal: strict dataset validator, deterministic Linear Regression training/evaluation, atomic self-contained JSON artifact, parity reference cases, Laravel metadata reader, Model Information berbasis artifact, dan focused Python/Pest tests tersedia.
- Milestone 4 selesai secara lokal: Laravel JSON inference, Python/PHP parity, OOD detection, native BCMath salary calculation, Form Request validation, transaction writer, responsive result/error states, dan focused tests tersedia.
- Milestone 5 selesai secara lokal: immutable prediction history, employee/month filters, detail technical snapshot, canonical monthly report query, exact summary totals, streamed CSV, numeric XLSX, dan browser Print/Save as PDF tersedia.
- Milestone 6 selesai secara lokal: public-demo write guards, throttling, security headers, WCAG-oriented core markup/navigation, dependency audits, MIT/CC BY 4.0 license split, screenshots, serta Laravel Cloud runbook tersedia.
- Prediction, calculation, history, reporting, dan export aktif; salary record hanya dibuat melalui validated prediction flow.
- Active candidate dataset `data_train/salary_500.csv` dibuat sendiri oleh pemilik project, disetujui untuk public Git, dan profil aktualnya tercatat di `data_train/README.md`. `salary.csv` tetap dikecualikan dari Git sebagai rollback dan bukan training input.
- Python training code tersedia di `ml/`; committed artifact berada di `artifacts/salary_linear_regression.json`. Model version aktif adalah `sha256:ef4edb1136f434009edc7be50ed2f8cc3dc2dbf1292756d5f33b0311b5ef05c5`.
- Provenance, publication permission, raw target column `salary`, monthly IDR semantics, integer score scale 0–100, schema, observed range, data completeness, cleaning decision, evaluation strategy, dan file hash sudah ditetapkan. Dataset sintetis yang diisi acak tidak mewakili distribusi gaji dunia nyata.
- Model Information menampilkan metric yang dibaca dari artifact: held-out R² `0.923052`, MAE `428562.70` IDR, dan RMSE `509974.45` IDR. Nilai ini hanya mengukur fit pada dataset sintetis, bukan akurasi dunia nyata.
