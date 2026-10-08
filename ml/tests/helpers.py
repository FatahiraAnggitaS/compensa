from __future__ import annotations

import csv
from pathlib import Path

HEADER = ["no", "knowledge", "technical", "logical", "year_experience", "salary"]


def valid_rows(count: int = 12) -> list[dict[str, str]]:
    return [
        {
            "no": str(index + 1),
            "knowledge": str(40 + index),
            "technical": str(50 + index),
            "logical": str(60 + index),
            "year_experience": f"{index / 10:.1f}",
            "salary": f"Rp {2_500_000 + index * 100_000:,.2f}",
        }
        for index in range(count)
    ]


def write_dataset(
    path: Path,
    rows: list[dict[str, str]],
    *,
    header: list[str] | None = None,
) -> Path:
    selected_header = header or HEADER
    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(
            handle, fieldnames=selected_header, delimiter=";", extrasaction="ignore"
        )
        writer.writeheader()
        writer.writerows(rows)
    return path
