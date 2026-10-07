# Compensa

Compensa adalah aplikasi portfolio Laravel untuk mendemonstrasikan alur employee, prediksi base salary, perhitungan periode kerja dan overtime, history, serta laporan bulanan. Aplikasi ini bukan payroll production, benchmark pasar, atau alat keputusan HR.

Status saat ini: Milestone 1 (project foundation) selesai secara lokal. UI scaffold, dataset sintetis, environment checks, dan CI workflow tersedia. Training model, persistence business data, prediction, dan report belum diimplementasikan.

## Prasyarat

- PHP 8.4 dan Composer
- Node.js serta NPM
- Python 3.13
- Laravel Herd untuk local web serving

## Setup Laravel

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

Pada Windows PowerShell, gunakan `Copy-Item .env.example .env` sebagai pengganti `cp`.

Jalankan aplikasi melalui Laravel Herd. Health check tersedia pada `/up`.

## Setup Python ML

Python hanya dipakai untuk offline training dan evaluation. Buat environment terisolasi dari root repository:

```bash
python -m venv ml/.venv
```

Aktifkan environment, lalu install dependency yang dipin:

```bash
python -m pip install --upgrade pip
python -m pip install -r ml/requirements.txt
```

Training command belum tersedia sampai Milestone 3.

## Quality checks

```bash
php artisan test
vendor/bin/pint --test
python -m unittest discover -s ml/tests
python -m ruff check ml
python -m ruff format --check ml
npm run build
```

Dokumentasi perencanaan dan keputusan project berada di [`docs/`](docs/README.md). Dataset aktif dijelaskan di [`data_train/README.md`](data_train/README.md).
