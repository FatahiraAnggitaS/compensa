# Product Requirements

## 1. Tujuan

Compensa adalah aplikasi portfolio untuk menunjukkan alur lengkap:

`employee -> model input -> base salary prediction -> work period calculation -> overtime calculation -> saved record -> history -> monthly report`

Aplikasi memberi estimasi berbasis dataset dan model. Aplikasi bukan payroll production, benchmark pasar, atau alat keputusan HR.

## 2. Pengguna MVP

Satu operator menggunakan aplikasi untuk mengelola employee, membuat salary calculation, melihat history, dan membuat monthly report.

Authentication, role, approval, dan multi-tenant tidak masuk MVP. Deployment publik hanya boleh memakai data demo non-sensitif.

## 3. Scope MVP

### 3.1 Employee Management

- Membuat employee dengan identitas minimum.
- Melihat daftar dan detail employee.
- Mengubah identitas minimum.
- Menonaktifkan employee tanpa menghapus salary history.

### 3.2 Salary Prediction & Calculation

- Memilih employee aktif.
- Memasukkan `knowledge_score`, `technical_score`, `logical_score`, dan `years_of_experience`.
- Menjalankan inference memakai artifact `LinearRegression` yang sudah dilatih.
- Menampilkan `predicted_base_salary` sebagai estimasi.
- Memasukkan periode kerja, applicable work days, worked days, overtime hours, dan overtime rate.
- Menghitung calculated base salary, normal working hours, overtime pay, dan estimated total salary.
- Menyimpan setiap calculation final sebagai salary record baru.

### 3.3 Prediction History

- Menampilkan salary records tersimpan.
- Memfilter berdasarkan employee dan reporting month.
- Membuka detail record beserta input, model version, OOD status, dan hasil calculation.

### 3.4 Monthly Report

- Memilih bulan dan tahun.
- Mengambil data hanya dari application database.
- Menampilkan employee, predicted base salary, calculated base salary, periode, applicable/worked days, normal working hours, overtime hours, overtime pay, dan estimated total salary.
- Menampilkan jumlah record serta total calculated base salary, overtime pay, dan estimated total salary untuk seluruh record terpilih.
- Mengekspor CSV dan Excel.
- Menyediakan halaman print yang dapat disimpan sebagai PDF oleh browser.

### 3.5 Model Information

- Menjelaskan dataset yang benar-benar digunakan.
- Menampilkan features dan target aktual.
- Menjelaskan preprocessing, split, algoritma, dan limitations.
- Menampilkan R2, MAE, atau RMSE hanya dari metadata artifact hasil evaluation aktual.

## 4. Core invariants

- Model utama adalah scikit-learn `LinearRegression`.
- Python hanya digunakan untuk offline training/evaluation dan JSON artifact export.
- Laravel melakukan request-time inference dari coefficients/intercept pada trusted JSON artifact.
- Python inference API dan per-request Python process tidak digunakan.
- Target model wajib monthly base salary dalam IDR.
- Overtime bukan model feature dan tidak mengubah `predicted_base_salary`.
- Training tidak berjalan dalam HTTP request atau saat prediction.
- Dataset training terpisah dari application database.
- User prediction tidak otomatis menjadi training data.
- Server melakukan authoritative validation.
- Salary memakai decimal arithmetic, bukan binary floating-point arithmetic.
- Setiap record menyimpan model version agar prediction dapat ditelusuri.
- Setiap prediction di luar observed training range menyimpan OOD flag dan menampilkan warning.
- Hasil selalu disebut estimasi, bukan salary pasti atau hak kompensasi.

## 5. Non-goals

- Authentication dan role management.
- Approval workflow.
- Attendance, fingerprint, cuti, notification, pajak, BPJS, tunjangan, dan potongan kompleks.
- Payment atau payroll disbursement.
- Dataset upload melalui dashboard.
- Automatic retraining.
- Multiple ML algorithms atau model comparison dashboard.
- Queue, cache server, microservice, event bus, atau cloud ML platform.
- Integrasi HR eksternal.
- Assessment untuk menghasilkan tiga score.

## 6. Acceptance criteria MVP

1. Reviewer dapat menjalankan project mengikuti dokumentasi tanpa langkah penting tersembunyi.
2. Python training command menghasilkan satu trusted JSON model artifact dari dataset tervalidasi.
3. Evaluation menghasilkan metric aktual yang dapat direproduksi dengan konfigurasi tercatat.
4. Prediction memakai artifact tersimpan tanpa retraining.
5. Laravel/Python parity checks membuktikan inference formula cocok dalam documented tolerance.
6. Input di luar contract ditolak atau diberi warning berbasis metadata dataset.
7. Salary calculation mengikuti formula di `05-salary-business-rules.md`.
8. Record valid tersimpan dan muncul pada history serta monthly report.
9. CSV, Excel, dan print view berisi data yang konsisten dengan database.
10. Automated tests penting lulus.
11. UI menampilkan disclaimer dan limitations model.

## 7. ML readiness record

Milestone 0 telah menyelesaikan readiness gate untuk active synthetic dataset:

- `data_train/salary_500.csv` dibuat sendiri dan disetujui untuk public Git;
- raw target `salary` ditetapkan sebagai monthly base salary dalam IDR;
- `knowledge`, `technical`, dan `logical` adalah integer score 0–100;
- 500 rows lengkap, numeric, finite, dan tidak memiliki exact feature-target duplicate;
- seluruh rows dipertahankan tanpa imputation atau outlier removal;
- evaluation memakai shuffled 80/20 split dengan seed 42 dan five-fold
  cross-validation hanya pada training set.

Dataset diisi secara acak dan hanya mendukung demonstrasi portfolio. Dataset dan
metric tidak boleh diklaim mewakili salary distribution atau akurasi dunia nyata.
