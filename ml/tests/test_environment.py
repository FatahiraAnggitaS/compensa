import importlib.metadata
import unittest


class EnvironmentTest(unittest.TestCase):
    def test_training_dependencies_match_pins(self) -> None:
        expected_versions = {
            "pandas": "3.0.6",
            "ruff": "0.16.10",
            "scikit-learn": "1.9.1",
        }

        for package, expected_version in expected_versions.items():
            with self.subTest(package=package):
                self.assertEqual(importlib.metadata.version(package), expected_version)


if __name__ == "__main__":
    unittest.main()
