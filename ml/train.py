from __future__ import annotations

import argparse
import sys
from pathlib import Path

if __package__ in (None, ""):
    sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from ml.salary_ml import (  # noqa: E402
    ARTIFACT_PATH,
    DatasetValidationError,
    build_artifact,
    train_model,
    validate_dataset,
    write_artifact_atomic,
)


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Train the Compensa salary model.")
    parser.add_argument("--dataset", required=True, help="Path to the approved CSV dataset.")
    return parser.parse_args()


def main() -> int:
    arguments = parse_arguments()
    try:
        dataset = validate_dataset(arguments.dataset)
        training = train_model(dataset)
        artifact = build_artifact(dataset, training)
        write_artifact_atomic(artifact)
    except (DatasetValidationError, OSError, ValueError) as exc:
        print(f"Training failed: {exc}", file=sys.stderr)
        return 1

    metrics = artifact["evaluation"]["held_out_metrics"]
    print(f"Artifact: {ARTIFACT_PATH}")
    print(f"Model version: {artifact['model_version']}")
    print(
        "Held-out metrics: "
        f"R2={metrics['r2']:.6f}, MAE={metrics['mae']:.2f}, RMSE={metrics['rmse']:.2f}"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
