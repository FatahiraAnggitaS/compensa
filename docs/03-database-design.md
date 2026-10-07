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
| `employee_code` | short string | Required, trimmed, normalized uppercase, unique, stable identifier |
| `full_name` | string | Required display name |
| `is_active` | boolean | Default true; nonaktifkan tanpa menghapus history |
| `created_at` | datetime | Auto-created |
| `updated_at` | datetime | Auto-updated |

Email, address, phone, date of birth, gender, tax identifier, bank account, dan sensitive attributes tidak disimpan.

Employee yang memiliki salary records tidak boleh dihapus melalui normal UI. Gunakan deactivation.

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
| `years_of_experience` | decimal | Model input snapshot; non-negative; sampai 2 decimal places |
| `predicted_base_salary` | decimal money | Model result yang sudah mengikuti rounding rule |
| `applicable_work_days` | positive integer | Denominator prorata |
| `worked_days` | non-negative integer | Tidak lebih besar dari applicable days |
| `normal_work_hours` | decimal hours | Stored calculation snapshot |
| `calculated_base_salary` | decimal money | Base salary setelah prorata |
| `overtime_hours` | decimal hours | Non-negative; sampai 2 decimal places |
| `overtime_rate` | decimal money/hour | Direct user input; non-negative |
| `overtime_pay` | decimal money | Stored calculation snapshot |
| `estimated_total_salary` | decimal money | Final estimate |
| `currency_code` | three-character string | Bernilai `IDR` untuk MVP |
| `model_version` | string | Artifact/metadata identifier |
| `has_ood_input` | boolean | True jika minimal satu feature di luar observed training range |
| `created_at` | datetime | Waktu pencatatan |

Seluruh monetary fields menggunakan `Decimal(18,2)`. Aplikasi memakai `ROUND_HALF_UP` saat menyimpan hasil monetary calculation.

## 5. Constraints

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

Jika kebutuhan berubah menjadi satu payable record per employee/month, status/finalization harus dirancang sebagai perubahan scope tersendiri.

## 8. Transactions

Prediction, calculation, dan insert salary record terjadi dalam satu application operation. Hanya record dengan seluruh input dan output valid yang disimpan.
