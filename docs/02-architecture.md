# Architecture

## 1. Repository evidence

Repository aktual adalah Laravel application skeleton:

- `composer.json` memakai `laravel/laravel` dan `laravel/framework` constraint `^13.17`;
- PHP constraint adalah `^8.4`;
- server-rendered view memakai Blade;
- frontend assets memakai Tailwind CSS dan Vite yang sudah tersedia;
- test stack memakai Pest dengan Laravel plugin;
- PHP formatting memakai Laravel Pint;
- local development dilayani Laravel Herd;
- active synthetic dataset `data_train/salary_500.csv` tersedia; Python training
  code, business implementation, dan model artifact belum tersedia.

## 2. Keputusan arsitektur

Arsitektur MVP: **Laravel modular monolith dengan offline Python ML training dan JSON model artifact**.

Ownership:

- Laravel menangani HTTP, validation, CSRF, Blade UI, Eloquent ORM, salary calculation, persistence, history, reports, exports, dan request-time inference.
- Python menangani dataset inspection, validation, training scikit-learn `LinearRegression`, evaluation, dan export artifact.
- Python tidak berjalan dalam HTTP request dan tidak menjadi production web service.
- Laravel tidak membaca `pickle` atau `joblib`. Laravel hanya membaca trusted JSON artifact milik project.

Pendekatan ini mempertahankan Laravel template dan penggunaan Herd tanpa menambah ML microservice.

## 3. Technology stack

| Concern | Pilihan MVP | Status/catatan |
| --- | --- | --- |
| Web language | PHP `^8.4` | Selaras dengan Pest 5 dan terverifikasi pada PHP 8.4.25 |
| Web framework | Laravel `^13.17` | Terverifikasi dari `composer.json` |
| Local web environment | Laravel Herd | Dipilih pemilik project |
| UI | Blade | Server-rendered |
| Styling/build | Tailwind CSS + Vite | Sudah tersedia dalam template |
| Browser behavior | Minimal vanilla JavaScript | Tanpa SPA framework |
| ORM/migrations | Eloquent + Laravel migrations | Application database ownership |
| Local database | SQLite | Sudah tersedia untuk local setup |
| Deployment database | PostgreSQL | Managed persistent database |
| ML language | Python 3.13 | Offline training only |
| ML/data | scikit-learn 1.9.1 + pandas 3.0.6 | Exact direct dan transitive pins tersedia di `ml/requirements.txt` |
| Runtime model format | Strict JSON | Coefficients, intercept, contract, ranges, metrics, dan provenance |
| PHP tests | Pest + Laravel plugin | Sudah tersedia |
| Python ML tests | standard-library `unittest` | Tanpa pytest |
| PHP format | Laravel Pint | Sudah tersedia |
| Python lint/format | Ruff | Hanya untuk `ml/` |
| CSV export | Laravel streamed response | Tanpa package tambahan |
| Excel export | Direct PhpSpreadsheet integration | Install setelah compatibility verification |
| PDF | Print stylesheet/browser Save as PDF | Tanpa PDF engine |

Mekanisme fixed-precision salary arithmetic di PHP harus dipilih sebelum salary calculator diimplementasikan. Contract tetap `Decimal(18,2)` dengan `ROUND_HALF_UP`; binary floating-point tidak boleh menjadi authoritative money representation.

## 4. Component boundaries

```text
CSV dataset
    |
    v
Offline Python trainer
validation -> mapping -> training -> evaluation -> JSON export
                                                    |
                                                    v
Browser -> Laravel routes/controllers/forms -> JSON predictor -> salary calculator
                       |                    |                 |
                       |                    v                 |
                       |           coefficients/intercept    |
                       v                                      v
                  Eloquent ORM ------------------------> application DB
                       |
                       +-> history -> monthly report -> CSV/XLSX/print
```

Tidak ada HTTP boundary antara Laravel dan Python karena Python tidak digunakan saat runtime web.

## 5. Target module layout

Struktur ini adalah target implementation. File yang belum ada tidak boleh dianggap sudah tersedia.

```text
app/
  Http/Controllers/
  Models/
    Employee.php
    SalaryRecord.php
  Services/
    SalaryPredictionService.php
    SalaryCalculator.php
    MonthlyReportService.php
  Console/Commands/
    SeedDemoData.php
database/
  migrations/
  seeders/
resources/
  views/
  css/
  js/
routes/
  web.php
ml/
  train.py
  salary_ml/
    data_validation.py
    training.py
    artifact.py
  tests/
  requirements.txt
artifacts/
  salary_linear_regression.json
docs/
tests/
  Feature/
  Unit/
```

