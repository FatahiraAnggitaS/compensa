# Training Dataset

## Status

`salary_500.csv` adalah active candidate training dataset untuk Milestone 0,
menggantikan `salary.csv`. Dataset dibuat sendiri oleh pemilik project dan telah
diizinkan untuk dipublikasikan dalam public Git repository Compensa.

Profil berikut diverifikasi dari file aktual pada 2026-10-07 tanpa mengubah
isinya.

## File identity

- Path: `data_train/salary_500.csv`
- SHA-256: `b4679821670d29104a8b8cf9162005eca7cb728861b13bfaad3fe30ce668ca65`
- Encoding: UTF-8 dengan BOM
- Delimiter: semicolon (`;`)
- Data rows: 500
- Complete rows untuk feature dan target: 500

## Verified column mapping and validation contract

Pemilik project menetapkan `salary` sebagai gaji pokok satu bulan dalam IDR dan
menjelaskan tiga score sebagai skor pengetahuan, teknik, dan logika. Ketiga score
memakai integer berskala valid 0–100.

| Raw column | Candidate canonical field | Observed profile |
| --- | --- | --- |
| `no` | Row identifier, bukan feature | 500 unique IDs; no missing value |
| `knowledge` | `knowledge_score` | Integer 0–100; observed 40–90; 50 unique values |
| `technical` | `technical_score` | Integer 0–100; observed 50–90; 41 unique values |
| `logical` | `logical_score` | Integer 0–100; observed 50–90; 41 unique values |
| `year_experience` | `years_of_experience` | Numeric 0–3.7; maksimal 1 decimal place; 38 unique values |
| `salary` | `monthly_base_salary` target | Monthly IDR; `Rp`-formatted 2,500,000.00–10,000,000.00; 134 unique values |

Tidak ditemukan missing value, invalid/non-finite numeric value, duplicate ID,
exact duplicate untuk gabungan empat features dan target, atau salary format
yang menyimpang pada active file.

Input score di luar 0–100 ditolak. Input valid yang berada di luar observed
training range tetap dapat diprediksi tetapi wajib diberi OOD warning.

## Dataset migration and rollback

- `salary_500.csv` adalah satu-satunya candidate input untuk training berikutnya.
- `salary.csv` adalah previous 19-row candidate dan tidak lagi boleh dipakai
  sebagai training input.
- `data_train/archive/salary.pre-cleaning.csv` mempertahankan versi 21-row lama
  untuk audit/rollback.
- `salary_500.csv` tidak lagi dikecualikan dari Git dan merupakan satu-satunya
  dataset yang disetujui untuk public repository.
- `salary.csv` lama dan seluruh folder archive tetap dikecualikan dari Git.

## Provenance and publication

- Creator/source: dibuat sendiri oleh pemilik project Compensa; bukan berasal
  dari website atau dataset eksternal.
- Generation method: 500 rows diisi secara acak oleh pemilik project tanpa
  generation script atau recorded random seed. File committed menjadi source of
  truth yang dapat direproduksi byte-for-byte melalui hash, tetapi proses
  generasinya tidak dapat direproduksi.
- External license dependency: tidak ada.
- Publication permission: disetujui pemilik project untuk public Git repository
  pada 2026-10-07.
- Personal/sensitive data: hasil inspeksi hanya menemukan row identifier,
  score, pengalaman, dan salary; tidak ada direct personal identifier.
- Dataset reuse terms: lihat `data_train/DATASET-NOTICE.md`; source-code MIT
  License tidak otomatis mencakup dataset.

## Cleaning and outlier decision

- Pertahankan seluruh 500 rows.
- Jangan melakukan outlier removal atau imputation karena seluruh rows lengkap,
  finite, berada dalam contract 0–100/non-negative, dan tidak ada data source
  eksternal untuk membenarkan perubahan nilai.

## Evaluation contract

- Shuffled train/test split: 80/20.
- Training rows: 400; held-out test rows: 100.
- Split random seed: 42.
- Five-fold cross-validation dijalankan hanya pada 400 training rows memakai
  shuffle dan seed 42 untuk melihat kestabilan.
- R², MAE, dan RMSE final dilaporkan dari held-out test set.
- Test set tidak dipakai untuk memilih ulang keputusan model.

Karena dataset sintetis dan diisi acak, metric hanya menggambarkan fit pada file
ini dan tidak boleh diklaim sebagai akurasi gaji dunia nyata.
