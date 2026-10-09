# Prediction Rules

## 1. Output contract

`predicted_base_salary` adalah estimasi monthly base salary dalam IDR dari model terlatih. Nilai ini satu-satunya salary output aplikasi.

Compensa tidak menghitung periode kerja, prorata, jam kerja, lembur, allowance, deduction, pajak, BPJS, atau total payroll.

## 2. Input contract

- `knowledge_score`: integer 0–100.
- `technical_score`: integer 0–100.
- `logical_score`: integer 0–100.
- `years_of_experience`: numeric non-negative, maksimal 2 desimal, batas request 999.99.
- `employee_name`: required, di-trim, maksimal 150 karakter, dan disimpan sebagai snapshot record.

## 3. Model equation

```text
raw_prediction = intercept + sum(coefficient[feature] * input[feature])
```

Feature order wajib sama dengan artifact. Output non-finite, nol, negatif, atau lebih besar dari `Decimal(18,2)` ditolak.

## 4. Rounding

Raw Linear Regression memakai float untuk parity dengan scikit-learn. Output lalu dikonversi melalui locale-independent string dan dibulatkan ke dua desimal memakai BCMath `RoundingMode::HalfAwayFromZero`.

```text
predicted_base_salary = round_half_up(raw_prediction, 2)
```

Browser tidak menghitung hasil authoritative.

## 5. OOD

Observed ranges dari artifact:

- Knowledge: 40–90.
- Technical: 50–90.
- Logical: 50–90.
- Years of Experience: 0–3.7.

Input valid di luar observed range tetap diprediksi dan disimpan dengan `has_ood_input=true`. UI menampilkan warning bahwa hasil merupakan ekstrapolasi dan dapat kurang andal.

## 6. Disclaimer

Dataset bersifat sintetis. Prediction bukan standar gaji pasar, hak kompensasi, atau rekomendasi keputusan HR.
