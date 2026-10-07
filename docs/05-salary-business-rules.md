# Salary Business Rules

## 1. Required terminology

- `predicted_base_salary`: base salary estimate dari model yang sudah mengikuti monetary rounding rule.
- `applicable_work_days`: jumlah hari kerja yang berlaku pada reporting period menurut input operator.
- `worked_days`: jumlah hari kerja employee dalam period.
- `normal_work_hours`: jam kerja normal berdasarkan worked days.
- `calculated_base_salary`: predicted base salary setelah prorata.
- `overtime_pay`: hasil overtime hours dikali overtime rate.
- `estimated_total_salary`: calculated base salary ditambah overtime pay.

Semua label UI harus memakai kata “predicted” atau “estimated”.

## 2. Salary target prerequisite

Target contract MVP adalah base salary untuk satu calendar month dalam IDR. Dataset harus membuktikan bahwa target memenuhi contract tersebut.

Jika dataset menggunakan annual, daily, hourly, mixed, non-IDR, atau unknown salary, integration berhenti. Conversion tidak dilakukan tanpa perubahan requirement yang disetujui.

## 3. Reporting period

- Satu salary record terikat pada satu `reporting_month`.
- `reporting_month` disimpan sebagai hari pertama bulan terpilih.
- `period_start` dan `period_end` harus berada dalam reporting month yang sama untuk MVP.
- Cross-month allocation tidak masuk MVP.
- Aplikasi tidak mengarang kalender hari kerja atau hari libur.
- Operator memasukkan `applicable_work_days` dan `worked_days` dari aturan/periode yang berlaku.

## 4. Normal working hours

Jam kerja normal ditetapkan 8 jam per hari.

```text
normal_work_hours = worked_days * 8
```

Nilai ini untuk reporting. Nilai ini bukan feature model.

## 5. Base salary proration

```text
work_ratio = worked_days / applicable_work_days
calculated_base_salary = predicted_base_salary * work_ratio
```

Jika `worked_days == applicable_work_days`, calculated base salary sama dengan predicted base salary.

Validation wajib memastikan applicable work days lebih dari nol dan worked days tidak melebihi applicable work days.

## 6. Overtime

Overtime rate tidak dihitung oleh formula turunan. Operator memasukkan tarif per jam secara langsung.

```text
overtime_pay = overtime_hours * overtime_rate
```

Jika tidak ada overtime, overtime hours dan overtime rate bernilai nol. Overtime tidak mengubah predicted atau calculated base salary.

Overtime hours menerima decimal sampai 2 angka di belakang koma. Sistem tidak memaksa interval 0,5 jam atau pembulatan jam tertentu.

## 7. Estimated total

```text
estimated_total_salary = calculated_base_salary + overtime_pay
```

Tax, benefits, allowance, deduction, BPJS, dan payroll adjustment tidak masuk formula.

## 8. Numeric rules

- Gunakan decimal arithmetic untuk semua money dan hour calculation.
- Jangan gunakan JavaScript result sebagai authoritative value.
- Backend menghitung ulang seluruh derived value.
- Simpan seluruh monetary fields sebagai `Decimal(18,2)`.
- Ubah model output ke decimal melalui string representation, lalu quantize ke 2 decimal dengan `ROUND_HALF_UP` sebagai `predicted_base_salary`.
- Hitung dan quantize `calculated_base_salary` serta `overtime_pay` ke 2 decimal dengan `ROUND_HALF_UP`.
- Hitung `estimated_total_salary` dari dua stored rounded components agar detail dan total selalu konsisten.
- UI dan exports menampilkan IDR dengan 2 decimal.
- Jangan silently clamp negative model output. Tampilkan warning lalu blokir calculation dan save.
- Jangan menerima NaN atau infinity.

## 9. Input validation

- Empat model features wajib numeric dan finite.
- `years_of_experience`, worked days, overtime hours, dan overtime rate tidak boleh negatif.
- `years_of_experience` menerima decimal sampai 2 angka di belakang koma; valid range tetap mengikuti dataset metadata.
- `knowledge_score`, `technical_score`, dan `logical_score` wajib integer 0–100.
- Input score di luar observed artifact range tetapi masih dalam 0–100 tetap
  valid, memicu OOD warning, dan disimpan dengan `has_ood_input=true`.
- Date range wajib valid dan berada dalam reporting month.
- Employee wajib aktif saat record dibuat.
- Model artifact dan metadata wajib tersedia serta kompatibel.

## 10. Disclaimer

UI wajib menjelaskan bahwa hasil bergantung pada dataset, feature, dan linear model. Hasil bukan standar salary pasar, hak kompensasi, atau rekomendasi keputusan HR.
