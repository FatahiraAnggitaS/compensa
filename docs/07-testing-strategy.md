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
- Python reference predictions dan Laravel formula lulus cross-language parity tolerance.

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

- Employee code uniqueness.
- Employee code di-trim, dinormalisasi uppercase, dan unique tanpa membedakan input capitalization.
- Employee deletion protection.
- Salary record stores all input/output snapshots.
- Transaction rollback saat save gagal.
- Model version wajib tersimpan.
- OOD prediction menyimpan `has_ood_input=true`.
- History filter berdasarkan employee dan month.
- Multiple records pada employee/month tetap terlihat.

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
