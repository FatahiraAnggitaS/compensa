# Machine Learning

## 1. Verified contract from product requirement

- Problem type: supervised regression.
- Algorithm: scikit-learn `LinearRegression`.
- Canonical features:
  - `knowledge_score`
  - `technical_score`
  - `logical_score`
  - `years_of_experience`
- Model output: predicted base salary.
- Target contract: monthly base salary dalam IDR.
- Overtime tidak termasuk feature.

## 2. Candidate dataset profile

Active candidate dataset telah tersedia di `data_train/salary_500.csv` dan
diprofilkan pada 2026-10-07 tanpa mengubah isinya. File ini menggantikan
`data_train/salary.csv` sebagai satu-satunya candidate training input. Pemilik
project menetapkan target sebagai gaji pokok satu bulan dalam IDR serta tiga
score sebagai skor pengetahuan, teknik, dan logika.

Dataset dibuat sendiri oleh pemilik project, bukan berasal dari website atau
dataset eksternal, dan disetujui untuk dipublikasikan dalam public Git repository
Compensa. Publication statement tercatat di `data_train/DATASET-NOTICE.md`.
Sebanyak 500 rows diisi secara acak oleh pemilik project tanpa generation script
atau recorded seed. Dataset bersifat sintetis dan tidak mewakili distribusi gaji
dunia nyata.

- Active candidate SHA-256: `b4679821670d29104a8b8cf9162005eca7cb728861b13bfaad3fe30ce668ca65`.
- Format: UTF-8 dengan BOM, semicolon-delimited CSV.
- Shape: 500 complete data rows dan 6 raw columns.
- Raw columns: `no`, `knowledge`, `technical`, `logical`, `year_experience`, dan
  `salary`.
- Candidate mapping: `knowledge` -> `knowledge_score`, `technical` ->
  `technical_score`, `logical` -> `logical_score`, `year_experience` ->
  `years_of_experience`, dan `salary` -> target.
- `no` hanya row identifier dan tidak boleh menjadi model feature.
- Observed `knowledge`: integer 40–90 tanpa missing value.
- Observed `technical`: integer 50–90 tanpa missing value.
- Observed `logical`: integer 50–90 tanpa missing value.
- Observed `year_experience`: numeric 0–3.7 dengan maksimal 1 decimal place dan
  tanpa missing value.
- Observed `salary`: string berformat `Rp` dengan numeric value
  2,500,000.00–10,000,000.00 dan tanpa missing value.
- Seluruh 500 rows memiliki feature dan target numeric yang finite; tidak ada
  missing value atau cleaning yang diterapkan pada active candidate.
- Tidak ditemukan exact duplicate pada gabungan candidate features dan target.
- Tidak ditemukan duplicate row ID atau salary format mismatch.
- `salary.csv` 19-row dipertahankan sebagai rollback dan tidak digunakan untuk
  training setelah keputusan penggantian dataset ini.

Monthly base-salary semantics adalah contract yang ditetapkan pemilik project.
Ketiga score wajib integer 0–100. Observed range knowledge adalah 40–90;
technical dan logical adalah 50–90. Input di luar observed range tetapi masih
dalam 0–100 adalah valid OOD input dan wajib menghasilkan warning.

Detail profile dan outstanding confirmations tersedia di
`data_train/README.md`.

## 3. Facts generated only during training

- Coefficients, intercept, dan model version.
- Actual train/test row identities setelah deterministic split.
- R², MAE, RMSE, dan cross-validation distribution.
- Actual prediction serta parity reference values.

Item tersebut tetap belum diverifikasi sampai Milestone 3 menjalankan training.

## 4. Dataset readiness gate

MVP menerima satu file CSV UTF-8. Excel, multiple sheets, dan format lain tidak didukung training command.

Training harus berhenti dengan pesan jelas bila:

- required column hilang;
- target tidak dapat dikonversi menjadi numeric;
- target dataset tidak dapat dibuktikan sebagai monthly base salary dalam IDR;
- feature atau target memiliki missing value dan belum ada cleaning decision berbasis data profile;
- exact duplicate rows ditemukan dan belum direview sebagai duplicate sah atau data issue;
- feature atau target berisi non-finite value setelah preprocessing;
- target ikut masuk feature matrix;
- jumlah valid sample tidak cukup untuk evaluation yang bermakna;
- provenance atau hak penggunaan dataset tidak jelas untuk tujuan publikasi.

Missing value, duplicate, dan outlier tidak otomatis dibuang. Setiap keputusan cleaning harus dicatat bersama jumlah row sebelum dan sesudah.

## 5. Training flow

