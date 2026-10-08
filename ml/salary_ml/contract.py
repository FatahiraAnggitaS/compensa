from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[2]
ARTIFACT_PATH = PROJECT_ROOT / "artifacts" / "salary_linear_regression.json"
CANONICAL_DATASET_PATH = "data_train/salary_500.csv"
APPROVED_DATASET_SHA256 = "b4679821670d29104a8b8cf9162005eca7cb728861b13bfaad3fe30ce668ca65"

RAW_COLUMNS = (
    "no",
    "knowledge",
    "technical",
    "logical",
    "year_experience",
    "salary",
)
FEATURE_MAPPING = {
    "knowledge": "knowledge_score",
    "technical": "technical_score",
    "logical": "logical_score",
    "year_experience": "years_of_experience",
}
FEATURES = tuple(FEATURE_MAPPING.values())
TARGET_SOURCE = "salary"
TARGET = "monthly_base_salary"

ALGORITHM = "linear_regression"
SCHEMA_VERSION = 1
SPLIT_SEED = 42
TEST_SIZE = 0.2
CV_FOLDS = 5
PARITY_TOLERANCE_IDR = 0.01
