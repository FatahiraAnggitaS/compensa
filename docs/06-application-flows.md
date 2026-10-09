# Application Flows

## Core flow

```text
Employee aktif
  -> Knowledge, Technical, Logical, Years of Experience
  -> Laravel inference dari trusted JSON artifact
  -> predicted monthly base salary
  -> immutable prediction record
  -> prediction history/detail
```

## Navigation

Compensa memiliki empat area:

1. Prediksi Gaji
2. Employee
3. Riwayat Prediksi
4. Informasi Model

Tidak ada Monthly Report atau export.

## Salary Prediction

Root page menampilkan employee aktif, empat feature, observed ranges, result, OOD warning, model version, dan disclaimer.

Submit menjalankan validation, inference, dan save sebagai satu operation. Success memakai Post/Redirect/Get. Flash result hanya tampil pada redirected request pertama sehingga refresh GET tidak mengulang insert. JavaScript hanya mencegah double submit.

Form dinonaktifkan bila artifact tidak valid atau tidak ada employee aktif.

## Prediction History

- Default ordering newest-first.
- Filter employee opsional.
- Pagination 15 record.
- Detail menampilkan employee, empat input, predicted base salary, OOD status, model version, dan waktu pencatatan.
- Correction membuat prediction baru; record lama tidak diedit.

## Employee Management

- List semua status, search code/name, status filter, dan pagination 15.
- Code di-trim dan dinormalisasi uppercase.
- Employee baru selalu aktif.
- Deactivate/reactivate memakai action eksplisit.
- Tidak ada delete route.

## Model Information

Halaman membaca artifact dan menampilkan dataset, feature ranges, target, equation, split, metrics, runtime, model version, serta limitations aktual. Artifact invalid menghasilkan HTTP 200 safe unavailable state tanpa metric palsu.

## Public demo

Saat `APP_PUBLIC_DEMO=true`, employee mutation disembunyikan dan ditolak 403. Prediction untuk demo employee tetap aktif dan dibatasi 10 request/menit/IP. Banner mewajibkan data fiktif.

## Accessibility

- Semantic headings dan landmarks.
- Label eksplisit untuk setiap field.
- Validation error terhubung melalui `aria-invalid` dan `aria-describedby`.
- Visible focus, keyboard-safe drawer, table caption, dan scoped headers.
- Layout tetap usable pada mobile.
