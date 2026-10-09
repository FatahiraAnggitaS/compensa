# Decision Log

Dokumen ini mencatat keputusan material hasil Q&A. `Accepted with conditions` hanya berlaku bila conditions tercantum terpenuhi. `Required` berasal langsung dari requirement awal. `Superseded` dipertahankan sebagai history dan tidak menjadi active implementation contract.

## D-001: Python Django modular monolith

- Date: 2026-10-07
- Status: Superseded by D-054
- Decision: Gunakan Django server-rendered application dan tempatkan ML modules pada repository/runtime yang sama.
- Reason: Menghindari dua language runtimes, SPA, dan ML microservice pada greenfield MVP.
- Consequence: Web process memuat trusted scikit-learn artifact; training tetap explicit offline command.

## D-002: SQLite local, PostgreSQL when deployed

- Date: 2026-10-07
- Status: Accepted, amended by D-054
- Decision: Gunakan SQLite untuk local development dan PostgreSQL bila deployment memerlukan persistent external database.
- Reason: Local setup minimum tanpa mengunci deployment pada file database ephemeral.
- Consequence: Models dan queries harus portable melalui Laravel Eloquent ORM.

## D-003: Separate training and inference

- Date: 2026-10-07
- Status: Superseded by D-055
- Decision: Training menghasilkan trusted joblib artifact plus JSON metadata. HTTP prediction hanya melakukan inference.
- Reason: Reproducibility, latency, dan pencegahan training-serving drift.
- Consequence: Artifact lifecycle menjadi bagian release checks.

## D-004: Operator supplies applicable work days

- Date: 2026-10-07
- Status: Accepted
- Decision: Operator memasukkan applicable work days dan worked days untuk period dalam satu calendar month.
- Reason: MVP tidak memiliki holiday, attendance, atau work-calendar source.
- Consequence: UI harus menjelaskan field dan backend memvalidasi ratio.

## D-005: Overtime rate is direct input

- Date: 2026-10-07
- Status: Required
- Decision: `overtime_pay = overtime_hours * overtime_rate`; aplikasi tidak membuat rate formula.
- Reason: Requirement tidak menetapkan formula tarif lembur.
- Consequence: Overtime rate tersimpan sebagai calculation snapshot.

## D-006: Salary records are immutable snapshots

- Date: 2026-10-07
- Status: Accepted
- Decision: Correction membuat record baru. Satu employee dapat memiliki beberapa record pada reporting month yang sama.
- Reason: Menjaga setiap hasil prediction/calculation tersimpan sebagai history tanpa workflow versioning kompleks.
- Consequence: Monthly report menampilkan seluruh matching records, bukan satu payable record.

## D-007: Browser print is MVP PDF path

- Date: 2026-10-07
- Status: Accepted
- Decision: Sediakan print-optimized HTML untuk Print/Save as PDF.
- Reason: Memenuhi kebutuhan PDF/Print tanpa PDF engine dan system dependency tambahan.
- Consequence: Layout diuji manual pada supported browser; tidak ada downloadable server PDF pada MVP.

## D-008: No authentication means demo-only deployment

- Date: 2026-10-07
- Status: Required
- Decision: Deployment tanpa authentication hanya memakai demo data non-sensitif.
- Reason: Employee dan salary data nyata memerlukan access control dan privacy scope yang belum tersedia.
- Consequence: Real organizational use dilarang sampai security scope diperluas.

## D-009: Work period stays within one reporting month

- Date: 2026-10-07
- Status: Superseded by D-081
- Decision: `period_start` dan `period_end` wajib berada dalam satu reporting month.
- Reason: Menjaga monthly report dan proration tetap jelas tanpa automatic period splitting.
- Consequence: Periode lintas bulan dibuat sebagai calculation terpisah untuk setiap bulan.

## D-010: Minimum employee identity

- Date: 2026-10-07
- Status: Accepted
- Decision: Employee menyimpan unique employee code, full name, dan active status.
- Reason: Field tersebut cukup untuk ownership, search, dan history tanpa data personal yang tidak diperlukan.
- Consequence: Email, department, job title, dan data personal lain tidak menjadi bagian MVP.

## D-011: Server-rendered frontend without Node build

- Date: 2026-10-07
- Status: Superseded by D-056
- Decision: Gunakan Django templates, project CSS, dan minimal vanilla JavaScript.
- Reason: Menjaga frontend mudah dipahami dan menghindari build pipeline yang tidak dibutuhkan core flow.
- Consequence: Bootstrap, Tailwind CSS, SPA framework, dan Node tooling tidak masuk MVP.

## D-012: Repository includes trusted model artifact when safe

- Date: 2026-10-07
- Status: Accepted with conditions
- Decision: Commit model artifact dan metadata agar reviewer dapat langsung menjalankan inference.
- Reason: Mengurangi setup demo sambil tetap menyediakan training command untuk regenerasi.
- Conditions: Artifact harus berukuran wajar, dibuat project, lolos integrity review, dan tidak melanggar dataset license atau repository policy.
- Consequence: Jika salah satu condition gagal, artifact tidak di-commit dan local training menjadi documented prerequisite.

## D-013: Managed PaaS deployment

- Date: 2026-10-07
- Status: Superseded by D-057
- Decision: Deploy web application pada managed Python PaaS dan gunakan managed PostgreSQL.
- Reason: Mengurangi operational scope untuk portfolio MVP.
- Consequence: Provider spesifik dipilih kemudian berdasarkan biaya, persistence, dan service limits yang berlaku.

