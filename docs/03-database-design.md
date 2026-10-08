# Database Design

## 1. Data ownership

Application database menyimpan employee dan salary calculation history. Dataset training tetap file/data source terpisah. Prediction user tidak menjadi label atau training row.

## 2. ERD

```text
employees 1 ----- many salary_records
```

MVP hanya membutuhkan dua business tables. Laravel framework/default skeleton tables berada di luar domain model dan direview terpisah sebelum penghapusan apa pun.

## 3. Table: `employees`

| Column | Type concept | Constraint/purpose |
| --- | --- | --- |
| `id` | big integer | Primary key |
| `employee_code` | string(32) | Required, 3–32 karakter `[A-Z0-9_-]`, diawali huruf/angka, trimmed, normalized uppercase, unique, stable identifier |
| `full_name` | string(150) | Required, trimmed display name |
| `is_active` | boolean | Default true; nonaktifkan tanpa menghapus history |
| `created_at` | datetime | Auto-created |
| `updated_at` | datetime | Auto-updated |

Email, address, phone, date of birth, gender, tax identifier, bank account, dan sensitive attributes tidak disimpan.

Employee yang memiliki salary records tidak boleh dihapus melalui normal UI. Gunakan deactivation.
Deactivation bersifat reversible melalui explicit status action. Kode milik employee nonaktif tetap reserved dan tidak dapat digunakan employee lain.

## 4. Table: `salary_records`

| Column | Type concept | Constraint/purpose |
| --- | --- | --- |
| `id` | big integer | Primary key |
| `employee_id` | foreign key | Required; protect employee deletion |
| `reporting_month` | date | Hari pertama bulan laporan |
| `period_start` | date | Required |
| `period_end` | date | Required; tidak sebelum start |
| `knowledge_score` | small integer | Model input snapshot; integer 0–100 |
| `technical_score` | small integer | Model input snapshot; integer 0–100 |
| `logical_score` | small integer | Model input snapshot; integer 0–100 |
| `years_of_experience` | decimal(5,2) | Model input snapshot; non-negative; sampai 2 decimal places |
| `predicted_base_salary` | decimal money | Model result yang sudah mengikuti rounding rule |
| `applicable_work_days` | positive integer | Denominator prorata |
| `worked_days` | non-negative integer | Tidak lebih besar dari applicable days |
| `normal_work_hours` | decimal(8,2) hours | Stored calculation snapshot |
| `calculated_base_salary` | decimal money | Base salary setelah prorata |
| `overtime_hours` | decimal(8,2) hours | Non-negative; sampai 2 decimal places |
| `overtime_rate` | decimal money/hour | Direct user input; non-negative |
| `overtime_pay` | decimal money | Stored calculation snapshot |
| `estimated_total_salary` | decimal money | Final estimate |
| `currency_code` | three-character string | Bernilai `IDR` untuk MVP |
| `model_version` | string(71) | Identifier lengkap `sha256:` ditambah 64 lowercase hex |
| `has_ood_input` | boolean | True jika minimal satu feature di luar observed training range |
| `created_at` | datetime | Waktu pencatatan |

Seluruh monetary fields menggunakan `Decimal(18,2)`. Aplikasi memakai `ROUND_HALF_UP` saat menyimpan hasil monetary calculation.

## 5. Constraints

Migrations memakai portable hybrid constraints agar schema yang sama berjalan pada SQLite dan PostgreSQL. Database menegakkan required columns, type/precision, unique index, foreign key, serta restricted deletion. Form Request dan application service menegakkan format serta aturan cross-field; raw SQL CHECK yang bercabang per database tidak digunakan.

- `period_start <= period_end`.
- `applicable_work_days > 0`.
- `0 <= worked_days <= applicable_work_days`.
- `normal_work_hours >= 0`.
- `knowledge_score`, `technical_score`, dan `logical_score` masing-masing integer 0–100.
- `years_of_experience >= 0`.
- `overtime_hours >= 0` dan `overtime_rate >= 0`.
- Semua monetary result harus finite dan mengikuti currency contract.
- `model_version` wajib ada untuk record hasil inference.
- `has_ood_input` dihitung server dari model metadata, bukan dari client.

Milestone 4 mengaktifkan satu salary-record writer melalui validated prediction flow. Writer mengunci ulang employee aktif, menjalankan prediction/calculation, dan membuat satu immutable snapshot dalam database transaction.

Migration additive memperbesar `model_version` dari 64 menjadi 71 karakter. Rollback ke 64 ditolak bila nilai lebih panjang sudah tersimpan agar identifier tidak dipotong.

Observed model ranges berasal dari artifact: knowledge 40–90 serta technical dan
logical 50–90 untuk active dataset. Score di luar observed range tetapi masih
dalam 0–100 valid dan disimpan dengan `has_ood_input=true`.

## 6. Indexes

- Unique index pada normalized `employees.employee_code`; `EMP-001` dan `emp-001` tidak dapat menjadi dua employee.
- Index pada `salary_records.employee_id` melalui foreign key.
- Index pada `salary_records.reporting_month`.
- Composite index pada `(employee_id, reporting_month)` untuk history filter.
- Index pada `created_at` hanya jika query nyata memerlukannya.

## 7. History semantics

Salary record diperlakukan sebagai immutable calculation snapshot pada UI. Correction membuat record baru agar hasil lama tetap dapat ditelusuri. Karena itu, satu employee dapat memiliki beberapa record pada bulan yang sama. Monthly report menampilkan seluruh record yang cocok dan tidak diam-diam memilih salah satunya.

Milestone 5 mengaktifkan history newest-first dengan pagination 15 record, filter employee/bulan, serta monthly report yang membaca seluruh matching records tanpa membuat atau mengubah data. Summary memakai penjumlahan BCMath atas nilai snapshot tersimpan.

Jika kebutuhan berubah menjadi satu payable record per employee/month, status/finalization harus dirancang sebagai perubahan scope tersendiri.

## 8. Transactions

Prediction, calculation, dan insert salary record terjadi dalam satu application operation. Hanya record dengan seluruh input dan output valid yang disimpan.
