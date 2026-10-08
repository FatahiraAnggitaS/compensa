from __future__ import annotations

import math
import unittest

from sklearn.model_selection import KFold

from ml.salary_ml.contract import FEATURES, PROJECT_ROOT, SPLIT_SEED
from ml.salary_ml.data_validation import validate_dataset
from ml.salary_ml.training import train_model


class TrainingTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.dataset = validate_dataset(PROJECT_ROOT / "data_train" / "salary_500.csv")
        cls.result = train_model(cls.dataset)

    def test_split_is_deterministic_and_keeps_100_rows_held_out(self) -> None:
        second = train_model(self.dataset)
        self.assertEqual(self.result.train_row_ids, second.train_row_ids)
        self.assertEqual(self.result.test_row_ids, second.test_row_ids)
        self.assertEqual(len(self.result.train_row_ids), 400)
        self.assertEqual(len(self.result.test_row_ids), 100)
        self.assertTrue(set(self.result.train_row_ids).isdisjoint(self.result.test_row_ids))

    def test_cross_validation_is_five_fold_and_isolated_to_training_rows(self) -> None:
        folds = list(
            KFold(n_splits=5, shuffle=True, random_state=SPLIT_SEED).split(
                self.result.train_indices
            )
        )
        self.assertEqual(len(self.result.cv_fold_metrics), 5)
        self.assertEqual(
            sum(len(validation) for _, validation in folds),
            len(self.result.train_row_ids),
        )
        self.assertTrue(
            all(max(validation) < len(self.result.train_row_ids) for _, validation in folds)
        )

    def test_model_and_evaluation_values_are_finite(self) -> None:
        values = [float(self.result.model.intercept_), *map(float, self.result.model.coef_)]
        values.extend(self.result.held_out_metrics.values())
        values.extend(
            value for metric in self.result.cv_summary.values() for value in metric.values()
        )
        self.assertTrue(all(math.isfinite(value) for value in values))
        self.assertEqual(len(self.result.model.coef_), len(FEATURES))

    def test_target_never_enters_feature_matrix(self) -> None:
        self.assertEqual(tuple(self.dataset.features.columns), FEATURES)
        self.assertNotIn(self.dataset.target.name, self.dataset.features.columns)


if __name__ == "__main__":
    unittest.main()