## D-014: Dataset supplied by project owner

- Date: 2026-10-07
- Status: Accepted
- Decision: Pemilik project menyediakan dataset training beserta source dan license information.
- Reason: Feature, target, permission, dan provenance harus diverifikasi dari data yang memang dipilih untuk project.
- Consequence: ML implementation tetap blocked sampai file dan informasi tersebut tersedia.

## D-015: Monthly IDR model target

- Date: 2026-10-07
- Status: Accepted
- Decision: Target model wajib merepresentasikan monthly base salary dalam IDR.
- Reason: Memberi contract jelas untuk proration, currency formatting, dan monthly reporting.
- Consequence: Dataset dengan annual, hourly, daily, non-IDR, mixed, atau unknown target ditolak sampai requirement diubah secara eksplisit.

## D-016: Two-decimal IDR storage

- Date: 2026-10-07
- Status: Accepted
- Decision: Seluruh monetary fields memakai `Decimal(18,2)` dan `ROUND_HALF_UP`.
- Reason: Menjaga calculation deterministik dan prediction dapat ditelusuri tanpa binary floating-point arithmetic.
- Consequence: UI dan exports menampilkan IDR dengan 2 decimal; total dihitung dari rounded stored components.

## D-017: Negative prediction blocks calculation

- Date: 2026-10-07
- Status: Accepted
- Decision: Tampilkan negative prediction sebagai model limitation, lalu blokir salary calculation dan record save.
- Reason: Menjaga raw model behavior terlihat tanpa membuat salary record tidak masuk akal atau melakukan arbitrary clamping.
- Consequence: Input dan model/data perlu diperiksa sebelum user dapat menyimpan calculation.

## D-018: Allow flagged out-of-distribution prediction

- Date: 2026-10-07
- Status: Accepted
- Decision: Izinkan prediction di luar observed training range, tampilkan warning, dan simpan `has_ood_input` pada salary record.
- Reason: Menjaga flow tetap dapat digunakan sambil membuat extrapolation risk terlihat dan dapat ditelusuri.
- Consequence: Sistem tidak clamp input; Prediction History detail harus menampilkan OOD status.

## D-019: Single-submit salary workflow

- Date: 2026-10-07
- Status: Accepted
- Decision: Satu server-rendered form dan satu submit menjalankan prediction, calculation, serta save.
- Reason: Menghindari state antar-langkah dan prediction API yang tidak diperlukan MVP.
- Consequence: Backend memvalidasi seluruh input dan menyimpan record secara atomik; failure tidak membuat partial record.

## D-020: Django management command for training

- Date: 2026-10-07
- Status: Superseded by D-055
- Decision: Training source of truth dijalankan melalui `python manage.py train_salary_model --dataset <path>`.
- Reason: Entry point mudah direproduksi, diuji, dan memakai project settings/path contract yang sama.
- Consequence: Notebook hanya boleh digunakan untuk eksplorasi; notebook tidak menghasilkan artifact release secara manual.

## D-021: Pinned requirements.txt

- Date: 2026-10-07
- Status: Accepted, amended by D-055
- Decision: Simpan Python training dependencies dengan exact versions dalam `ml/requirements.txt`.
- Reason: Reviewer dapat memakai standard `pip` tanpa package manager tambahan.
- Consequence: Dependency update dilakukan eksplisit dan seluruh test dijalankan kembali sebelum version pin berubah.

## D-022: Built-in Django test framework

- Date: 2026-10-07
- Status: Superseded by D-058
- Decision: Gunakan Django test framework dan standard-library `unittest`.
- Reason: Cukup untuk model, form, view, database, service, dan management command tests tanpa dependency tambahan.
- Consequence: pytest dan pytest-django tidak masuk dependency MVP.

## D-023: Minimal GitHub Actions CI

- Date: 2026-10-07
- Status: Superseded by D-059
- Decision: GitHub Actions menjalankan Django tests dan system checks pada repository events yang dipilih saat implementation.
- Reason: Memberi verification otomatis dengan satu runtime/job yang mudah dipahami.
- Consequence: Multi-OS dan multi-version matrix tidak masuk MVP; workflow dibuat setelah GitHub remote tersedia.

## D-024: Ruff for lint and format

- Date: 2026-10-07
- Status: Accepted, amended by D-058
- Decision: Gunakan Ruff untuk Python linting dan formatting pada folder `ml/`.
- Reason: Satu tool dan satu konfigurasi cukup untuk quality checks MVP.
- Consequence: Black dan Flake8 tidak ditambahkan; CI menjalankan Ruff checks.

## D-025: CSV-only training dataset

- Date: 2026-10-07
- Status: Accepted
- Decision: Training command menerima satu dataset file dalam format CSV UTF-8.
- Reason: Format mudah diperiksa, diproses pandas, dan tidak memiliki sheet ambiguity.
- Consequence: Excel dan format dataset lain ditolak pada MVP.

## D-026: Repository includes dataset when publication is safe

- Date: 2026-10-07
- Status: Accepted with conditions
- Decision: Commit dataset CSV agar reviewer dapat mereproduksi training dan metrics.
- Reason: Reproducibility lebih kuat ketika source data tersedia bersama training code.
- Conditions: License mengizinkan redistribusi, ukuran wajar, provenance terdokumentasi, dan dataset tidak memuat data personal/sensitif yang tidak boleh dipublikasikan.
- Consequence: Jika satu condition gagal, dataset masuk `.gitignore` dan limitation reproducibility dijelaskan eksplisit.

