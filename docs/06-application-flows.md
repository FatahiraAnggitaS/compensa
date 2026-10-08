# Application Flows

## End-to-end core flow

```text
Employee aktif
  -> empat feature numerik
  -> inference dari committed JSON artifact
  -> predicted monthly base salary
  -> prorata worked/applicable days
  -> overtime hours × user-provided rate
  -> estimated total salary
  -> transactional immutable salary record
  -> history/detail
  -> monthly report -> CSV/XLSX/browser print
```

Inference hanya memakai empat feature model. Periode, worked days, overtime hours, dan overtime rate tidak masuk ke Linear Regression. Semua kalkulasi salary setelah raw prediction memakai BCMath. Monthly report selalu membaca salary records yang sudah tersimpan dan tidak membaca dataset training.

## Public demo boundary

`APP_PUBLIC_DEMO=false` adalah default local. Saat bernilai `true`, list/detail employee tetap tersedia tetapi create/edit/update/status ditolak HTTP 403 dan aksinya tidak dirender. Prediction tetap menyimpan salary record untuk employee demo aktif. Banner meminta pengunjung hanya memakai data fiktif karena prediction history/report bersifat publik. Prediction dibatasi 10 request/menit/IP; CSV/XLSX/print dibatasi 20 request/menit/IP.

## 1. Navigation

MVP memiliki lima area:

1. Employees
2. Salary Prediction
3. Prediction History
4. Monthly Report
5. Model Information

Dashboard statistik, notification center, dan administrative console tidak diperlukan.

Root page membuka Salary Prediction & Calculation agar reviewer langsung masuk core flow.

UI, helper text, validation message, report labels, dan disclaimer memakai Bahasa Indonesia. Code identifiers serta istilah teknis yang lebih natural tetap memakai English.

### Current implementation state

- Lima halaman tersedia sebagai server-rendered Blade views dengan shared responsive navigation.
- Salary Prediction menjadi root page dan menampilkan seluruh kelompok input serta result summary.
- Employee Management memakai application database dan telah memiliki lifecycle lengkap.
- Model Information membaca artifact terverifikasi dan menampilkan model version, dataset, equation, held-out/CV metrics, runtime, serta limitations aktual.
- Salary Prediction aktif untuk employee aktif. Submit tervalidasi menjalankan inference, BCMath calculation, transaction save, lalu Post/Redirect/Get menampilkan snapshot tersimpan satu kali.
- Prediction History aktif dengan list newest-first, pagination, employee/month filters, dan immutable detail snapshot.
- Monthly Report aktif dengan canonical database query, exact selected-record totals, CSV/XLSX exports, serta print-optimized browser PDF workflow.

## 2. Employee Management

### List

- Tampilkan employee code, full name, dan active status, diurutkan berdasarkan code ascending.
- Pencarian case-insensitive memakai code atau name; filter status menerima all, active, atau inactive.
- Pagination menampilkan 15 employee per halaman.

### Create/Edit

- Required fields: employee code dan full name. Employee baru selalu aktif.
- Code di-trim, dinormalisasi uppercase, divalidasi 3–32 karakter `[A-Z0-9_-]` dengan karakter awal huruf/angka, dan unique untuk seluruh status.
- Full name di-trim dan dibatasi 150 karakter.
- Detail menampilkan identity, status, serta timestamp dalam WIB.
- Deactivation dan reactivation memakai status action eksplisit dengan confirmation. Normal UI tidak memiliki delete action.

## 3. Salary Prediction & Calculation

Satu server-rendered form dapat dibagi menjadi bagian berikut:

1. Employee selection.
2. Model features.
3. Reporting period dan work days.
4. Overtime inputs.
5. Prediction/calculation result dan disclaimer.

Submit melakukan prediction, calculation, dan save sebagai satu operation. Tidak diperlukan prediction API terpisah untuk MVP.

`reporting_month` menentukan bucket history/report. Rentang kerja bersifat independen dan boleh melintasi bulan atau tahun selama `period_start <= period_end`. Contoh valid: reporting month Oktober dengan periode 20 September–20 Oktober. Sistem tidak membagi record tersebut otomatis ke bulan September dan Oktober.

