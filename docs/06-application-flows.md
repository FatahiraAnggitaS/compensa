# Application Flows

## Core flow

```text
Nama employee diketik langsung
  -> Knowledge, Technical, Logical, Years of Experience
  -> Laravel inference dari trusted JSON artifact
  -> predicted monthly base salary
  -> immutable prediction record
  -> prediction history/detail
```

## Navigation

Compensa memiliki tiga area:

1. Prediksi Gaji
2. Riwayat Prediksi
3. Informasi Model

Tidak ada Monthly Report atau export.

## Salary Prediction

Root page menampilkan input nama employee, empat feature, observed ranges, result, OOD warning, model version, dan disclaimer.

Submit menjalankan validation, inference, dan save sebagai satu operation. Success memakai Post/Redirect/Get. Flash result hanya tampil pada redirected request pertama sehingga refresh GET tidak mengulang insert. JavaScript hanya mencegah double submit.

Form dinonaktifkan hanya bila artifact tidak valid.

## Prediction History

- Default ordering newest-first.
- Pencarian nama employee opsional dan case-insensitive.
- Pagination 15 record.
- Detail menampilkan employee, empat input, predicted base salary, OOD status, model version, dan waktu pencatatan.
- Correction membuat prediction baru; record lama tidak diedit.

## Model Information

Halaman membaca artifact dan menampilkan dataset, feature ranges, target, equation, split, metrics, runtime, model version, serta limitations aktual. Artifact invalid menghasilkan HTTP 200 safe unavailable state tanpa metric palsu.

## Public demo

Saat `APP_PUBLIC_DEMO=true`, prediction tetap aktif dan dibatasi 10 request/menit/IP. Banner menjelaskan bahwa nama dan input harus fiktif karena history dapat dilihat pengunjung lain.

## Accessibility

- Semantic headings dan landmarks.
- Label eksplisit untuk setiap field.
- Validation error terhubung melalui `aria-invalid` dan `aria-describedby`.
- Visible focus, keyboard-safe drawer, table caption, dan scoped headers.
- Layout tetap usable pada mobile.