## D-027: Fixed mapping preserves raw dataset headers

- Date: 2026-10-07
- Status: Accepted
- Decision: Pertahankan raw CSV headers dan buat satu fixed mapping ke canonical feature/target names setelah dataset inspection.
- Reason: Menjaga source data utuh serta membuat training/inference contract eksplisit.
- Consequence: Mapping tersimpan dalam training code/config tetap dan artifact metadata; mapping tidak diberikan bebas melalui CLI.

## D-028: Missing values fail until reviewed

- Date: 2026-10-07
- Status: Accepted
- Decision: Training validation gagal ketika feature/target memiliki missing value sampai data profile menghasilkan explicit cleaning decision.
- Reason: Mencegah silent row deletion atau imputation tanpa bukti.
- Consequence: Drop atau imputation hanya dapat ditambahkan bersama alasan dan before/after row counts.

## D-029: Duplicate rows fail until reviewed

- Date: 2026-10-07
- Status: Accepted
- Decision: Training validation melaporkan exact duplicate count dan gagal sampai duplicate direview.
- Reason: Identical feature/target rows dapat berupa observasi sah atau data duplication; sistem tidak dapat menentukannya tanpa context.
- Consequence: Keputusan keep/drop harus dicatat bersama alasan dan before/after row counts.

## D-030: Evaluation strategy follows data profile

- Date: 2026-10-07
- Status: Accepted, amended by D-070
- Decision: Tentukan split ratio, random seed, dan penggunaan cross-validation setelah ukuran serta distribusi dataset diperiksa.
- Reason: Fixed 80/20 atau cross-validation dapat tidak sesuai untuk jumlah sample aktual.
- Consequence: Training implementation tetap blocked pada keputusan evaluation sampai data profile tersedia; konfigurasi final wajib masuk metadata.

## D-031: Monthly report includes selected-record totals

- Date: 2026-10-07
- Status: Accepted
- Decision: Monthly report menampilkan detail records, record count, dan total calculated base salary, overtime pay, serta estimated total salary.
- Reason: Memberi ringkasan yang berguna tanpa menyembunyikan calculation history.
- Consequence: Summary diberi label “total selected records” karena employee dapat muncul lebih dari sekali.

## D-032: Keep ML details out of monthly report

- Date: 2026-10-07
- Status: Accepted
- Decision: Monthly report fokus pada salary fields; model inputs, model version, dan OOD status tetap tersedia pada Prediction History detail.
- Reason: Menjaga laporan atasan ringkas tanpa menghilangkan technical traceability.
- Consequence: Report export tidak menyediakan selectable technical columns pada MVP.

## D-033: Minimal history filters

- Date: 2026-10-07
- Status: Accepted
- Decision: Prediction History menyediakan filter employee dan reporting month.
- Reason: Dua filter tersebut memenuhi pencarian utama tanpa query/filter UI berlebih.
- Consequence: Date range, OOD, model version, dan salary range filters tidak masuk MVP.

## D-034: Direct openpyxl Excel export

- Date: 2026-10-07
- Status: Superseded by D-060
- Decision: Monthly report membuat `.xlsx` memakai openpyxl langsung.
- Reason: Cell types dan formatting dapat dikontrol tanpa membawa DataFrame ke reporting layer.
- Consequence: Django import-export package dan pandas `ExcelWriter` tidak digunakan untuk report.

## D-035: Gunicorn WSGI application server

- Date: 2026-10-07
- Status: Superseded by D-057
- Decision: Managed PaaS menjalankan Django melalui Gunicorn WSGI.
- Reason: MVP memakai synchronous request flow tanpa WebSocket atau async workload.
- Consequence: Uvicorn/ASGI dan Django development server tidak digunakan sebagai production server.

## D-036: WhiteNoise for static files

- Date: 2026-10-07
- Status: Superseded by D-057
- Decision: Gunakan WhiteNoise untuk melayani collected static files pada deployment.
- Reason: CSS dan JavaScript MVP kecil serta tidak memerlukan object storage/CDN.
- Consequence: Static files tetap melalui `collectstatic`; provider-specific static server tidak menjadi requirement.

## D-037: Standard application logging to stdout

- Date: 2026-10-07
- Status: Accepted, amended by D-057
- Decision: Gunakan Laravel logging ke stdout/stderr atau provider-supported channel.
- Reason: Menghindari local file rotation dan monitoring service tambahan.
- Consequence: Log tidak boleh memuat full salary inputs, credentials, secrets, atau unnecessary personal data.

## D-038: Idempotent employee demo seed

- Date: 2026-10-07
- Status: Superseded by D-061
- Decision: Sediakan `python manage.py seed_demo_data` yang idempotent untuk membuat employee fiktif berlabel demo.
- Reason: Reviewer dapat mencoba core flow tanpa setup data manual.
- Consequence: Command tidak membuat fake salary records atau metric; calculation tetap berasal dari artifact aktual.

## D-039: Lazy cached model loading

- Date: 2026-10-07
- Status: Superseded by D-062
- Decision: Load dan validate trusted model artifact saat prediction pertama, lalu cache per web process.
- Reason: Menghindari repeated deserialization tanpa membuat seluruh application gagal start ketika artifact bermasalah.
- Consequence: Halaman non-prediction tetap tersedia; prediction menampilkan safe operational error sampai artifact diperbaiki.

## D-040: Prediction form as home page

