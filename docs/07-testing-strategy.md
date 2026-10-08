# Testing Strategy

## 1. Principle

Test memverifikasi behavior dan contract penting. Test tidak dibuat hanya untuk mengejar coverage percentage.

## 2. ML and data tests

- Required columns tersedia setelah mapping.
- Feature matrix hanya berisi empat canonical features.
- Target tidak masuk feature matrix.
- Numeric parsing menangani invalid, missing, NaN, dan infinity.
- Missing feature/target memblokir training sampai cleaning decision tersedia.
- Exact duplicate rows memblokir training sampai review decision tersedia.
- Cleaning row counts tercatat.
- Model dapat di-fit pada valid fixture.
- Prediction menghasilkan satu finite numeric value.
- Feature order/name mismatch ditolak.
- Score non-integer atau di luar 0–100 ditolak.
- Score dalam 0–100 tetapi di luar observed range menghasilkan OOD warning.
- JSON artifact schema, coefficients, intercept, metadata, dan model version sinkron.
- Metric calculation memakai evaluation data.
- Deterministic 80/20 split memakai seed 42 dan menghasilkan 400/100 rows.
- Five-fold cross-validation tidak menyentuh held-out test rows.
- Training tidak membaca application salary records.
- Python reference predictions menghitung exported equation dalam absolute tolerance `0.01` IDR. Laravel formula cross-language parity diaktifkan bersama inference pada Milestone 4.

Synthetic fixture kecil boleh dipakai untuk test behavior. Fixture tidak boleh diklaim sebagai dataset atau metric production model.

## 3. Salary calculator unit tests

- Full-period calculation.
- Partial-period proration.
- Zero worked days.
- Zero overtime.
- Fractional overtime hours.
- Invalid zero applicable days.
- Worked days melebihi applicable days.
- Negative hours/rate ditolak.
- Decimal rounding boundary.
- Model output NaN/infinity/negative handling.

Floating-point exact equality tidak digunakan untuk model result. Money calculator memakai exact decimal assertions.

## 4. Database and service tests

- Business migrations dapat diterapkan pada empty SQLite database dan dua migration domain dapat di-rollback tanpa menghapus default Laravel tables.
- Employee code uniqueness.
- Employee code di-trim, dinormalisasi uppercase, dan unique tanpa membedakan input capitalization.
- Employee deletion protection.
- Salary record stores all input/output snapshots.
- Transaction rollback saat save gagal.
- Model version wajib tersimpan.
- OOD prediction menyimpan `has_ood_input=true`.
- History filter berdasarkan employee dan month.
- Multiple records pada employee/month tetap terlihat.
- Demo seed dapat dijalankan berulang dan tetap menghasilkan tiga employee fiktif tanpa salary record.

## 5. Laravel web integration tests

- Gunakan Pest dengan Laravel plugin yang sudah tersedia.
- Employee list/create/edit/deactivate.
- Salary form rendering dan CSRF protection.
- Valid submission membuat satu record.
- Missing/non-numeric/malformed input tidak membuat record.
- Inactive employee ditolak.
- Missing/corrupt/mismatched model artifact menghasilkan safe error.
- Extrapolation warning tampil.
- OOD submission tetap dapat disimpan dengan flag.
- Negative prediction menampilkan limitation serta tidak membuat salary record.
- Duplicate submit prevention behavior.
- Model Information memakai metadata aktual.

## 6. Report tests

- Monthly query tidak mencampur month lain.
- Summary totals sama dengan penjumlahan seluruh selected detail records.
- HTML, CSV, dan XLSX memakai record set konsisten.
- CSV header dan encoding stabil.
- CSV/XLSX text formula injection dinetralkan.
- Numeric cells tetap numeric pada XLSX.
- Print view memiliki required columns dan disclaimer.
- Monthly report tidak memuat model inputs, model version, atau OOD status.

## 7. Manual checks

- Keyboard-only flow.
- Mobile viewport.
- Focus visibility dan readable errors.
- Browser Print/Save as PDF layout.
- Local setup dari clean environment melalui Laravel Herd.
- Deployment smoke test memakai demo data.
- Mobile drawer: focus masuk saat dibuka, Tab/Shift+Tab terperangkap, Escape menutup, dan focus kembali ke tombol pembuka.
- Core flow pada viewport desktop dan mobile; report table tetap dapat di-scroll horizontal.
- Heading hierarchy, caption, table header scope, validation association, warning/flash semantics, focus visibility, dan contrast direview terhadap target WCAG 2.2 AA.

## 8. Quality gates

Command final ditetapkan setelah implementation tersedia. Minimum gate sebelum merge/release:

1. Pest/Laravel tests lulus.
2. Laravel Pint check lulus.
3. Python `unittest`, Ruff lint, dan Ruff format checks lulus untuk `ml/`.
4. Vite production build lulus.
5. Laravel migrations konsisten dan dapat diterapkan pada empty database.
6. Training/evaluation command lulus pada verified dataset bila ML code berubah.
7. JSON artifact schema dan Python/PHP parity checks lulus.
8. Artifact dan Model Information tetap sinkron.
9. Diff tidak membawa secret, local database, cache, Python virtual environment, atau unapproved dataset.
10. `composer audit --locked --no-interaction` dan `npm audit --audit-level=high` tidak menemukan vulnerability yang memenuhi threshold.
11. Public demo mutation guards, route throttling, response headers, serta accessibility markup regression tests lulus.

Command foundation yang sudah aktif:

```text
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

Training dan artifact gates aktif mulai Milestone 3. Inference parity serta salary calculation/persistence gates aktif mulai Milestone 4. Report gates mulai aktif pada Milestone 5.

Migration gate aktif mulai Milestone 2. Focused proof mencakup schema, rollback, normalization, unique constraint, foreign-key deletion protection, model casts/relations, employee web lifecycle, search/filter/pagination, serta idempotent demo seed.

Milestone 3 proof mencakup strict hash/schema/data validation, deterministic
400/100 split, training-only five-fold isolation, finite model/metrics, stable
model version, parity equation, atomic-write preservation, Laravel artifact
validation, metadata rendering, safe unavailable states, dan metric non-hardcoding.

Milestone 4 proof mencakup tiga Python/PHP parity cases, OOD detection, finite dan negative prediction handling, exact BCMath proration/overtime/rounding, request cross-field validation, active-employee locking, Post/Redirect/Get, immutable snapshot persistence, transaction rollback, 71-character model version, serta guarded schema rollback.

Milestone 5 proof mencakup history ordering/filter/pagination/detail, multi-record monthly totals dengan BCMath, month dan employee isolation, konsistensi canonical record set pada HTML/CSV/XLSX/print, stable UTF-8 CSV headers, formula-injection neutralization, numeric XLSX cells, required print metadata/disclaimer, serta invalid export filter rejection.

Milestone 6 proof mencakup public-demo read/write boundaries, prediction yang tetap aktif, 10/min prediction throttle, 20/min export throttle, global security headers, mobile navigation accessibility contract, field/error associations, Composer/NPM audits, production cache build, local production-mode smoke, dan review enam screenshot. PostgreSQL/live URL smoke hanya dapat dinyatakan lulus setelah deployment aktual.
