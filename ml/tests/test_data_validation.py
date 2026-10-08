from __future__ import annotations

import math
import tempfile
import unittest
from pathlib import Path

from ml.salary_ml.contract import APPROVED_DATASET_SHA256, FEATURES, PROJECT_ROOT, TARGET
from ml.salary_ml.data_validation import DatasetValidationError, validate_dataset
from ml.tests.helpers import HEADER, valid_rows, write_dataset


class DatasetValidationTest(unittest.TestCase):
    def setUp(self) -> None:
        self.temporary_directory = tempfile.TemporaryDirectory()
        self.directory = Path(self.temporary_directory.name)

    def tearDown(self) -> None:
        self.temporary_directory.cleanup()

    def validate_rows(self, rows: list[dict[str, str]]):
        path = write_dataset(self.directory / "dataset.csv", rows)
        return validate_dataset(path, enforce_approved_hash=False)

    def test_approved_dataset_and_canonical_mapping(self) -> None:
        dataset = validate_dataset(PROJECT_ROOT / "data_train" / "salary_500.csv")

        self.assertEqual(dataset.sha256, APPROVED_DATASET_SHA256)
        self.assertEqual(tuple(dataset.features.columns), FEATURES)
        self.assertEqual(dataset.target.name, TARGET)
        self.assertEqual(len(dataset.features), 500)
        self.assertNotIn(TARGET, dataset.features.columns)

    def test_missing_or_extra_columns_are_rejected(self) -> None:
        rows = valid_rows()
        for header in (HEADER[:-1], [*HEADER, "unexpected"]):
            with self.subTest(header=header):
                path = write_dataset(
                    self.directory / f"dataset-{len(header)}.csv", rows, header=header
                )
                with self.assertRaises(DatasetValidationError):
                    validate_dataset(path, enforce_approved_hash=False)

    def test_malformed_salary_and_missing_or_non_finite_values_are_rejected(self) -> None:
        cases = [
            ("salary", "2500000"),
            ("salary", "Rp NaN"),
            ("knowledge", ""),
            ("technical", "Infinity"),
        ]
        for field, value in cases:
            with self.subTest(field=field, value=value):
                rows = valid_rows()
                rows[0][field] = value
                with self.assertRaises(DatasetValidationError):
                    self.validate_rows(rows)

    def test_invalid_scores_and_experience_are_rejected(self) -> None:
        cases = [
            ("knowledge", "40.5"),
            ("technical", "101"),
            ("logical", "-1"),
            ("year_experience", "-0.1"),
            ("year_experience", "1.234"),
            ("year_experience", "NaN"),
        ]
        for field, value in cases:
            with self.subTest(field=field, value=value):
                rows = valid_rows()
                rows[0][field] = value
                with self.assertRaises(DatasetValidationError):
                    self.validate_rows(rows)

    def test_duplicate_id_and_feature_target_row_are_rejected(self) -> None:
        duplicate_id = valid_rows()
        duplicate_id[1]["no"] = duplicate_id[0]["no"]
        with self.assertRaisesRegex(DatasetValidationError, "Row IDs"):
            self.validate_rows(duplicate_id)

        duplicate_row = valid_rows()
        original_id = duplicate_row[1]["no"]
        duplicate_row[1] = {**duplicate_row[0], "no": original_id}
        with self.assertRaisesRegex(DatasetValidationError, "Duplicate feature-target"):
            self.validate_rows(duplicate_row)

    def test_strict_sha_gate_rejects_modified_dataset(self) -> None:
        path = write_dataset(self.directory / "modified.csv", valid_rows())
        with self.assertRaisesRegex(DatasetValidationError, "SHA-256"):
            validate_dataset(path)

    def test_validated_values_are_finite(self) -> None:
        dataset = self.validate_rows(valid_rows())
        self.assertTrue(dataset.features.map(math.isfinite).to_numpy().all())
        self.assertTrue(dataset.target.map(math.isfinite).all())


if __name__ == "__main__":
    unittest.main()