- Date: 2026-10-07
- Status: Accepted
- Decision: Root page menampilkan Salary Prediction & Calculation form.
- Reason: Core value dapat dicoba tanpa melewati dashboard atau marketing page.
- Consequence: Dashboard statistics dan separate landing page tidak masuk MVP.

## D-041: Indonesian user interface

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan Bahasa Indonesia untuk UI, report labels, validation messages, dan disclaimer.
- Reason: Selaras dengan IDR dan konteks penggunaan lokal.
- Consequence: Code identifiers dan istilah teknis seperti Linear Regression tetap English; language switcher tidak masuk MVP.

## D-042: UTC persistence with Asia/Jakarta display

- Date: 2026-10-07
- Status: Accepted
- Decision: Simpan timestamp timezone-aware dalam UTC dan tampilkan sebagai `Asia/Jakarta` (WIB).
- Reason: Timestamp tetap konsisten pada local dan managed PaaS environments.
- Consequence: User-configurable timezone tidak masuk MVP.

## D-043: HTML month picker for reporting month

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan satu HTML month picker yang mengirim `YYYY-MM` untuk reporting month.
- Reason: Input ringkas dan selaras dengan single-month record contract.
- Consequence: Server tetap memvalidasi format dan memastikan period dates berada dalam bulan tersebut.

## D-044: User-provided normalized employee code

- Date: 2026-10-07
- Status: Accepted
- Decision: User mengisi employee code; server trim whitespace, normalize ke uppercase, dan enforce uniqueness.
- Reason: Mendukung existing organization code tanpa membuat identity scheme baru atau case-only duplicates.
- Consequence: Aplikasi tidak menyediakan automatic `EMP-0001` generator pada MVP.

## D-045: Two-decimal overtime hours

- Date: 2026-10-07
- Status: Accepted
- Decision: Overtime hours menerima non-negative decimal sampai 2 angka di belakang koma.
- Reason: Mendukung partial-hour input tanpa mengarang interval kebijakan perusahaan.
- Consequence: Backend tidak membulatkan overtime ke 0,5 jam atau whole hour.

## D-046: Two-decimal years of experience

- Date: 2026-10-07
- Status: Accepted
- Decision: `years_of_experience` menerima non-negative decimal sampai 2 angka di belakang koma.
- Reason: Mendukung pengalaman parsial tanpa conversion dari bulan yang tidak ada pada model contract.
- Consequence: Valid range tetap berasal dari verified dataset metadata.

## D-047: Score precision follows dataset schema

- Date: 2026-10-07
- Status: Accepted, amended by D-070
- Decision: Integer/decimal type, decimal places, dan valid range `knowledge_score`, `technical_score`, serta `logical_score` mengikuti verified dataset schema.
- Reason: Requirement hanya menetapkan input numeric dan tidak mendefinisikan score scale.
- Consequence: Form fields dan database precision untuk tiga score diselesaikan setelah dataset inspection.

## D-048: Initialize Git before implementation

- Date: 2026-10-07
- Status: Accepted
- Decision: Inisialisasi Git setelah Q&A dan planning review selesai, sebelum implementation dimulai.
- Reason: Planning menjadi baseline dan implementation history tetap jelas.
- Consequence: Git initialization belum dilakukan selama decision Q&A masih berjalan.

## D-049: MIT License for source code

- Date: 2026-10-07
- Status: Accepted
- Decision: Publikasikan source code project dengan MIT License.
- Reason: Public portfolio memiliki reuse terms yang jelas dan sederhana.
- Consequence: Dataset dan model artifact tetap tunduk pada source dataset license; MIT tidak otomatis mencakup hak redistribusi data.

## D-050: Verify compatibility before pinning runtime versions

- Date: 2026-10-07
- Status: Superseded by D-063
- Decision: Pilih stable Python/Django versions setelah memeriksa official compatibility dengan scikit-learn, Gunicorn, WhiteNoise, dan target PaaS; lalu pin exact versions.
- Reason: Repository belum memiliki runtime/dependency manifest dan compatibility dapat berubah.
- Consequence: Dokumentasi tidak mengarang version numbers sebelum verification.

## D-051: Select deployment provider after local MVP verification

- Date: 2026-10-07
- Status: Accepted
- Decision: Pilih managed PaaS provider setelah MVP lulus local automated tests dan smoke tests.
- Reason: Provider recommendation harus memakai runtime support, PostgreSQL offering, persistence, biaya, dan limits terbaru.
- Consequence: Provider research dan production-specific configuration berada pada Milestone 6.

## D-052: Fixed active artifact paths with metadata versioning

- Date: 2026-10-07
- Status: Superseded by D-055
- Decision: Runtime membaca fixed artifact dan metadata filenames; `model_version` serta SHA-256 disimpan dalam metadata.
- Reason: Path configuration tetap sederhana tanpa database binary atau active-pointer mechanism.
- Consequence: Release harus memverifikasi dan mengganti artifact/metadata pair secara atomik; application release menyimpan previous pair untuk rollback.

## D-053: Planning baseline approved with blockers

- Date: 2026-10-07
- Status: Superseded by D-064
- Decision: Scope, architecture, business rules, quality strategy, dan roadmap MVP menjadi approved planning baseline.
- Reason: Q&A MVP selesai dan cross-document audit telah dilakukan.
- Consequence: Implementation belum dimulai. Dataset facts, package versions, dan deployment provider tetap diselesaikan pada milestone yang tercantum.

## D-054: Laravel monolith with offline Python training

