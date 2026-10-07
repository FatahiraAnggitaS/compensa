# Compensa Planning Documentation

Folder ini menjadi source of truth perencanaan MVP Compensa sebelum implementasi dimulai.

Status seluruh dokumen: **Approved with blockers pada 2026-10-07**. Scope dan keputusan MVP telah disetujui melalui Q&A. Item data, dependency version, dan provider yang belum terverifikasi tetap menjadi blocker pada milestone terkait.

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
- Responsive UI scaffold, lima GET routes, invokable controllers, shared navigation, dan honest empty states telah tersedia tanpa database.
- Tombol prediction, persistence, filter berbasis data, dan export tetap nonaktif. Business implementation belum tersedia.
- Active candidate dataset `data_train/salary_500.csv` dibuat sendiri oleh pemilik project, disetujui untuk public Git, dan profil aktualnya tercatat di `data_train/README.md`. `salary.csv` tetap dikecualikan dari Git sebagai rollback dan bukan training input.
- Python training code dan JSON model artifact belum tersedia.
- Provenance, publication permission, raw target column `salary`, monthly IDR semantics, integer score scale 0–100, schema, observed range, data completeness, cleaning decision, evaluation strategy, dan file hash sudah ditetapkan. Dataset sintetis yang diisi acak tidak mewakili distribusi gaji dunia nyata.
- Tidak ada metric model yang boleh ditampilkan sebelum training dan evaluation benar-benar dijalankan.
