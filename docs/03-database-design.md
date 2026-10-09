# Database Design

## 1. Ownership

Application database menyimpan employee dan prediction history. Dataset training tetap file terpisah. Prediction user bukan ground-truth label dan tidak menjadi training row.

## 2. ERD

```text
employees 1 ----- many salary_records
```

Nama tabel `salary_records` dipertahankan untuk kompatibilitas migration dan data lama. Scope aktif memperlakukannya sebagai prediction records.

## 3. Employees

| Column | Contract |
|---|---|
| `id` | Primary key |
| `employee_code` | String 3–32, uppercase, unique |
| `full_name` | String maksimal 150 |
| `is_active` | Boolean, default true |
| timestamps | Created dan updated time |

Employee yang memiliki prediction records tidak dapat dihapus. Gunakan reversible active status.

## 4. Active prediction fields

| Column | Contract |
|---|---|
| `id` | Primary key |
| `employee_id` | Required FK, restrict delete |
| `knowledge_score` | Integer 0–100 |
| `technical_score` | Integer 0–100 |
| `logical_score` | Integer 0–100 |
| `years_of_experience` | Decimal(5,2), non-negative |
| `predicted_base_salary` | Decimal(18,2), positive |
| `currency_code` | `IDR` |
| `model_version` | `sha256:` + 64 lowercase hex |
| `has_ood_input` | Server-computed boolean |
| `created_at` | Immutable record time |

Tidak ada `updated_at`. Prediction record diperlakukan sebagai immutable snapshot.

## 5. Legacy compatibility fields

Migration lama pernah membuat field periode dan salary calculation. Migration 2026-10-09 membuat field berikut nullable:

- `reporting_month`
- `period_start`, `period_end`
- `applicable_work_days`, `worked_days`
- `normal_work_hours`
- `calculated_base_salary`
- `overtime_hours`, `overtime_rate`, `overtime_pay`
- `estimated_total_salary`

Writer aktif tidak mengisi field tersebut. Field tetap ada hanya untuk menjaga record lama tanpa destructive migration. UI, route, service, dan report tidak membacanya.

Rollback nullable migration ditolak bila pure prediction records memiliki nilai `NULL`. Guard ini mencegah rollback yang merusak atau memalsukan data.

## 6. Constraints dan indexes

- Unique index pada normalized employee code.
- Foreign key `salary_records.employee_id` memakai restricted deletion.
- Existing legacy indexes dipertahankan untuk compatibility; writer aktif tidak bergantung padanya.
- Format/range input dan OOD flag ditegakkan oleh Laravel.
- Money result harus finite, positive, dan muat dalam `Decimal(18,2)`.

## 7. Transactions

Employee lock, artifact inference, dan insert terjadi dalam satu database transaction. Failure tidak membuat partial record.