- Date: 2026-10-07
- Status: Accepted, amended by D-071
- Decision: Gunakan existing Laravel `^13.17` application sebagai web monolith. Python hanya menangani offline scikit-learn training dan JSON export.
- Reason: Repository aktual sudah memakai Laravel, PHP `^8.3`, Blade, Eloquent, Pest, Pint, Tailwind, dan Vite; tidak ada Django code.
- Consequence: Laravel memiliki HTTP, validation, database, salary logic, reporting, dan request-time inference. Django tidak digunakan.

## D-055: Self-contained JSON Linear Regression artifact

- Date: 2026-10-07
- Status: Accepted
- Decision: `python ml/train.py --dataset <path>` mengekspor satu fixed `artifacts/salary_linear_regression.json` berisi coefficients, intercept, feature/target contract, ranges, provenance, configuration, metrics, dan model version.
- Reason: Laravel dapat menjalankan deterministic Linear Regression inference tanpa Python service atau unsafe deserialization.
- Consequence: `joblib`, Django management command, dan separate metadata file tidak digunakan runtime. Learned preprocessing memerlukan architecture review.

## D-056: Existing Blade, Tailwind, and Vite frontend

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan Blade, existing Tailwind CSS/Vite pipeline, dan minimal vanilla JavaScript.
- Reason: Stack tersebut sudah menjadi bagian template dan lockfiles aktual.
- Consequence: Tidak ada SPA framework. Node tetap diperlukan untuk asset build.

## D-057: Herd local and Laravel managed-PaaS deployment

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan Laravel Herd untuk local web serving dan managed Laravel/PHP PaaS plus managed PostgreSQL untuk production.
- Reason: Selaras dengan repository dan local environment aktual.
- Consequence: Gunicorn, WhiteNoise, dan production Python web runtime tidak digunakan. Vite menghasilkan production assets pada `public/`.

## D-058: Pest/Pint for Laravel and unittest/Ruff for ML

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan Pest dan Pint untuk Laravel; standard-library `unittest` dan Ruff untuk offline Python ML code.
- Reason: PHP tools sudah tersedia dan Python toolchain tetap minimum.
- Consequence: Django test framework, pytest, Black, dan Flake8 tidak digunakan.

## D-059: Cross-runtime GitHub Actions CI

- Date: 2026-10-07
- Status: Accepted
- Decision: CI menjalankan Composer install, Pint, Pest, Python unittest/Ruff, parity tests, NPM install, dan Vite build.
- Reason: Repository memiliki Laravel runtime, isolated Python training code, dan frontend build.
- Consequence: CI tetap satu minimal workflow tanpa multi-OS/version matrix.

## D-060: Direct PhpSpreadsheet Excel export

- Date: 2026-10-07
- Status: Accepted with compatibility verification
- Decision: Laravel membuat `.xlsx` melalui direct PhpSpreadsheet integration.
- Reason: Mempertahankan keputusan direct spreadsheet library tanpa menjalankan Python/pandas pada reporting layer.
- Consequence: Package version dipilih setelah official PHP/Laravel compatibility verification; Laravel Excel/import-export abstraction tidak digunakan.

## D-061: Artisan demo seed command

- Date: 2026-10-07
- Status: Accepted, scheduling amended by D-072
- Decision: Sediakan idempotent `php artisan app:seed-demo-data` untuk membuat employee fiktif berlabel demo.
- Reason: Menyesuaikan demo setup dengan Laravel runtime.
- Consequence: Command tidak membuat fake salary records atau metrics.

## D-062: Strict JSON load during prediction

- Date: 2026-10-07
- Status: Accepted
- Decision: Laravel membaca dan memvalidasi small JSON artifact ketika prediction dijalankan; object dapat digunakan kembali selama request yang sama.
- Reason: PHP request lifecycle tidak menyediakan reliable cross-request in-memory singleton tanpa cache service tambahan.
- Consequence: Tidak ada repeated unsafe deserialization, persistent cache, atau per-request Python process.

## D-063: Verify Laravel/PHP and Python training compatibility

- Date: 2026-10-07
- Status: Superseded by D-071
- Decision: Pertahankan existing Composer/NPM lockfiles; pilih dan pin Python/scikit-learn versions setelah official compatibility checks. Verifikasi package PHP tambahan sebelum install.
- Reason: Repository sudah memiliki PHP/Node dependency baseline, sedangkan Python dependencies belum tersedia.
- Consequence: Dokumentasi tidak mengarang Python atau PhpSpreadsheet version numbers.

## D-064: Revised Laravel planning baseline approved with blockers

- Date: 2026-10-07
- Status: Accepted, amended by D-071
- Decision: Laravel monolith, offline Python training, dan JSON inference menjadi planning baseline pengganti Django architecture.
- Reason: Baseline sekarang mengikuti repository aktual dan pilihan terbaru pemilik project.
- Consequence: Belum ada business code atau data migration. Dataset facts selesai pada Milestone 0 dan dependency compatibility selesai pada Milestone 1; decimal implementation serta provider tetap diselesaikan pada milestone terkait.

## D-065: No-data responsive UI scaffold before business implementation

- Date: 2026-10-07
- Status: Accepted, amended by D-073
- Decision: Siapkan lima Blade pages, shared responsive navigation, invokable GET controllers, dan honest empty states sebelum database serta business logic diimplementasikan.
- Reason: Struktur frontend/backend dan arah visual dapat divalidasi lebih awal tanpa mengarang data, metric, atau hasil prediksi.
- Consequence: Action prediction, persistence, data filter, dan export disabled; tidak ada POST route, model, migration, query, ataupun fake salary record pada tahap ini.

