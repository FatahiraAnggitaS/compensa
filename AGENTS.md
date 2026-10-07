@C:\Users\fatah\.codex\RTK.md

# AGENTS.md — Compensa

## 1. Required project context

`docs/` adalah source of truth perencanaan project Compensa.

Sebelum mengerjakan task non-trivial:

1. inspeksi repository aktual;
2. baca `docs/README.md` secara lengkap;
3. baca dokumen domain yang relevan dari routing table di bawah;
4. periksa `docs/10-decision-log.md` untuk keputusan aktif, superseded decisions, dan open blockers;
5. baru rencanakan atau ubah implementation.

Jangan meminta user mengulang informasi yang sudah tersedia dan masih valid dalam `docs/`.

## 2. Documentation routing

| Area task | Dokumen wajib |
| --- | --- |
| Scope, requirement, acceptance criteria, non-goals | `docs/01-requirements.md` |
| Architecture, technology stack, module boundaries, artifact flow | `docs/02-architecture.md` |
| Models, migrations, constraints, indexes, persistence | `docs/03-database-design.md` |
| Dataset, training, evaluation, JSON artifact, inference parity | `docs/04-machine-learning.md` |
| Proration, working hours, overtime, rounding, salary formulas | `docs/05-salary-business-rules.md` |
| Pages, forms, validation states, history, reports, exports, accessibility | `docs/06-application-flows.md` |
| Test strategy dan quality gates | `docs/07-testing-strategy.md` |
| Environment, security, release, deployment, rollback | `docs/08-deployment.md` |
| Implementation order dan milestone stop conditions | `docs/09-roadmap.md` |
| Accepted, required, conditional, superseded decisions, dan blockers | `docs/10-decision-log.md` |

Untuk perubahan lintas domain, baca seluruh dokumen terkait. Jangan hanya membaca satu file bila invariant dimiliki dokumen lain.

## 3. Source-of-truth precedence

Jika terdapat konflik, gunakan urutan berikut:

1. requirement terbaru user;
2. repository aktual;
3. dataset dan model artifact aktual;
4. active decisions serta domain contract dalam `docs/`;
5. dokumentasi resmi dependency sesuai versi terpasang;
6. referensi eksternal.

Laporkan konflik secara eksplisit. Jangan diam-diam memilih satu sumber atau mempertahankan dokumentasi yang sudah kedaluwarsa.

Decision berstatus `Superseded` hanya history dan tidak boleh digunakan sebagai implementation contract.

## 4. Current baseline handling

Jangan hardcode ringkasan architecture, package version, schema, metric, atau roadmap di file ini. Ambil semuanya dari repository dan `docs/` agar `AGENTS.md` tidak drift.

Khusus technology stack:

- verifikasi dependency manifest dan lockfile aktual;
- gunakan active architecture decision dari `docs/02-architecture.md` dan `docs/10-decision-log.md`;
- jangan menghidupkan kembali framework atau runtime dari superseded decision;
- jangan menambah service, dependency, preprocessing, atau infrastructure yang tidak diperlukan acceptance criteria.

Gunakan Laravel Boost tools/guidance yang sudah terpasang bila relevan. Jangan reinstall atau upgrade Laravel Boost tanpa requirement eksplisit.

## 5. Documentation synchronization

Perubahan berikut wajib memperbarui dokumen terkait dalam task yang sama:

- scope atau acceptance criteria;
- architecture atau technology stack;
- database schema/constraint;
- dataset, feature, target, preprocessing, evaluation, atau artifact contract;
- salary/overtime formula dan rounding;
- UI flow, report, atau export behavior;
- test/quality gate;
- deployment, security, atau rollback process.

Perubahan material juga wajib dicatat dalam `docs/10-decision-log.md` dengan status dan alasan yang jelas.

Jangan mengubah fakta terverifikasi menjadi rencana. Jangan menulis rencana seolah-olah sudah diimplementasikan.

## 6. Blockers and stop conditions

Sebelum implementation, cek open blockers pada akhir `docs/10-decision-log.md` dan milestone stop condition pada `docs/09-roadmap.md`.

Berhenti dan minta keputusan user bila blocker memengaruhi makna project, antara lain:

- dataset source/provenance/license;
- target column atau salary contract;
- feature meaning/range/precision;
- model/preprocessing architecture;
- fixed-precision money implementation;
- destructive data/schema operation;
- credential atau external deployment action.

Untuk keputusan kecil, reversible, dan sudah dibatasi docs, gunakan best judgment.

## 7. Evidence and anti-hallucination

Jangan mengarang:

- dataset fact atau provenance;
- coefficient/intercept;
- split, seed, metric, prediction, atau model version;
- installed package version;
- route, environment variable, schema, atau deployment status;
- test/build/evaluation result.

Jika belum diperiksa, tulis **belum diverifikasi**.

Setiap metric atau model claim harus berasal dari command/artifact aktual. R² bukan classification accuracy.

## 8. Implementation workflow

Untuk task non-trivial:

```text
UNDERSTAND
INSPECT REPOSITORY + DOCS
VERIFY FACTS AND BLOCKERS
PLAN MINIMUM CHANGE
IMPLEMENT
RUN FOCUSED TESTS / EVALUATION
SECURITY + ML REVIEW
FINAL DIFF + DOC SYNC REVIEW
```

Jaga training terpisah dari request-time inference. Jangan memakai user predictions sebagai training labels. Jangan load artifact dari upload atau untrusted path.

## 9. Verification and final report

Jalankan gate relevan dari `docs/07-testing-strategy.md`. Jangan mengklaim berhasil atau tested tanpa command aktual.

Laporan akhir singkat:

### Apa yang dikerjakan
Perubahan aktual.

### Keputusan teknis
Keputusan penting dan referensi dokumennya.

### Model/Data
Fakta model/data yang berubah; tulis belum diverifikasi bila memang belum.

### Verification
Command dan hasil aktual.

### Security & Guardrails
Validation, secret, artifact trust, leakage, serta salary/model limitations yang diperiksa.

### Catatan
Open blockers, risiko, dan hal yang belum diverifikasi.
