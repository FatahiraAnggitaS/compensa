# Architecture

## 1. Keputusan

Compensa memakai **Laravel modular monolith dengan offline Python ML training dan JSON model artifact**.

- Laravel menangani HTTP, validation, Blade UI, Eloquent, request-time inference, persistence, history, dan security boundary.
- Python menangani dataset validation, training, evaluation, dan artifact export.
- Python tidak berjalan dalam HTTP request atau production web runtime.
- Laravel hanya membaca trusted JSON artifact dari fixed project path.

## 2. Stack

| Concern | Pilihan |
|---|---|
| Web | PHP 8.4, Laravel 13 |
| UI | Blade, Tailwind CSS, Vite, minimal JavaScript |
| Database | SQLite local, PostgreSQL deployment |
| ML | Python 3.13, pandas, scikit-learn |
| Model format | Strict JSON |
| Money boundary | BCMath round to 2 decimals |
| Tests | Pest dan Python `unittest` |
| Formatting | Pint dan Ruff |

## 3. Component flow

```text
data_train/salary_500.csv
        |
        v
Python validator -> train/evaluate LinearRegression -> atomic JSON artifact
                                                        |
                                                        v
Browser -> Laravel Form Request -> artifact reader -> prediction service
                                                        |
                                                        v
                                            Eloquent prediction record
                                                        |
                                                        v
                                                prediction history
```

## 4. Training lifecycle

1. Operator menjalankan `python ml/train.py --dataset <path>`.
2. Validator memeriksa hash, schema, values, duplicates, ranges, dan leakage.
3. Pipeline membuat deterministic 400/100 split dengan seed 42.
4. `LinearRegression` fit pada 400 training rows.
5. Pipeline menghitung held-out metrics dan five-fold training-only CV.
6. Exporter membuat stable model version dan parity cases.
7. Artifact ditulis secara atomik ke `artifacts/salary_linear_regression.json`.

Training tidak membaca application database.

## 5. Inference lifecycle

1. Form Request memvalidasi employee dan empat feature.
2. Service mengunci employee dan memeriksa status aktif dalam transaction.
3. Artifact reader memvalidasi schema, algorithm, feature order, target, values, metrics, dan parity contract.
4. Predictor menghitung `intercept + sum(coefficient × feature)` memakai float untuk parity dengan scikit-learn.
5. Predictor menolak hasil non-finite, nol, negatif, atau overflow.
6. Predictor membulatkan output menjadi dua desimal memakai BCMath `HalfAwayFromZero`.
7. Predictor menentukan OOD dari observed feature ranges.
8. Eloquent menyimpan immutable prediction record.

## 6. Failure behavior

- Artifact hilang/rusak: form dinonaktifkan atau submit gagal aman.
- Contract mismatch: inference ditolak.
- Input invalid atau employee nonaktif: record tidak dibuat.
- Database failure: transaction rollback.
- Negative/non-finite prediction: record tidak dibuat.

## 7. Excluded architecture

Tidak ada Python API, microservice, queue, Redis, worker, scheduler, model registry, automatic retraining, report engine, atau object storage.
