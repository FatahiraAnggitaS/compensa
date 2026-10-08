# MVP Roadmap

Roadmap mengikuti vertical slices. Setiap milestone harus runnable sebelum milestone berikutnya.

## Milestone 0: Data and decision readiness

Status: **Complete**. Active synthetic dataset `data_train/salary_500.csv`,
provenance/publication permission, schema, monthly IDR target, integer score
contract 0–100, cleaning decision, limitations, serta deterministic evaluation
strategy sudah ditetapkan. Git planning baseline dibuat sebelum kelanjutan
implementation.

Deliverables:

- Dataset aktual tersedia.
- Provenance/license, target, currency, cadence, schema, dan feature meaning terverifikasi.
- Data profile dan cleaning decisions terdokumentasi.
- Accepted decisions di `10-decision-log.md` tetap sinkron dengan dokumen domain.

Stop condition: jangan scaffold ML contract bila dataset source of truth ambigu.

Setelah Q&A dan review dokumen selesai, inisialisasi Git repository dan simpan planning sebagai baseline sebelum implementation.

## Milestone 1: Project foundation

Status: **Complete locally**. Responsive Blade scaffold, shared navigation, lima GET routes/controllers, asset pipeline, no-data states, safe error pages, health check, pinned ML environment, dan local quality gates tersedia. CI workflow sudah disiapkan dan mulai berjalan setelah repository memiliki GitHub remote.

Deliverables:

- Existing Laravel skeleton, Composer lockfile, NPM lockfile, Herd local serving, dan Vite build terverifikasi.
- Pinned `ml/requirements.txt` untuk offline Python training.
- Environment example dan Git ignore rules.
- Blade base layout, navigation, error handling, dan Laravel health checks.
- GitHub Actions untuk Pint, Pest, Python unittest/Ruff, dan Vite build; remote execution belum diverifikasi karena repository belum memiliki remote.

Stop condition: project berjalan dari clean local setup. Foundation dependency install dan seluruh local quality gate telah diverifikasi pada 2026-10-07.

## Milestone 2: Employee and database slice

Status: **Complete locally**. Additive employee/salary-record migrations, Eloquent contracts, employee list/create/detail/edit, search/status filter/pagination, reversible deactivation, three-record idempotent demo seed, dan focused migration/web/database tests tersedia. Prediction dan salary-record writer tetap belum diaktifkan.

Deliverables:

- Employee dan salary record migrations.
- Employee list/create/edit/deactivate.
- Idempotent Artisan `app:seed-demo-data` command untuk employee fiktif setelah employee schema tersedia.
- Model constraints dan focused tests.

Stop condition: employee lifecycle lulus automated tests tanpa salary prediction.

## Milestone 3: Reproducible ML slice

Status: **Complete locally**. Strict dataset/hash validation, deterministic
400/100 training/evaluation, five-fold CV pada training rows, atomic JSON
artifact, stable model version, parity references, Laravel artifact reader,
Model Information, CI training step, dan focused tests tersedia.

Deliverables:

- Dataset validator.
- `python ml/train.py --dataset <path>` training/evaluation entry point.
- Self-contained `LinearRegression` JSON artifact.
- Python ML/data tests dan generated parity reference cases.
- Model Information page dari JSON metadata aktual.

Stop condition: clean training run menghasilkan artifact, actual metrics, dan finite prediction. Jangan lanjut jika dataset tidak terbukti memakai monthly base salary dalam IDR.

## Milestone 4: Prediction and salary calculation slice

Status: **Complete locally**. Laravel JSON inference, Python/PHP parity, OOD detection, native BCMath calculation, Form Request validation, active-employee locking, guarded model-version migration, atomic immutable save, Post/Redirect/Get result, dan failure states tersedia.

Deliverables:

- Laravel JSON inference service.
- Salary calculator dengan decimal arithmetic.
- Laravel Form Request-validated Blade prediction form.
- Atomic salary record save.
- Result, error, warning, dan disclaimer states.
- Python/PHP prediction parity tests.

Stop condition: valid flow tersimpan; invalid flow tidak menyimpan partial record; tidak ada retraining saat request.

## Milestone 5: History and reporting slice

Status: **Complete locally (2026-10-08)**.

Deliverables:

- History list/detail dan filters.
- Monthly report.
- CSV dan XLSX exports.
- Print stylesheet/PDF workflow.
- Report security and consistency tests.

Stop condition: seluruh format memakai database record set yang sama.

Completion evidence: history list/detail/filter/pagination aktif; monthly HTML, streamed UTF-8 CSV, PhpSpreadsheet XLSX, dan print view memakai canonical `MonthlyReportService` record set; exact totals serta export security/consistency tests lulus.

## Milestone 6: Portfolio release

Status: **Complete locally / Laravel Cloud deployment-ready (2026-10-08)**. Public demo guardrails, rate limits, security headers, accessibility improvements, dependency audits, screenshot set, license separation, release documentation, dan Laravel Cloud runbook tersedia. Deployment live serta PostgreSQL smoke tetap pending sampai account/environment nyata tersedia.

Deliverables:

- Responsive/accessibility review.
- Security and ML guardrail review.
- Laravel managed-PaaS configuration dan smoke test.
- Root README dengan setup, training, evaluation, screenshots, limitations, dan demo guidance.
- MIT License untuk source code serta dataset license/provenance note yang terpisah.
- Final diff, secret, artifact, dataset license, dan generated-file review.

Stop condition: reviewer dapat memahami dan menjalankan alur tanpa undocumented manual step.

Completion evidence: core flow memakai visual sistem operasional yang tenang dan menjelaskan alur empat tahap; semantic error linkage serta keyboard-safe mobile drawer tersedia; public demo mempertahankan employee read/prediction sambil menolak employee writes; `shell-quote` dipin aman melalui NPM override; CI menjalankan Composer/NPM audit; source MIT dan dataset CC BY 4.0 dipisahkan; enam screenshot non-sensitif tersedia; local release gates dan production-mode SQLite smoke lulus. Tidak ada klaim live deployment.

## Deferred future improvements

Hanya pertimbangkan setelah MVP terbukti:

- Authentication dan role-based access.
- Finalization/approval untuk satu payable record per employee/month.
- Holiday/work calendar integration.
- Server-generated PDF.
- Model monitoring dan manually approved retraining workflow.
- Fairness analysis bila use case dan dataset mendukungnya.
- Additional algorithms sebagai experiment terpisah, bukan pengganti diam-diam.

Tax, BPJS, complex allowances/deductions, attendance devices, payment, dan external HR integration tetap di luar roadmap dekat.
