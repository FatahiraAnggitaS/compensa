# Product Requirements

## 1. Tujuan

Compensa menunjukkan alur portfolio berikut:

```text
employee -> empat model input -> base salary prediction -> saved record -> prediction history
```

Aplikasi hanya memprediksi monthly base salary dalam IDR. Aplikasi bukan payroll, kalkulator kompensasi, benchmark pasar, atau alat keputusan HR.

## 2. Pengguna

Satu operator mengelola employee, membuat prediksi, melihat history, dan membaca informasi model. Authentication, role, approval, dan multi-tenant tidak masuk MVP.

## 3. Scope

### Employee Management

- Simpan employee code, full name, dan active status.
- List, search, detail, create, edit, deactivate, dan reactivate.
- Tidak ada delete route.

### Salary Prediction

- Pilih employee aktif.
- Masukkan `knowledge_score`, `technical_score`, `logical_score`, dan `years_of_experience`.
- Jalankan inference memakai committed `LinearRegression` JSON artifact.
- Tampilkan dan simpan `predicted_base_salary`, model version, dan OOD flag.
- Gunakan Post/Redirect/Get agar refresh tidak membuat record baru.

### Prediction History

- Tampilkan record terbaru lebih dahulu.
- Filter berdasarkan employee.
- Tampilkan empat input, hasil, OOD status, model version, dan waktu pencatatan.
- Record tidak diedit melalui UI.

### Model Information

- Tampilkan dataset, features, target, algorithm, preprocessing decision, split, metrics, runtime, model version, dan limitations dari artifact.

## 4. Invariants

- Model adalah scikit-learn `LinearRegression`.
- Python hanya untuk offline training dan artifact export.
- Laravel melakukan request-time inference tanpa menjalankan Python.
- Dataset training terpisah dari application database.
- Prediction user tidak otomatis menjadi training data.
- Server melakukan validation dan OOD detection.
- Predicted salary dibulatkan dua desimal dengan BCMath.
- Setiap record menyimpan model version.
- Hasil selalu disebut prediksi atau estimasi.

## 5. Non-goals

- Periode kerja dan attendance.
- Prorata gaji.
- Jam kerja normal.
- Lembur dan tarif lembur.
- Estimated total salary.
- Monthly payroll report, CSV, XLSX, atau PDF.
- Pajak, BPJS, tunjangan, potongan, dan payment.
- Authentication, approval, notification, dan integrasi HR.
- Upload dataset, automatic retraining, atau multiple algorithms.
- Assessment untuk menghasilkan tiga score.

## 6. Acceptance criteria

1. Training command menghasilkan trusted artifact dari dataset tervalidasi.
2. Evaluation deterministic menghasilkan metrics aktual.
3. Web melakukan inference tanpa retraining.
4. Python/PHP parity berada dalam tolerance `0.01` IDR.
5. Input valid menghasilkan satu prediction record.
6. Input invalid atau artifact invalid tidak menghasilkan record.
7. OOD input disimpan dengan warning.
8. History hanya menampilkan data prediksi.
9. UI tidak menampilkan periode, prorata, lembur, total salary, report, atau export.
10. Automated tests dan production build lulus.