UI states:

- initial form;
- field validation errors;
- processing state dengan duplicate submission prevention;
- successful saved result;
- model unavailable/mismatch error;
- extrapolation warning;
- nonsensical prediction limitation.

Client validation membantu UX. Server validation tetap authoritative.

Current success behavior memakai Post/Redirect/Get ke root. Flash menyimpan result snapshot untuk satu redirected request sehingga refresh GET tidak mengulang insert. JavaScript hanya menonaktifkan submit button setelah valid submit dimulai.

Ketiga score memakai integer 0–100. Form menjelaskan observed artifact range;
nilai valid di luar observed range menampilkan extrapolation warning dan tidak
otomatis ditolak.

## 4. Prediction History

- Default ordering: newest record first.
- Filter opsional: employee dan reporting month.
- Detail menunjukkan seluruh stored snapshot, model version, dan OOD warning/flag.
- Record tidak diedit melalui normal UI. Correction membuat calculation baru.
- List menampilkan 15 record per halaman dan mempertahankan query filter pada pagination.

## 5. Monthly Report

Report menerima `reporting_month` sebagai filter wajib melalui HTML month picker (`YYYY-MM`) dan employee sebagai filter opsional. Server tetap memvalidasi format serta nilai bulan.

Columns minimum:

- employee code;
- employee name;
- predicted base salary;
- calculated base salary;
- period start dan end;
- applicable work days;
- worked days;
- normal working hours;
- overtime hours;
- overtime rate;
- overtime pay;
- estimated total salary;
- currency;
- recorded time.

Report membaca salary records, bukan dataset training dan bukan live model prediction.

Summary menampilkan jumlah record serta jumlah `calculated_base_salary`, `overtime_pay`, dan `estimated_total_salary` dari record set terpilih. Label wajib menyebut “total selected records” karena satu employee dapat memiliki beberapa calculation pada bulan yang sama.

Monthly report tidak menampilkan empat model inputs, model version, atau OOD status. Informasi teknis tersebut tersedia pada Prediction History detail.

## 6. Export

### CSV

- UTF-8 encoded.
- Header stabil dan berbahasa teknis konsisten.
- Text cells yang diawali formula marker harus dinetralkan untuk mencegah spreadsheet formula injection.

### Excel

- Satu worksheet untuk selected month.
- Money dan hour cells tetap numeric, bukan display strings.
- Tidak memakai macro.

### Print/PDF

- Print view memiliki title, selected month, generation timestamp, table, dan disclaimer.
- Print stylesheet menghapus navigation serta control.
- User memakai browser Print/Save as PDF.
- Server-side PDF generator bukan bagian MVP.

Keempat format memakai record set dari `MonthlyReportService` yang sama. CSV menggunakan UTF-8 BOM dan stable technical headers. CSV/XLSX menetralkan text yang diawali formula marker; XLSX menyimpan money/hour/day cells sebagai numeric values.

## 7. Model Information

Halaman membaca metadata artifact dan menampilkan:

- algorithm;
- verified dataset source/provenance;
- features dan target;
- currency/cadence;
- preprocessing dan cleaning decisions;
- split/evaluation configuration;
- actual metrics;
- observed feature ranges;
- model version dan training time;
- limitations dan responsible-use disclaimer.

Jika artifact hilang, malformed, terlalu besar, atau incompatible, halaman tetap HTTP 200 dan menampilkan safe unavailable state plus training command tanpa detail exception. Metric disembunyikan, bukan diganti placeholder atau nilai hardcoded.

## 8. Accessibility baseline

- Semantic headings dan landmarks.
- Explicit label untuk setiap input.
- Error terhubung ke field dan dapat dibaca screen reader.
- Visible keyboard focus.
- Button text menjelaskan action.
- Result tidak dibedakan hanya dengan warna.
- Table memiliki caption dan header cells.
- Layout tetap usable pada mobile width.
