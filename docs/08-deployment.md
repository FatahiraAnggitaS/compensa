# Deployment

## 1. Deployment goal

Production memakai satu Laravel application service dan satu managed PostgreSQL database. Python hanya diperlukan untuk offline training, bukan request-time inference.

Pendekatan deployment tetap managed PaaS. Provider spesifik dipilih setelah MVP lulus local tests melalui review dukungan Laravel/PHP, PostgreSQL, persistent release files, biaya, dan service limits terbaru. Dokumen tidak mengklaim deployment aktif.

## 2. Local environment

Target local flow:

1. Gunakan Laravel Herd untuk melayani project.
2. Jalankan `composer install` dari `composer.lock`.
3. Jalankan `npm ci` dari `package-lock.json`.
4. Siapkan `.env` lokal tanpa memasukkan secret ke Git.
5. Jalankan Laravel migrations ke SQLite.
6. Buat isolated Python 3.13 environment untuk `ml/`.
7. Install pinned Python training dependencies dari `ml/requirements.txt`.
8. Generate trusted JSON artifact dari verified dataset atau gunakan artifact aman yang disertakan.
9. Jalankan PHP/Python tests, parity checks, lint, dan frontend build.

Command final ditulis pada root README setelah implementation tersedia dan benar-benar dijalankan.

Milestone 1 menyediakan setup minimum dan quality commands pada root README. Training dan release commands ditambahkan setelah implementation pemiliknya tersedia.

## 3. Configuration contract

Laravel configuration memakai environment variables untuk:

- application key, environment, debug flag, URL, dan timezone;
- PostgreSQL connection configuration sesuai provider;
- fixed trusted JSON artifact path.

Nama exact custom artifact variable ditetapkan saat Laravel configuration diimplementasikan. `.env` tidak di-commit. `.env.example` hanya berisi placeholder aman.

Timestamp disimpan timezone-aware dalam UTC dan ditampilkan sebagai `Asia/Jakarta`.

## 4. Model delivery

- Dataset tidak diperlukan oleh production web runtime.
- Release membawa satu trusted `artifacts/salary_linear_regression.json`.
- Artifact memuat coefficients, intercept, feature contract, ranges, provenance, evaluation metadata, dan model version.
- Laravel memvalidasi strict schema dan model version sebelum inference.
- Artifact hanya diganti melalui documented Python training/release process.
- JSON artifact boleh di-commit hanya setelah dataset license, artifact content, size, dan repository policy diverifikasi.
- Production tidak menginstall atau menjalankan Python bila artifact sudah disertakan.

## 5. Database

- SQLite digunakan untuk local development dan single-user demo.
- Deployment memakai managed PostgreSQL sebagai persistent production database.
- Laravel migrations berjalan sebagai explicit release step.
- Default skeleton migrations direview sebelum perubahan destructive; dokumentasi ini tidak menghapus tabel atau file.
- Backup policy mengikuti target provider dan harus diuji sebelum menyimpan data non-demo.

## 6. Static files and reports

- Tailwind CSS dan JavaScript dibangun memakai existing Vite pipeline.
- Production melayani files dari Laravel `public/` sesuai contract provider.
- CSV dibuat melalui streamed Laravel response.
- XLSX dibuat melalui direct PhpSpreadsheet integration setelah compatibility verification.
- Print/PDF dibuat oleh browser; tidak memerlukan server PDF engine.

## 7. Security baseline

- `APP_DEBUG` dimatikan pada deployment.
- Application key dan database credentials hanya melalui environment.
- HTTPS ditangani deployment platform/reverse proxy.
- Laravel CSRF protection tetap aktif.
- Validation dan authorization boundaries tetap di server walaupun MVP belum memiliki login.
- Error response tidak mengekspos stack trace.
- CSV/XLSX export menetralkan spreadsheet formula injection.
- JSON artifact berasal dari fixed trusted path dan tidak dapat di-upload user.
- Laravel logging dikirim ke stderr/stdout atau provider-supported log channel. Log tidak memuat full salary inputs, credentials, secrets, atau unnecessary personal data.

Karena MVP tidak memiliki authentication, public deployment hanya boleh memakai demo data non-sensitif. Penggunaan data employee nyata membutuhkan authentication, authorization, privacy review, dan scope baru.

## 8. Release checks

1. Composer install berhasil dari lockfile.
2. NPM clean install dan Vite production build berhasil dari lockfile.
3. Laravel tests dan Pint checks lulus.
4. Python ML tests dan Ruff checks lulus.
5. Migration plan direview dan dapat diterapkan pada clean database.
6. JSON artifact schema, model version, dan Python/PHP parity checks lulus.
7. Model Information menampilkan metric aktual dari artifact yang sama.
8. Secret, untracked-file, dataset-license, dan artifact-content review selesai.
9. Smoke test prediction, save, history, CSV, XLSX, dan print selesai.

## 9. Rollback

- Simpan previous Laravel release dan previous trusted JSON artifact.
- Rollback application dan artifact sebagai satu release unit.
- Database migration destructive memerlukan backup serta explicit rollback plan.
- MVP menghindari destructive schema changes selama additive migration masih memadai.
- Python tidak menyimpan mutable production state sehingga training tool rollback tidak memengaruhi application database.