## D-066: Candidate dataset requires semantic and cleaning approval

- Date: 2026-10-07
- Status: Superseded by D-068
- Decision: Perlakukan `data_train/salary.csv` sebagai candidate dataset Milestone 0 dan pertahankan raw file tanpa perubahan. Dataset belum menjadi training source of truth sampai provenance/license, monthly base-salary semantics, feature meaning, dan cleaning decision dikonfirmasi.
- Reason: File aktual sudah tersedia dan dapat diprofilkan, tetapi memiliki `NaN` pada baris `9` dan `21`, hanya 19 complete rows, serta tidak menyertakan metadata sumber atau definisi kolom.
- Conditions: Pemilik project mengonfirmasi provenance dan hak publikasi, target `salary` sebagai monthly base salary dalam IDR, definisi/range/precision score, serta penanganan missing values. Evaluation strategy kemudian dipilih berdasarkan 19 rows atau jumlah final setelah cleaning.
- Consequence: CSV dikecualikan dari Git sementara. Training, artifact generation, dan metric claims tetap blocked; profile terverifikasi dicatat di `data_train/README.md` dan `04-machine-learning.md`.

## D-067: Owner-declared target semantics and complete-row cleaning

- Date: 2026-10-07
- Status: Superseded by D-068
- Decision: Perlakukan `salary` sebagai monthly base salary dalam IDR untuk contract project; `knowledge`, `technical`, dan `logical` masing-masing berarti skor pengetahuan, teknik, dan logika. Hapus original rows `9` dan `21` yang memiliki `NaN` dari active candidate dataset tanpa imputasi.
- Reason: Pemilik project menetapkan semantik target dan feature serta memilih complete-row deletion. Required feature values pada dua row tersebut tidak dapat digunakan langsung oleh MVP contract.
- Conditions: URL website sumber, lisensi/publication permission, dan skala serta metode pengukuran score tetap harus diverifikasi. Dataset kecil berisi 19 complete rows memerlukan evaluation strategy yang eksplisit dan limitation warning.
- Consequence: Active dataset memiliki 19 complete rows dan SHA-256 `53cf99c3212644e9fd88ddf586afdbdd0d8eb444b290a3b53a08a00b069f0755`. Private ignored pre-cleaning copy dipertahankan untuk rollback. Training dan publication tetap blocked oleh conditions yang belum terpenuhi.

## D-068: Replace active candidate with 500-row dataset

- Date: 2026-10-07
- Status: Accepted, amended by D-069 and D-070
- Decision: Gunakan hanya `data_train/salary_500.csv` sebagai candidate input untuk training berikutnya. Pertahankan `salary.csv` dan pre-cleaning archive sebagai rollback history, bukan sebagai input training.
- Reason: Pemilik project mengganti dataset sebelumnya dengan versi 500 rows. Profil aktual menemukan 500 complete rows, schema yang sama, seluruh required values numeric dan finite, serta tidak ada duplicate ID, exact feature-target duplicate, atau salary format mismatch.
- Conditions: URL website sumber, lisensi/publication permission, skala dan metode pengukuran score, serta evaluation configuration tetap harus diselesaikan sebelum release training.
- Consequence: Active candidate SHA-256 menjadi `b4679821670d29104a8b8cf9162005eca7cb728861b13bfaad3fe30ce668ca65`. Training command dan artifact metadata wajib mereferensikan file/hash ini; dataset lama tidak boleh terpilih secara implisit.

## D-069: Owner-authored dataset approved for public Git

- Date: 2026-10-07
- Status: Accepted
- Decision: Catat `data_train/salary_500.csv` sebagai dataset yang dibuat sendiri oleh pemilik project, tanpa source website atau external dataset dependency, dan izinkan file aktif tersebut masuk public Git repository. Pertahankan `salary.csv` lama dan pre-cleaning archive dalam `.gitignore`.
- Reason: Pemilik project mengoreksi provenance sebelumnya dan memberikan izin publikasi eksplisit. Dataset aktif tidak bergantung pada lisensi sumber eksternal.
- Consequence: `salary_500.csv` tidak lagi di-ignore, sedangkan rollback datasets tetap private. Publication permission dicatat terpisah di `data_train/DATASET-NOTICE.md`; MIT License source code tidak otomatis dianggap sebagai dataset reuse license.

## D-070: Synthetic data, score, cleaning, and evaluation contract

- Date: 2026-10-07
- Status: Accepted
- Decision: Nyatakan 500 rows sebagai data sintetis yang diisi acak oleh pemilik project tanpa generation script/seed. Gunakan integer 0–100 untuk ketiga score, pertahankan seluruh rows tanpa outlier removal, dan evaluasi dengan shuffled 80/20 split seed 42. Jalankan five-fold cross-validation dengan shuffle/seed 42 hanya pada 400 training rows; laporkan final R², MAE, dan RMSE pada 100 held-out test rows.
- Reason: Pemilik project menerima rekomendasi score dan evaluation serta menjelaskan proses pembuatan data. Contract deterministic diperlukan untuk reproducibility, sedangkan keterbatasan data sintetis harus terlihat jelas.
- Consequence: Observed range 40–90 untuk knowledge serta 50–90 untuk technical/logical digunakan untuk OOD warning, bukan sebagai hard validation range. Metric hanya membuktikan behavior pada dataset ini dan tidak boleh diklaim sebagai akurasi salary dunia nyata. O-004 dan O-008 selesai; Milestone 0 dapat ditutup.