## 6. JSON model contract

Satu self-contained JSON artifact menyimpan:

- artifact schema version;
- model version;
- algorithm identifier `linear_regression`;
- ordered canonical features;
- coefficient untuk setiap feature;
- intercept;
- target name, currency `IDR`, dan monthly cadence;
- raw-to-canonical column mapping;
- observed training range setiap feature;
- dataset provenance dan SHA-256;
- row counts serta cleaning decisions;
- evaluation configuration dan actual metrics;
- Python dan scikit-learn versions;
- training timestamp;
- parity reference cases yang aman dipublikasikan.

`model_version` berasal dari hash canonical model payload. Laravel memverifikasi schema, feature names, algorithm, target contract, model version, dan finite numeric values sebelum inference.

MVP tidak mendukung learned preprocessing seperti imputation, scaling, categorical encoding, atau arbitrary scikit-learn `Pipeline` pada runtime PHP. Dataset requirement saat ini hanya empat numeric features dan missing values memblokir training. Jika learned preprocessing ternyata diperlukan, implementation berhenti dan architecture harus direview.

## 7. Training lifecycle

1. Operator mengaktifkan isolated Python environment untuk folder `ml/`.
2. Operator menjalankan `python ml/train.py --dataset <path>`.
3. Trainer membaca CSV tanpa mengubah source file.
4. Validator memeriksa schema, target contract, missing values, duplicates, finite values, dan provenance requirements.
5. Trainer memakai fixed raw-to-canonical column mapping.
6. Trainer memisahkan evaluation data sesuai keputusan berbasis data profile.
7. scikit-learn `LinearRegression` melakukan fit dan evaluation.
8. Exporter menulis temporary JSON, memvalidasinya, lalu mengganti active artifact file secara atomik.
9. Python dan PHP parity tests membuktikan formula Laravel cocok dengan reference predictions dalam tolerance yang ditetapkan.

Training tidak membaca application database dan tidak memakai user predictions sebagai labels.

## 8. Inference lifecycle

1. Laravel Form Request memvalidasi employee, features, period, work days, dan overtime inputs.
2. `SalaryPredictionService` membaca fixed trusted JSON artifact.
3. Service memvalidasi artifact contract sebelum menggunakan nilainya.
4. Service menghitung `intercept + sum(coefficient[feature] * input[feature])` dengan canonical feature mapping.
5. Service mendeteksi OOD input dari observed training ranges.
6. Negative atau non-finite prediction memblokir calculation dan save.
7. Valid prediction dikonversi ke fixed-precision monetary representation sesuai rounding contract.
8. `SalaryCalculator` menghitung proration, normal hours, overtime pay, dan estimated total.
9. Eloquent menyimpan immutable salary record dalam satu database transaction.

JSON hanya berasal dari fixed project path. Artifact upload dan arbitrary path input tidak tersedia.

## 9. Failure behavior

- Missing, malformed, atau incompatible artifact: prediction dinonaktifkan dan UI menampilkan safe operational error.
- Feature/model contract mismatch: inference gagal tertutup; Laravel tidak menebak feature order.
- Python/PHP parity failure: artifact tidak boleh dirilis.
- Invalid form input: field errors tampil dan record tidak tersimpan.
- Database failure: transaction rollback dan UI tidak mengklaim save berhasil.
- Export failure: report HTML tetap dapat digunakan.

## 10. Deployment topology

Local web runtime memakai Laravel Herd dan Vite. Production memakai satu Laravel application service serta managed PostgreSQL. Python tidak diperlukan pada production runtime bila trusted JSON artifact sudah menjadi bagian release.

Queue worker, cache server, Python inference API, object storage, API gateway, dan ML platform tidak ditambahkan.

## 11. Migration and rollback

Forward path:

1. Selaraskan dokumentasi dengan Laravel repository.
2. Pertahankan Laravel skeleton dan existing lockfiles.
3. Tambahkan offline Python trainer tanpa mengubah web runtime.
4. Tambahkan JSON artifact contract dan cross-language parity proof.
5. Implementasikan Laravel inference setelah artifact contract lulus.

Rollback dependency foundation dilakukan dengan mengembalikan `composer.json`, `composer.lock`, dan `ml/requirements.txt` ke baseline Git sebelumnya. Belum ada production data atau model artifact yang dimigrasikan. Default Laravel migrations tidak dihapus.
