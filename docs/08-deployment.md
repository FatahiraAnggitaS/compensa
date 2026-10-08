# Deployment

## 1. Release status and target

Compensa berstatus **complete locally / Laravel Cloud deployment-ready**. Target deployment adalah Laravel Cloud Starter, region Singapore, smallest hibernating application compute, dan Serverless PostgreSQL. Repository belum terhubung ke account/deployment aktif; karena itu live URL dan PostgreSQL smoke test tetap **pending**, bukan dianggap lulus.

Python hanya digunakan untuk offline training. Production membawa fixed trusted JSON artifact dan tidak menginstall Python atau menyimpan model pada writable filesystem.

## 2. Laravel Cloud dashboard configuration

### Application

- Connect repository Compensa dan pilih branch release.
- Region: Singapore.
- Runtime: PHP 8.4.
- Compute: smallest hibernating application size pada Starter.
- Health check path: `/up`.
- Attach Serverless PostgreSQL; gunakan credentials/environment variables yang di-inject Laravel Cloud.

Required PHP extensions mengikuti Composer/platform checks: BCMath, Ctype, DOM, Fileinfo, Filter, GD, Iconv, Libxml, Mbstring, PDO PostgreSQL, SimpleXML, XML, XMLReader, XMLWriter, ZIP, dan Zlib.

### Build command

```bash
npm ci && npm run build
```

Platform tetap menjalankan Composer install dari `composer.lock`. Production build tidak menjalankan training; `artifacts/salary_linear_regression.json` sudah menjadi release asset.

### Deploy commands

Jalankan berurutan setelah build:

```bash
php artisan migrate --force
php artisan app:seed-demo-data
php artisan optimize
```

Seed command idempotent dan hanya menyinkronkan tiga employee fiktif. Command tidak membuat salary record atau metric.

### Environment variables

```dotenv
APP_NAME=Compensa
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<assigned-domain>
APP_TIMEZONE=UTC
APP_PUBLIC_DEMO=true

LOG_CHANNEL=stderr
LOG_LEVEL=warning
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

`APP_KEY` harus dibuat sebagai secret. Database variables berasal dari attached PostgreSQL resource. Jangan menyimpan `.env`, key, atau database credentials dalam Git.

Session dan cache memakai tabel database dari default Laravel migrations. Queue tetap synchronous; tidak ada worker, scheduler, Redis, object storage, atau server-side PDF service.

## 3. Public demo behavior

Saat `APP_PUBLIC_DEMO=true`:

- employee list/detail tetap dapat dibaca;
- create, edit, update, serta status mutation disembunyikan dan ditolak HTTP 403;
- prediction tetap dapat dibuat untuk employee aktif hasil demo seed;
- halaman menampilkan banner bahwa data publik dan wajib fiktif;
- prediction dibatasi 10 request/menit/IP;
- CSV/XLSX/print masing-masing dibatasi 20 request/menit/IP.

Response global membawa `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, dan restrictive `Permissions-Policy`. Mode lokal tetap full-write karena default `APP_PUBLIC_DEMO=false`.

Tanpa authentication, deployment tidak boleh menerima data employee nyata. Authentication, authorization, privacy policy, dan retention policy adalah scope baru sebelum penggunaan non-demo.

## 4. Model and static asset delivery

- Artifact berada pada fixed committed path `artifacts/salary_linear_regression.json`.
- Request tidak menerima upload/path model dan tidak menjalankan Python/training.
- Laravel memvalidasi schema, contract, model version, finite numbers, dan parity structure sebelum inference.
- Vite menghasilkan asset production pada build step.
- CSV di-stream, XLSX dibuat request-time dengan PhpSpreadsheet, dan PDF dibuat melalui browser Print/Save as PDF.
- Production tidak bergantung pada persistent application filesystem.

## 5. Pre-deploy gates

Sebelum membuat deployment:

1. Composer install/validate/audit dari lockfile lulus.
2. NPM clean install, high-severity audit, dan production build lulus.
3. Pest, Pint, Python unittest, serta Ruff lint/format lulus.
4. Training terhadap verified dataset mempertahankan model version dan PHP/Python parity.
5. SQLite migrate-fresh/rollback dan production-mode smoke lulus.
6. Config, route, serta view cache dapat dibangun.
7. Secret, ignored environment/database, archive dataset, artifact/hash, license, screenshots, dan generated-file diff direview.

## 6. First deployment smoke checklist

Checklist ini wajib dijalankan pada URL dan PostgreSQL nyata setelah resource tersedia:

- `/up` healthy dan halaman error tidak menampilkan debug trace;
- public-demo banner tampil;
- employee list/detail tampil, sedangkan seluruh mutation route memberi 403;
- satu prediction valid tersimpan dan muncul di history/detail;
- monthly report, CSV, XLSX, dan print memakai record yang sama;
- response security headers tersedia;
- throttle prediction/export menghasilkan 429 setelah batasnya;
- database tetap ada setelah redeploy/hibernate-wake;
- log tersedia di stderr tanpa secret atau unnecessary salary input.

Status saat dokumen ini dibuat: **pending — no active Laravel Cloud account/deployment**.

## 7. Rollback

- Gunakan previous successful application release dan artifact sebagai satu release unit.
- Jangan rollback migration yang berpotensi truncation tanpa guard/backup.
- Serverless PostgreSQL backup/restore mengikuti fasilitas Laravel Cloud dan harus diverifikasi sebelum menyimpan state non-demo.
- Setelah rollback, jalankan `/up`, prediction read path, history, dan report smoke.

## 8. Infrastructure intentionally excluded

MVP tidak menambah Dockerfile, worker, scheduler, Redis, queue service, object storage, model service, filesystem persistence, ataupun server-side PDF engine. Penambahan komponen hanya dilakukan saat requirement baru membuktikan kebutuhannya.
