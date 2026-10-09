# Deployment

## 1. Target

Target adalah Laravel Cloud Starter region Singapore dengan PHP 8.4 dan Serverless PostgreSQL. Live URL dan PostgreSQL smoke masih pending.

Python hanya digunakan untuk offline training. Production membawa committed JSON artifact dan tidak menginstall Python.

## 2. Build dan deploy

Build:

```bash
npm ci && npm run build
```

Deploy:

```bash
php artisan migrate --force
php artisan optimize
```

Environment utama:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_PUBLIC_DEMO=true
LOG_CHANNEL=stderr
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

## 3. Public demo

- Tidak ada seed atau data master employee yang diperlukan.
- Prediction menerima nama employee langsung.
- Prediction dibatasi 10 request/menit/IP.
- History bersifat publik; gunakan hanya data fiktif.
- Response membawa defensive security headers.

Tanpa authentication, deployment tidak boleh menerima employee atau salary data nyata.

## 4. Artifact

- Fixed path: `artifacts/salary_linear_regression.json`.
- Request tidak menerima upload/path artifact.
- Laravel memvalidasi schema, contract, model version, finite values, metrics, dan parity structure.
- Missing atau incompatible artifact memblokir prediction dengan safe error.

## 5. Pre-deploy gates

1. Composer validate/audit.
2. NPM audit dan build.
3. Pest, Pint, Python unittest, dan Ruff.
4. Training mempertahankan model version dan parity.
5. SQLite migrate fresh/rollback.
6. Config, route, dan view cache.
7. Secret, dataset, artifact, license, dan generated-file review.

## 6. Smoke checklist

- `/up` healthy.
- Form prediction menerima nama fiktif tanpa setup employee.
- Prediction valid tersimpan.
- History list/detail menampilkan hasil yang sama.
- Invalid input tidak tersimpan.
- Security headers dan prediction throttle aktif.
- Database tetap persisten setelah redeploy.

## 7. Rollback

Gunakan previous application release dan artifact sebagai satu unit. Nullable legacy-field migration menolak rollback bila pure prediction records tersedia. Migration nama employee menolak rollback bila record baru memiliki `employee_id` null. Backup database sebelum schema rollback; rollback aplikasi ke versi Employee Management memerlukan pemetaan data yang eksplisit.

## 8. Excluded infrastructure

Tidak ada Dockerfile, worker, scheduler, Redis, queue service, object storage, model service, report service, atau server-side PDF engine.
