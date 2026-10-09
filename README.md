# Compensa

Compensa adalah aplikasi portfolio Laravel untuk memprediksi **monthly base salary** dengan scikit-learn `LinearRegression`. User memilih employee, memasukkan empat feature, lalu Laravel melakukan inference dari artifact JSON terlatih dan menyimpan hasil prediksi.

Compensa bukan payroll, kalkulator lembur, atau sistem HR. Dataset bersifat sintetis dan hasil tidak boleh dipakai sebagai benchmark gaji pasar atau keputusan kerja.

## Fitur

- Employee management dengan identitas minimum dan status aktif.
- Prediksi base salary dari Knowledge Score, Technical Score, Logical Score, dan Years of Experience.
- Offline Python training terpisah dari request web.
- Trusted JSON artifact dengan model version, coefficient, intercept, ranges, dan metrics.
- OOD warning saat input berada di luar rentang data training.
- Riwayat prediksi immutable dengan filter employee.
- Model Information dari metadata artifact aktual.
- Public demo guard, rate limit, security headers, dan responsive Blade UI.

## Alur

```text
CSV sintetis
  -> validasi hash/schema/data
  -> deterministic training dan evaluation
  -> committed JSON artifact
  -> Laravel artifact validation
  -> employee + empat feature
  -> Linear Regression inference
  -> predicted monthly base salary
  -> database history
```

Python tidak berjalan saat HTTP request. Prediction user tidak menjadi training data.

## Stack

- PHP 8.4, Laravel 13, Blade, Tailwind CSS, Vite
- SQLite untuk local development; PostgreSQL untuk target deployment
- Python 3.13, pandas, scikit-learn
- Pest, Pint, `unittest`, dan Ruff

## Setup lokal

```bash
composer install
npm ci
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan app:seed-demo-data
npm run build
```

Jalankan melalui Laravel Herd atau:

```bash
php artisan serve
```

Demo seed membuat dua employee aktif dan satu employee nonaktif. Data demo tidak mencakup hasil prediksi.

## Training

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r ml/requirements.txt
python ml/train.py --dataset data_train/salary_500.csv
```

Artifact ditulis ke `artifacts/salary_linear_regression.json`. Training memakai 400 rows, held-out testing 100 rows, split seed 42, serta five-fold cross-validation hanya pada training rows.

Model version aktif:

```text
sha256:ef4edb1136f434009edc7be50ed2f8cc3dc2dbf1292756d5f33b0311b5ef05c5
```

Held-out metrics pada dataset sintetis:

- R²: `0.9230522017850311`
- MAE: `428562.69967103825` IDR
- RMSE: `509974.4498421641` IDR

Metrics tersebut bukan bukti akurasi dunia nyata.

## Quality gates

```bash
php artisan test
vendor/bin/pint --test
python -m unittest discover -s ml/tests
python -m ruff check ml
python -m ruff format --check ml
python ml/train.py --dataset data_train/salary_500.csv
npm run build
composer validate --strict --no-check-publish
composer audit --locked --no-interaction
npm audit --audit-level=high
```

## Deployment

Target release adalah Laravel Cloud dengan PHP 8.4 dan managed PostgreSQL. Production memakai committed artifact dan tidak memerlukan Python. Live deployment dan PostgreSQL smoke masih pending.

## Dokumentasi

Folder [`docs/`](docs/) menjadi source of truth requirement, arsitektur, database, Machine Learning, prediction rules, testing, deployment, roadmap, dan keputusan teknis.

## License

Source code memakai MIT License. Ketentuan dataset dijelaskan terpisah pada `data_train/DATASET-NOTICE.md`.
