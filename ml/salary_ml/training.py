from __future__ import annotations

import math
from dataclasses import dataclass

import numpy as np
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, r2_score, root_mean_squared_error
from sklearn.model_selection import KFold, train_test_split

from .contract import CV_FOLDS, SPLIT_SEED, TEST_SIZE
from .data_validation import DatasetValidationError, ValidatedDataset


@dataclass(frozen=True)
class TrainingResult:
    model: LinearRegression
    train_indices: tuple[int, ...]
    test_indices: tuple[int, ...]
    train_row_ids: tuple[int, ...]
    test_row_ids: tuple[int, ...]
    held_out_metrics: dict[str, float]
    cv_fold_metrics: tuple[dict[str, float | int], ...]
    cv_summary: dict[str, dict[str, float]]


def _metrics(y_true: np.ndarray, y_predicted: np.ndarray) -> dict[str, float]:
    values = {
        "r2": float(r2_score(y_true, y_predicted)),
        "mae": float(mean_absolute_error(y_true, y_predicted)),
        "rmse": float(root_mean_squared_error(y_true, y_predicted)),
    }
    if not all(math.isfinite(value) for value in values.values()):
        raise DatasetValidationError("Model evaluation produced non-finite metrics.")
    return values


def train_model(dataset: ValidatedDataset) -> TrainingResult:
    if len(dataset.features) < 10:
        raise DatasetValidationError("Dataset is too small for the evaluation contract.")

    all_indices = np.arange(len(dataset.features))
    train_indices, test_indices = train_test_split(
        all_indices,
        test_size=TEST_SIZE,
        random_state=SPLIT_SEED,
        shuffle=True,
    )
    x_train = dataset.features.iloc[train_indices]
    y_train = dataset.target.iloc[train_indices]
    x_test = dataset.features.iloc[test_indices]
    y_test = dataset.target.iloc[test_indices]

    model = LinearRegression().fit(x_train, y_train)
    held_out_metrics = _metrics(y_test.to_numpy(), model.predict(x_test))

    fold_metrics: list[dict[str, float | int]] = []
    splitter = KFold(n_splits=CV_FOLDS, shuffle=True, random_state=SPLIT_SEED)
    for fold_number, (fold_train, fold_validation) in enumerate(splitter.split(x_train), start=1):
        fold_model = LinearRegression().fit(x_train.iloc[fold_train], y_train.iloc[fold_train])
        fold_result = _metrics(
            y_train.iloc[fold_validation].to_numpy(),
            fold_model.predict(x_train.iloc[fold_validation]),
        )
        fold_metrics.append({"fold": fold_number, **fold_result})

    summary = {
        metric: {
            "mean": float(np.mean([fold[metric] for fold in fold_metrics])),
            "population_std": float(np.std([fold[metric] for fold in fold_metrics], ddof=0)),
        }
        for metric in ("r2", "mae", "rmse")
    }
    finite_values = [float(model.intercept_), *map(float, model.coef_)]
    finite_values.extend(value for metric in summary.values() for value in metric.values())
    if not all(math.isfinite(value) for value in finite_values):
        raise DatasetValidationError("Model training produced non-finite values.")

    return TrainingResult(
        model=model,
        train_indices=tuple(map(int, train_indices)),
        test_indices=tuple(map(int, test_indices)),
        train_row_ids=tuple(dataset.row_ids[index] for index in train_indices),
        test_row_ids=tuple(dataset.row_ids[index] for index in test_indices),
        held_out_metrics=held_out_metrics,
        cv_fold_metrics=tuple(fold_metrics),
        cv_summary=summary,
    )
