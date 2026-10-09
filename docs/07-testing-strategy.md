# Testing Strategy

## 1. ML dan data

- Exact dataset hash, schema, UTF-8 BOM, delimiter, dan salary format.
- Missing, duplicate, invalid range, non-finite value, dan target leakage rejection.
- Deterministic 400/100 split seed 42.
- Five-fold CV hanya pada training rows.
- Finite coefficients, intercept, predictions, dan metrics.
- Stable model version dan atomic artifact replacement.

## 2. Predictor

- Feature order dan required features.
- Tiga Python/PHP parity cases dalam tolerance `0.01` IDR.
- Two-decimal BCMath rounding.
- Positive, finite, dan storage-limit checks.
- OOD detection dari observed ranges.

## 3. Database dan service

- Direct-name prediction menyimpan trimmed employee name, inputs, output, currency, model version, OOD flag, dan timestamp.
- Legacy employee relation, backfill nama, nullable FK, delete restriction, dan guarded rollback tetap diuji.
- Legacy calculation fields tetap nullable dan tidak diisi writer aktif.
- Nullable migration forward/rollback pada empty table.
- Rollback guard saat pure prediction record tersedia.
- Insert failure tidak menghasilkan partial record.

## 4. Web

- Form hanya memuat nama employee dan empat feature.
- Form tidak memuat periode, hari kerja, lembur, total, report, atau export.
- Invalid input dan invalid artifact tidak menyimpan record.
- Post/Redirect/Get tidak membuat duplicate record saat refresh.
- History ordering, pencarian nama employee, pagination, detail, dan OOD warning.
- Monthly report routes tidak tersedia.
- Employee Management route tidak tersedia.
- Public demo warning, prediction throttle, dan security headers.

## 5. Manual checks

- Keyboard-only flow dan visible focus.
- Mobile/desktop form serta history table.
- Drawer focus trap, Escape, dan focus restoration.
- Disclaimer dan OOD warning tidak bergantung pada warna.

## 6. Quality gates

```text
php artisan test
vendor/bin/pint --test
python -m unittest discover -s ml/tests
python -m ruff check ml
python -m ruff format --check ml
python ml/train.py --dataset data_train/salary_500.csv
npm run build
composer validate --strict --no-check-publish
composer audit --locked --no-interaction
npm audit --audit-level=high
```

Jangan mengklaim gate lulus tanpa command aktual.