## D-071: Resolve foundation runtime and dependency compatibility

- Date: 2026-10-07
- Status: Accepted
- Decision: Naikkan project PHP minimum dari `^8.3` ke `^8.4` agar sesuai dengan Pest 5.3. Gunakan Python 3.13 dengan exact pins scikit-learn 1.9.1, pandas 3.0.6, Ruff 0.16.10, serta seluruh dependency transitif pada `ml/requirements.txt`. Pertahankan Composer dan NPM dependency versions yang sudah terkunci.
- Reason: `composer.lock` memuat Pest 5.3 yang membutuhkan PHP `^8.4`; root PHP `^8.3` sebelumnya memberi compatibility claim yang tidak benar. Official package metadata menyediakan wheel Python 3.13 untuk ML pins terpilih, dan clean virtual environment install berhasil.
- Consequence: Clean setup membutuhkan PHP 8.4 dan Python 3.13. Perubahan dependency harus memperbarui exact pins serta menjalankan ulang PHP, Python, dan Vite gates. O-005 selesai.

## D-072: Create demo seed after employee schema

- Date: 2026-10-07
- Status: Accepted
- Decision: Implementasikan `php artisan app:seed-demo-data` pada Milestone 2 setelah migration dan model Employee tersedia, bukan sebagai placeholder Milestone 1.
- Reason: Command wajib membuat employee fiktif secara idempotent. Milestone 1 tidak boleh memperkenalkan business schema sebelum employee/database slice.
- Consequence: Milestone 1 dapat ditutup tanpa no-op command. Milestone 2 belum selesai sampai command membuat employee demo tanpa salary record atau metric palsu.

## D-073: Employee lifecycle and portable business schema

- Date: 2026-10-07
- Status: Accepted
- Decision: Gunakan employee code uppercase 3–32 karakter `[A-Z0-9_-]` dengan karakter awal huruf/angka, full name maksimum 150 karakter, reversible active status, server-rendered list/create/detail/edit flow, case-insensitive search, status filter, dan pagination 15 record. Tambahkan portable additive `employees` serta `salary_records` migrations dan tiga deterministic demo employees.
- Reason: Contract ini memberikan lifecycle employee yang lengkap tanpa menambah data personal, delete workflow, database-specific CHECK SQL, atau mengaktifkan salary prediction sebelum artifact tersedia.
- Consequence: Database menegakkan required/unique/foreign-key constraints dan application layer menegakkan format/cross-field rules. Tidak ada delete route; employee code tetap reserved saat nonaktif. `app:seed-demo-data` menyinkronkan dua employee aktif dan satu nonaktif tanpa salary record. Salary cross-field validation tetap Milestone 4.

## D-074: Deterministic 400-row model and committed trusted artifact

- Date: 2026-10-07
- Status: Accepted
- Decision: Fit final `LinearRegression` hanya pada 400 deterministic training rows, pertahankan 100 rows sebagai held-out evaluation, dan jalankan five-fold KFold hanya pada training rows. CLI menerima path berbeda tetapi mewajibkan SHA-256 dataset aktif. Commit satu atomic JSON artifact dengan stable model version yang tidak memasukkan training timestamp.
- Reason: Model yang dievaluasi harus sama dengan model yang dirilis, sementara strict dataset identity dan self-contained artifact menjaga reproducibility tanpa model registry atau Python runtime service.
- Consequence: Artifact aktif adalah `artifacts/salary_linear_regression.json` dengan model version `sha256:ef4edb1136f434009edc7be50ed2f8cc3dc2dbf1292756d5f33b0311b5ef05c5`. Laravel Milestone 3 hanya memvalidasi dan menampilkan metadata; inference serta PHP/Python parity tetap Milestone 4. Artifact hilang/rusak menghasilkan safe HTTP 200 unavailable state tanpa metric atau exception detail.

## D-075: Native BCMath prediction-to-money boundary

- Date: 2026-10-08
- Status: Accepted
- Decision: Gunakan PHP float hanya untuk raw Linear Regression agar cocok dengan scikit-learn. Setelah finite/positive check, ubah output melalui locale-independent decimal string dan gunakan native BCMath PHP 8.4 dengan `HalfAwayFromZero` untuk seluruh authoritative money/hour calculation. Overtime hours/rate harus keduanya nol atau keduanya positif. Work days tidak dibatasi panjang calendar period.
- Reason: PHP 8.4 project sudah menyediakan BCMath sehingga fixed precision tidak membutuhkan package baru. Batas numeric yang eksplisit menjaga prediction parity dan salary determinism.
- Consequence: `ext-bcmath` menjadi Composer/CI runtime requirement. `model_version` diperbesar additive menjadi 71 karakter; rollback ke 64 menolak data panjang tanpa truncation. Success memakai Post/Redirect/Get dan JavaScript hanya mencegah double-click. O-007 selesai.

## D-076: Canonical database-backed reports and direct XLSX export

- Date: 2026-10-08
- Status: Accepted
- Decision: Prediction History membaca immutable salary records newest-first dengan employee/month filters dan pagination 15. Monthly Report memakai satu canonical `MonthlyReportService` record set untuk HTML, streamed UTF-8 CSV, PhpSpreadsheet `5.10.0` XLSX, dan print view. Summary dihitung dengan BCMath; CSV/XLSX menetralkan formula-like text; XLSX money/hour/day cells tetap numeric.
- Reason: Satu query/row contract mencegah drift antarformat sekaligus memenuhi reporting MVP tanpa queue, report database, Laravel Excel abstraction, atau server-side PDF engine.
- Consequence: PHP runtime/CI menyediakan extensions yang diwajibkan PhpSpreadsheet. Report menampilkan seluruh matching snapshots dan selected-record totals; technical ML fields hanya tersedia pada history detail. PDF dibuat melalui browser Print/Save as PDF.