1. Baca dataset tanpa mengubah source file.
2. Validasi schema dan data quality.
3. Map raw dataset headers ke canonical feature contract melalui fixed mapping yang ditetapkan setelah inspeksi.
4. Parse feature dan target menjadi numeric.
5. Pisahkan feature matrix dan target.
6. Buat evaluation split sebelum preprocessing yang membutuhkan learned statistics.
7. Fit preprocessing hanya pada training data bila preprocessing diperlukan.
8. Fit `LinearRegression` pada training data.
9. Hitung R2, MAE, dan RMSE pada held-out data bila evaluation valid.
10. Simpan artifact dan metadata secara atomik.

Evaluation memakai shuffled 80/20 split dengan random seed 42: 400 training rows
dan 100 held-out test rows. Five-fold cross-validation dengan shuffle dan seed 42
dijalankan hanya pada training rows untuk melihat kestabilan. Final R², MAE, dan
RMSE dihitung pada held-out test set. Nilai tersebut wajib tercatat dalam
artifact dan tidak boleh digunakan untuk mengklaim performa dunia nyata.

## 6. Training/inference parity

- Inference menerima empat canonical features dengan nama dan urutan yang sama.
- Numeric casting, feature mapping, dan validation contract harus sama antara Python training dan Laravel inference.
- MVP tidak memakai learned preprocessing. Missing values memblokir training; numeric scaling tidak diperlukan untuk Linear Regression contract ini.
- Jika learned preprocessing ternyata diperlukan, training berhenti dan architecture harus direview karena arbitrary scikit-learn pipeline tidak dieksekusi oleh Laravel.
- Jangan memperbaiki mismatch melalui silent reorder atau default value tersembunyi.
- Python export memakai ordered canonical feature list; Laravel menghitung berdasarkan names dan menolak missing/extra feature.
- Cross-language parity tests membandingkan Laravel formula dengan Python reference predictions memakai tolerance yang terdokumentasi.

## 7. Artifact strategy

Artifact outputs:

```text
artifacts/salary_linear_regression.json
```

Artifact minimal:

- artifact schema version;
- model version;
- algorithm identifier;
- ordered canonical features;
- coefficient per feature dan intercept;
- creation timestamp;
- trusted dataset hash dan source reference;
- canonical feature names dan actual source columns;
- target column, currency, unit, dan cadence;
- row counts sebelum/sesudah cleaning;
- cleaning decisions;
- split/evaluation configuration;
- scikit-learn version;
- R2, MAE, dan RMSE yang benar-benar dihitung;
- observed training ranges untuk empat features;
- safe parity reference cases.

`model_version` berasal dari hash canonical model payload. Web app hanya membaca strict JSON artifact dari fixed trusted project path. User tidak dapat meng-upload atau memilih artifact path.

Satu self-contained file menghindari mismatch antara model dan metadata. Trainer menulis temporary JSON, memvalidasinya, lalu mengganti fixed active filename secara atomik.

## 8. Inference behavior

- Laravel membaca artifact saat prediction dan dapat mempertahankannya selama request yang sama.
- Setiap load memverifikasi schema version, algorithm, feature names, target contract, model version, dan finite numeric values.
- Prediction harus satu finite numeric value.
- Input di luar observed training range tetap dapat dihitung oleh Linear Regression. UI wajib memberi extrapolation warning dan saved record menyimpan OOD flag.
- Sistem tidak clamp prediction ke angka arbitrary.
- Negative atau nonsensical prediction ditampilkan sebagai model limitation dan tidak disamarkan menjadi salary realistis.
- Negative prediction memblokir salary calculation dan record save. User dapat memperbaiki input; model/data perlu dievaluasi bila input valid tetap menghasilkan nilai tersebut.

## 9. Evaluation and claims

- `LinearRegression.score()` disebut R2, bukan accuracy.
- MAE dan RMSE memakai unit target salary.
- Cross-validation hanya memakai training rows dan melaporkan distribution atau
  mean/standard deviation secara jujur.
- Test set tidak digunakan untuk memilih keputusan model berulang kali lalu tetap disebut unbiased.
- Model Information page membaca metadata artifact, bukan hardcoded metric.
- Dataset sintetis yang diisi acak membuat seluruh metric terbatas pada dataset
  ini; metric bukan bukti salary benchmark atau generalisasi dunia nyata.

## 10. Reproducibility

Active dataset training command adalah
`python ml/train.py --dataset data_train/salary_500.csv`. Python dependencies
dikunci dalam `ml/requirements.txt` untuk Python 3.13. Environment foundation
memakai scikit-learn 1.9.1, pandas 3.0.6, Ruff 0.16.10, serta exact transitive
pins yang telah dipasang pada clean virtual environment. Seed 42 sudah menjadi
evaluation contract; output details dan artifact checks diselesaikan pada
Milestone 3. Model artifact berubah hanya melalui command tersebut.
