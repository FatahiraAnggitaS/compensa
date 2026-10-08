from __future__ import annotations

import hashlib
import json
import math
import os
import tempfile
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

import sklearn

from .contract import (
    ALGORITHM,
    ARTIFACT_PATH,
    CANONICAL_DATASET_PATH,
    CV_FOLDS,
    FEATURE_MAPPING,
    FEATURES,
    PARITY_TOLERANCE_IDR,
    SCHEMA_VERSION,
    SPLIT_SEED,
    TARGET,
    TARGET_SOURCE,
    TEST_SIZE,
)
from .data_validation import DatasetValidationError, ValidatedDataset
from .training import TrainingResult


def _canonical_json(payload: Any) -> str:
    return json.dumps(
        payload,
        ensure_ascii=False,
        sort_keys=True,
        separators=(",", ":"),
        allow_nan=False,
    )


def _prediction(coefficients: list[float], intercept: float, inputs: list[float]) -> float:
    prediction = intercept + sum(
        coefficient * value for coefficient, value in zip(coefficients, inputs, strict=True)
    )
    if not math.isfinite(prediction):
        raise DatasetValidationError("Parity prediction is not finite.")
    return float(prediction)


def _model_version_payload(
    dataset: ValidatedDataset, training: TrainingResult, coefficients: list[float], intercept: float
) -> dict[str, Any]:
    return {
        "artifact_schema_version": SCHEMA_VERSION,
        "algorithm": ALGORITHM,
        "features": [
            {
                "name": name,
                "source": source,
                "valid_range": [0, 100] if name != "years_of_experience" else [0, None],
                "observed_range": [
                    dataset.observed_ranges[name]["minimum"],
                    dataset.observed_ranges[name]["maximum"],
                ],
            }
            for source, name in FEATURE_MAPPING.items()
        ],
        "target": {
            "name": TARGET,
            "source": TARGET_SOURCE,
            "currency": "IDR",
            "cadence": "monthly",
        },
        "dataset_sha256": dataset.sha256,
        "training_row_ids": list(training.train_row_ids),
        "scikit_learn_version": sklearn.__version__,
        "coefficients": coefficients,
        "intercept": intercept,
    }


def build_artifact(
    dataset: ValidatedDataset,
    training: TrainingResult,
    *,
    trained_at: datetime | None = None,
) -> dict[str, Any]:
    coefficients = [float(value) for value in training.model.coef_]
    intercept = float(training.model.intercept_)
    version_payload = _model_version_payload(dataset, training, coefficients, intercept)
    model_version = (
        "sha256:" + hashlib.sha256(_canonical_json(version_payload).encode("utf-8")).hexdigest()
    )

    minimum = [dataset.observed_ranges[name]["minimum"] for name in FEATURES]
    maximum = [dataset.observed_ranges[name]["maximum"] for name in FEATURES]
    midpoint = [(low + high) / 2 for low, high in zip(minimum, maximum, strict=True)]
    parity_cases = [
        {
            "name": name,
            "inputs": dict(zip(FEATURES, inputs, strict=True)),
            "expected_prediction": _prediction(coefficients, intercept, inputs),
        }
        for name, inputs in (
            ("observed_minimum", minimum),
            ("observed_midpoint", midpoint),
            ("observed_maximum", maximum),
        )
    ]

    timestamp = trained_at or datetime.now(UTC)
    if timestamp.tzinfo is None:
        timestamp = timestamp.replace(tzinfo=UTC)
    artifact = {
        "artifact_schema_version": SCHEMA_VERSION,
        "model_version": model_version,
        "algorithm": ALGORITHM,
        "trained_at": timestamp.astimezone(UTC).isoformat().replace("+00:00", "Z"),
        "features": version_payload["features"],
        "model": {
            "coefficients": [
                {"feature": feature, "value": coefficient}
                for feature, coefficient in zip(FEATURES, coefficients, strict=True)
            ],
            "intercept": intercept,
        },
        "target": version_payload["target"],
        "dataset": {
            "path": CANONICAL_DATASET_PATH,
            "sha256": dataset.sha256,
            "provenance": "Created by the Compensa project owner; no external dataset source.",
            "synthetic": True,
            "rows_before_cleaning": dataset.rows_before_cleaning,
            "rows_after_cleaning": dataset.rows_after_cleaning,
            "cleaning_decision": "No imputation, scaling, outlier removal, or row removal.",
        },
        "evaluation": {
            "split": {
                "strategy": "shuffled_train_test_split",
                "test_size": TEST_SIZE,
                "random_seed": SPLIT_SEED,
                "train_rows": len(training.train_row_ids),
                "test_rows": len(training.test_row_ids),
                "train_row_ids": list(training.train_row_ids),
                "test_row_ids": list(training.test_row_ids),
            },
            "held_out_metrics": training.held_out_metrics,
            "cross_validation": {
                "strategy": "kfold",
                "folds": CV_FOLDS,
                "shuffle": True,
                "random_seed": SPLIT_SEED,
                "scope": "training_rows_only",
                "fold_metrics": list(training.cv_fold_metrics),
                "summary": training.cv_summary,
            },
        },
        "runtime": {
            "python_version": __import__("platform").python_version(),
            "scikit_learn_version": sklearn.__version__,
        },
        "parity": {
            "absolute_tolerance_idr": PARITY_TOLERANCE_IDR,
            "cases": parity_cases,
        },
        "limitations": [
            "The dataset is synthetic and randomly populated, so metrics do not establish real-world salary accuracy.",
            "Predictions are estimates of monthly base salary, not compensation decisions.",
            "Linear Regression may extrapolate unreliably outside observed feature ranges.",
            "User predictions are not ground-truth labels and are not added to training data.",
        ],
    }
    validate_artifact(artifact)
    return artifact


