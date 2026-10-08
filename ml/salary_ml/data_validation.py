from __future__ import annotations

import csv
import hashlib
import math
import re
from dataclasses import dataclass
from decimal import Decimal, InvalidOperation
from pathlib import Path

import pandas as pd

from .contract import (
    APPROVED_DATASET_SHA256,
    FEATURE_MAPPING,
    FEATURES,
    RAW_COLUMNS,
    TARGET,
)

SALARY_PATTERN = re.compile(r"^Rp [0-9]{1,3}(?:,[0-9]{3})*\.[0-9]{2}$")


class DatasetValidationError(ValueError):
    """Raised when a dataset violates the trusted training contract."""


@dataclass(frozen=True)
class ValidatedDataset:
    features: pd.DataFrame
    target: pd.Series
    row_ids: tuple[int, ...]
    sha256: str
    observed_ranges: dict[str, dict[str, float]]
    rows_before_cleaning: int
    rows_after_cleaning: int


def calculate_sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def _decimal(value: str, field: str, row_number: int) -> Decimal:
    try:
        number = Decimal(value)
    except InvalidOperation as exc:
        raise DatasetValidationError(f"Row {row_number}: {field} must be numeric.") from exc
    if not number.is_finite():
        raise DatasetValidationError(f"Row {row_number}: {field} must be finite.")
    return number


def _decimal_places(number: Decimal) -> int:
    return max(0, -number.as_tuple().exponent)


def validate_dataset(path: str | Path, *, enforce_approved_hash: bool = True) -> ValidatedDataset:
    dataset_path = Path(path)
    if not dataset_path.is_file():
        raise DatasetValidationError("Dataset file does not exist.")

    dataset_hash = calculate_sha256(dataset_path)
    if enforce_approved_hash and dataset_hash != APPROVED_DATASET_SHA256:
        raise DatasetValidationError("Dataset SHA-256 does not match the approved source.")

    try:
        with dataset_path.open("r", encoding="utf-8-sig", newline="") as handle:
            reader = csv.DictReader(handle, delimiter=";")
            if tuple(reader.fieldnames or ()) != RAW_COLUMNS:
                raise DatasetValidationError(
                    "Dataset columns must exactly match the approved raw schema."
                )
            raw_rows = list(reader)
    except UnicodeDecodeError as exc:
        raise DatasetValidationError("Dataset must be valid UTF-8 with optional BOM.") from exc

    if not raw_rows:
        raise DatasetValidationError("Dataset must contain data rows.")

    row_ids: list[int] = []
    feature_rows: list[list[float]] = []
    targets: list[float] = []
    duplicate_rows: set[tuple[float, ...]] = set()

    for csv_row_number, row in enumerate(raw_rows, start=2):
        if None in row or any(value is None or value.strip() == "" for value in row.values()):
            raise DatasetValidationError(f"Row {csv_row_number}: missing values are not allowed.")

        row_id_value = _decimal(row["no"].strip(), "no", csv_row_number)
        if row_id_value != row_id_value.to_integral_value() or row_id_value <= 0:
            raise DatasetValidationError(f"Row {csv_row_number}: no must be a positive integer.")
        row_id = int(row_id_value)

        values: list[float] = []
        for raw_name in ("knowledge", "technical", "logical"):
            number = _decimal(row[raw_name].strip(), raw_name, csv_row_number)
            if number != number.to_integral_value() or not 0 <= number <= 100:
                raise DatasetValidationError(
                    f"Row {csv_row_number}: {raw_name} must be an integer from 0 to 100."
                )
            values.append(float(number))

        experience = _decimal(row["year_experience"].strip(), "year_experience", csv_row_number)
        if experience < 0 or _decimal_places(experience) > 2:
            raise DatasetValidationError(
                f"Row {csv_row_number}: year_experience must be non-negative with at most two decimals."
            )
        values.append(float(experience))

        salary_text = row["salary"].strip()
        if not SALARY_PATTERN.fullmatch(salary_text):
            raise DatasetValidationError(
                f"Row {csv_row_number}: salary must match 'Rp 2,500,000.00'."
            )
        salary = _decimal(
            salary_text.removeprefix("Rp ").replace(",", ""), "salary", csv_row_number
        )
        if salary <= 0:
            raise DatasetValidationError(f"Row {csv_row_number}: salary must be positive.")
        salary_value = float(salary)
        if not math.isfinite(salary_value):
            raise DatasetValidationError(f"Row {csv_row_number}: salary must be finite.")

        duplicate_key = (*values, salary_value)
        if duplicate_key in duplicate_rows:
            raise DatasetValidationError("Duplicate feature-target rows are not allowed.")
        duplicate_rows.add(duplicate_key)
        row_ids.append(row_id)
        feature_rows.append(values)
        targets.append(salary_value)

    if len(row_ids) != len(set(row_ids)):
        raise DatasetValidationError("Row IDs must be unique.")

    features = pd.DataFrame(feature_rows, columns=FEATURES, dtype="float64")
    target = pd.Series(targets, name=TARGET, dtype="float64")
    if TARGET in features.columns or tuple(features.columns) != FEATURES:
        raise DatasetValidationError("Target leakage or invalid feature ordering detected.")
    if not features.map(math.isfinite).to_numpy().all() or not target.map(math.isfinite).all():
        raise DatasetValidationError("Features and target must be finite.")

    observed_ranges = {
        canonical_name: {
            "minimum": float(features[canonical_name].min()),
            "maximum": float(features[canonical_name].max()),
        }
        for canonical_name in FEATURE_MAPPING.values()
    }

    return ValidatedDataset(
        features=features,
        target=target,
        row_ids=tuple(row_ids),
        sha256=dataset_hash,
        observed_ranges=observed_ranges,
        rows_before_cleaning=len(raw_rows),
        rows_after_cleaning=len(raw_rows),
    )