## D-077: Laravel Cloud portfolio deployment target

- Date: 2026-10-08
- Status: Accepted
- Decision: Targetkan Laravel Cloud Starter region Singapore dengan smallest hibernating application compute dan Serverless PostgreSQL. Build frontend memakai `npm ci && npm run build`; deploy menjalankan migration, idempotent demo seed, lalu optimize. Production tidak menginstall Python.
- Reason: Managed Laravel runtime dan database menjaga deployment portfolio sederhana, sedangkan auto-hibernation sesuai traffic demo yang jarang.
- Consequence: `docs/08-deployment.md` menjadi dashboard runbook. Release dinyatakan deployment-ready, bukan live; PostgreSQL dan public URL smoke tetap pending sampai account aktif. O-006 selesai.

## D-078: Read-only employee boundary for public demo

- Date: 2026-10-08
- Status: Accepted
- Decision: Tambahkan `APP_PUBLIC_DEMO`, default `false`. Saat aktif, employee list/detail tetap publik, seluruh employee mutation disembunyikan dan ditolak 403, sedangkan prediction untuk demo employees tetap aktif. Batasi prediction 10/min/IP dan setiap export/print 20/min/IP.
- Reason: Reviewer tetap dapat mencoba core ML flow tanpa mengizinkan perubahan demo identities atau memperluas scope menjadi authentication.
- Consequence: Public data wajib fiktif dan banner menjelaskan visibilitas data. Penggunaan data nyata memerlukan authentication, authorization, privacy, serta retention scope baru.

## D-079: Portfolio release accessibility, security, and licensing baseline

- Date: 2026-10-08
- Status: Accepted
- Decision: Targetkan WCAG 2.2 AA pada core flow dengan semantic markup dan keyboard-safe drawer tanpa dependency baru; tambahkan defensive response headers; override `shell-quote` ke 1.12.0 dan audit Composer/NPM di CI; lisensikan source dengan MIT dan dataset terpisah dengan CC BY 4.0.
- Reason: Public portfolio harus dapat ditinjau, aman secara default, serta memiliki reuse terms yang tidak ambigu tanpa menambah infrastructure atau framework.
- Consequence: Enam screenshot non-sensitif menjadi bagian README. Accessibility mencakup automated markup regression dan manual keyboard/responsive review; bukan klaim external conformance certification.

## D-080: Operational visual language and explicit workflow

- Date: 2026-10-08
- Status: Accepted
- Decision: Gunakan visual system yang menyerupai aplikasi operasional internal: system font, neutral palette, flat bordered panels, modest radius, restrained accent, serta sidebar putih. Tampilkan alur empat tahap dan pemisahan dataset/database langsung pada halaman prediction.
- Reason: Portfolio harus terasa dirancang untuk workflow Compensa, bukan seperti generic AI-generated dashboard; user juga harus memahami konsekuensi setiap input sebelum submit.
- Consequence: Decorative typography, excessive rounded cards, shadow, serta AI-themed styling dikurangi. Screenshot release diambil ulang setelah perubahan dan core behavior tetap sama.

## D-081: Reporting month is independent from the work-period boundary

- Date: 2026-10-08
- Status: Accepted
- Decision: Perlakukan `reporting_month` sebagai bucket laporan/payroll yang dipilih operator. Izinkan `period_start` dan `period_end` melintasi bulan atau tahun selama end tidak mendahului start.
- Reason: Siklus penggajian organisasi dapat berjalan dari tanggal tertentu pada bulan sebelumnya sampai tanggal yang sama pada bulan laporan, misalnya 20 September–20 Oktober.
- Consequence: Same-month validator dihapus tanpa perubahan schema atau formula. Satu salary record tetap masuk tepat ke reporting month yang dipilih dan tidak dialokasikan otomatis ke beberapa bulan. D-009 digantikan.

## D-082: Reduce Compensa to pure salary prediction

- Date: 2026-10-09
- Status: Accepted; supersedes salary-calculation and reporting portions of D-004, D-005, D-006, D-007, D-009, D-016, D-019, D-075, D-076, and D-081, plus the stale screenshot set from D-079
- Decision: Compensa hanya menerima employee serta empat model features, menghasilkan predicted monthly base salary, dan menyimpan prediction history. Hapus periode kerja, prorata, jam kerja, lembur, estimated total salary, monthly report, dan exports dari aplikasi aktif.
- Reason: Pemilik project menetapkan Compensa sebagai web prediksi, bukan salary calculation atau payroll application.
- Consequence: Prediction writer hanya mengisi active prediction fields. Existing calculation columns tetap nullable sebagai compatibility layer agar data lama tidak dihapus. Rollback ditolak bila pure prediction rows membuat legacy required schema tidak valid. PhpSpreadsheet, seluruh report surface, dan screenshot flow lama dihapus.

## Open decisions and blockers

Tidak ada open decision yang menghalangi portfolio release lokal. Deployment account, live URL, dan PostgreSQL smoke adalah pekerjaan operasional pending, bukan keputusan architecture yang terbuka.
