# Compensa Planning Documentation

Folder ini menjadi source of truth Compensa. Scope aktif sejak 2026-10-09 adalah **pure salary prediction**. Periode kerja, prorata, lembur, estimated total salary, monthly report, dan export tidak termasuk aplikasi.

## Urutan baca

1. [Requirements](01-requirements.md)
2. [Architecture](02-architecture.md)
3. [Database Design](03-database-design.md)
4. [Machine Learning](04-machine-learning.md)
5. [Prediction Rules](05-salary-business-rules.md)
6. [Application Flows](06-application-flows.md)
7. [Testing Strategy](07-testing-strategy.md)
8. [Deployment](08-deployment.md)
9. [Roadmap](09-roadmap.md)
10. [Decision Log](10-decision-log.md)

## Source of truth

| Area | Dokumen |
|---|---|
| Scope dan acceptance criteria | `01-requirements.md` |
| Arsitektur dan module boundary | `02-architecture.md` |
| Schema dan data preservation | `03-database-design.md` |
| Dataset, training, evaluation, artifact | `04-machine-learning.md` |
| Prediction output, OOD, dan rounding | `05-salary-business-rules.md` |
| Form, history, dan failure states | `06-application-flows.md` |
| Automated dan manual verification | `07-testing-strategy.md` |
| Environment dan deployment | `08-deployment.md` |
| Urutan implementasi | `09-roadmap.md` |
| Keputusan aktif dan superseded | `10-decision-log.md` |

Repository, dataset, artifact, dan hasil test aktual mengalahkan klaim dokumentasi yang kedaluwarsa. Perubahan material wajib memperbarui dokumen domain dan decision log.
