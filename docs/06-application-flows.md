# Application Flows

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

### Current scaffold state

- Lima halaman tersedia sebagai server-rendered Blade views dengan shared responsive navigation.
- Salary Prediction menjadi root page dan menampilkan seluruh kelompok input serta result summary.
- Halaman berbasis data memakai empty state; model metric dan salary value yang belum ada tidak dipalsukan.
- Semua action yang memerlukan database, artifact, prediction, persistence, filter, atau export tetap disabled.
- Scaffolding tidak memiliki POST route, Eloquent model, migration, atau database query.

## 2. Employee Management

### List

- Tampilkan employee code, full name, dan active status.

### Create/Edit

- Required fields: employee code dan full name.
- Server memvalidasi unique employee code.
- Deactivation dipakai untuk employee yang sudah memiliki history.

## 3. Salary Prediction & Calculation

Satu server-rendered form dapat dibagi menjadi bagian berikut:

1. Employee selection.
2. Model features.
3. Reporting period dan work days.
4. Overtime inputs.
5. Prediction/calculation result dan disclaimer.

Submit melakukan prediction, calculation, dan save sebagai satu operation. Tidak diperlukan prediction API terpisah untuk MVP.

UI states:

- initial form;
- field validation errors;
- processing state dengan duplicate submission prevention;
- successful saved result;
- model unavailable/mismatch error;
- extrapolation warning;
- nonsensical prediction limitation.

Client validation membantu UX. Server validation tetap authoritative.

Ketiga score memakai integer 0–100. Form menjelaskan observed artifact range;
nilai valid di luar observed range menampilkan extrapolation warning dan tidak
otomatis ditolak.

## 4. Prediction History

- Default ordering: newest record first.
- Filter opsional: employee dan reporting month.
- Detail menunjukkan seluruh stored snapshot, model version, dan OOD warning/flag.
- Record tidak diedit melalui normal UI. Correction membuat calculation baru.

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

Jika metadata/artifact belum tersedia, halaman menampilkan status belum dilatih. Halaman tidak menampilkan placeholder metric.

## 8. Accessibility baseline

- Semantic headings dan landmarks.
- Explicit label untuk setiap input.
- Error terhubung ke field dan dapat dibaca screen reader.
- Visible keyboard focus.
- Button text menjelaskan action.
- Result tidak dibedakan hanya dengan warna.
- Table memiliki caption dan header cells.
- Layout tetap usable pada mobile width.
