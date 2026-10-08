from __future__ import annotations

import copy
import json
import tempfile
import unittest
from datetime import UTC, datetime, timedelta
from pathlib import Path

from ml.salary_ml.artifact import build_artifact, write_artifact_atomic
from ml.salary_ml.contract import FEATURES, PROJECT_ROOT
from ml.salary_ml.data_validation import DatasetValidationError, validate_dataset
from ml.salary_ml.training import train_model


class ArtifactTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.dataset = validate_dataset(PROJECT_ROOT / "data_train" / "salary_500.csv")
        cls.training = train_model(cls.dataset)

    def test_model_version_is_stable_when_timestamp_changes(self) -> None:
        first = build_artifact(
            self.dataset, self.training, trained_at=datetime(2026, 1, 1, tzinfo=UTC)
        )
        second = build_artifact(
            self.dataset,
            self.training,
            trained_at=datetime(2026, 1, 1, tzinfo=UTC) + timedelta(days=1),
        )

        self.assertEqual(first["model_version"], second["model_version"])
        self.assertNotEqual(first["trained_at"], second["trained_at"])

    def test_parity_cases_use_the_exported_linear_equation(self) -> None:
        artifact = build_artifact(self.dataset, self.training)
        coefficients = {
            item["feature"]: item["value"] for item in artifact["model"]["coefficients"]
        }
        for case in artifact["parity"]["cases"]:
            expected = artifact["model"]["intercept"] + sum(
                coefficients[feature] * case["inputs"][feature] for feature in FEATURES
            )
            self.assertAlmostEqual(expected, case["expected_prediction"], places=8)
        self.assertEqual(artifact["parity"]["absolute_tolerance_idr"], 0.01)

    def test_atomic_writer_outputs_strict_sorted_json(self) -> None:
        artifact = build_artifact(self.dataset, self.training)
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "artifact.json"
            write_artifact_atomic(artifact, path)
            loaded = json.loads(path.read_text(encoding="utf-8"))
            self.assertEqual(loaded["model_version"], artifact["model_version"])
            self.assertEqual(list(loaded), sorted(loaded))

    def test_failed_validation_preserves_existing_artifact(self) -> None:
        artifact = build_artifact(self.dataset, self.training)
        invalid = copy.deepcopy(artifact)
        invalid["model"]["intercept"] = float("nan")
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "artifact.json"
            path.write_text("trusted-old-artifact", encoding="utf-8")
            with self.assertRaises(DatasetValidationError):
                write_artifact_atomic(invalid, path)
            self.assertEqual(path.read_text(encoding="utf-8"), "trusted-old-artifact")


if __name__ == "__main__":
    unittest.main()