def validate_artifact(artifact: dict[str, Any]) -> None:
    if artifact.get("artifact_schema_version") != SCHEMA_VERSION:
        raise DatasetValidationError("Artifact schema version is invalid.")
    if artifact.get("algorithm") != ALGORITHM:
        raise DatasetValidationError("Artifact algorithm is invalid.")
    if not isinstance(artifact.get("model_version"), str) or not re_fullmatch_sha(
        artifact["model_version"]
    ):
        raise DatasetValidationError("Artifact model version is invalid.")
    coefficient_entries = artifact.get("model", {}).get("coefficients", [])
    if [entry.get("feature") for entry in coefficient_entries] != list(FEATURES):
        raise DatasetValidationError("Artifact feature order is invalid.")
    numeric_values = [entry.get("value") for entry in coefficient_entries]
    numeric_values.append(artifact.get("model", {}).get("intercept"))
    numeric_values.extend(artifact.get("evaluation", {}).get("held_out_metrics", {}).values())
    if not numeric_values or any(
        not isinstance(value, (int, float)) or isinstance(value, bool) or not math.isfinite(value)
        for value in numeric_values
    ):
        raise DatasetValidationError("Artifact contains non-finite model values.")
    if len(artifact.get("parity", {}).get("cases", [])) != 3:
        raise DatasetValidationError("Artifact parity contract is invalid.")


def re_fullmatch_sha(value: str) -> bool:
    import re

    return re.fullmatch(r"sha256:[0-9a-f]{64}", value) is not None


def write_artifact_atomic(artifact: dict[str, Any], path: Path = ARTIFACT_PATH) -> None:
    validate_artifact(artifact)
    path.parent.mkdir(parents=True, exist_ok=True)
    serialized = (
        json.dumps(
            artifact,
            ensure_ascii=False,
            sort_keys=True,
            indent=2,
            allow_nan=False,
        )
        + "\n"
    )
    temporary_name: str | None = None
    try:
        with tempfile.NamedTemporaryFile(
            "w",
            encoding="utf-8",
            dir=path.parent,
            delete=False,
            prefix=f".{path.name}.",
            suffix=".tmp",
        ) as temporary:
            temporary_name = temporary.name
            temporary.write(serialized)
            temporary.flush()
            os.fsync(temporary.fileno())
        os.replace(temporary_name, path)
    finally:
        if temporary_name is not None and Path(temporary_name).exists():
            Path(temporary_name).unlink()
