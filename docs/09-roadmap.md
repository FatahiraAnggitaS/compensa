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

Status: **In progress**. Responsive Blade scaffold, shared navigation, lima GET routes/controllers, asset pipeline, no-data states, dan page-level smoke tests tersedia. Error handling, health checks, ML environment, demo seed command, serta CI belum selesai.

Deliverables:

- Existing Laravel skeleton, Composer lockfile, NPM lockfile, Herd local serving, dan Vite build terverifikasi.
- Pinned `ml/requirements.txt` untuk offline Python training.
- Environment example dan Git ignore rules.
- Blade base layout, navigation, error handling, dan Laravel health checks.
- Idempotent Artisan `app:seed-demo-data` command untuk employee fiktif.
- GitHub Actions untuk Pint, Pest, Python unittest/Ruff, dan Vite build setelah repository remote tersedia.

Stop condition: project berjalan dari clean local setup.

## Milestone 2: Employee and database slice

Deliverables:

- Employee dan salary record migrations.
- Employee list/create/edit/deactivate.
- Model constraints dan focused tests.

Stop condition: employee lifecycle lulus automated tests tanpa salary prediction.

## Milestone 3: Reproducible ML slice

Deliverables:

- Dataset validator.
- `python ml/train.py --dataset <path>` training/evaluation entry point.
- Self-contained `LinearRegression` JSON artifact.
- Python ML/data tests dan generated parity reference cases.
- Model Information page dari JSON metadata aktual.

Stop condition: clean training run menghasilkan artifact, actual metrics, dan finite prediction. Jangan lanjut jika dataset tidak terbukti memakai monthly base salary dalam IDR.

## Milestone 4: Prediction and salary calculation slice

Deliverables:

- Laravel JSON inference service.
- Salary calculator dengan decimal arithmetic.
- Laravel Form Request-validated Blade prediction form.
- Atomic salary record save.
- Result, error, warning, dan disclaimer states.
- Python/PHP prediction parity tests.

Stop condition: valid flow tersimpan; invalid flow tidak menyimpan partial record; tidak ada retraining saat request.

## Milestone 5: History and reporting slice

Deliverables:

- History list/detail dan filters.
- Monthly report.
- CSV dan XLSX exports.
- Print stylesheet/PDF workflow.
- Report security and consistency tests.

Stop condition: seluruh format memakai database record set yang sama.

## Milestone 6: Portfolio release

Deliverables:

- Responsive/accessibility review.
- Security and ML guardrail review.
- Laravel managed-PaaS configuration dan smoke test.
- Root README dengan setup, training, evaluation, screenshots, limitations, dan demo guidance.
- MIT License untuk source code serta dataset license/provenance note yang terpisah.
- Final diff, secret, artifact, dataset license, dan generated-file review.

Stop condition: reviewer dapat memahami dan menjalankan alur tanpa undocumented manual step.

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
