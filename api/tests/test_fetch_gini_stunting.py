import os
import sys
import unittest
from pathlib import Path
from unittest.mock import patch

API_DIR = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(API_DIR / "scripts"))

with patch.dict(
    os.environ,
    {
        "BPS_API_KEY": "test-key",
        "BPS_API_URL": "https://bps.test/v1/api",
        "BPS_API_BASE_URL": "https://bps.test/v1/api",
        "BPS_GINI_VARIABLE_ID": "2103",
        "BPS_STUNTING_VARIABLE_ID": "419",
    },
):
    import fetch_gini_stunting


class FetchGiniStuntingTest(unittest.TestCase):
    def fetch_records(self, derived_periods: list[dict]) -> list[dict]:
        provinces = {
            f"{1100 + index:04d}": (f"{1100 + index:04d}", f"Province {index}")
            for index in range(34)
        }
        derived_variables = [
            {"val": "1", "label": "Perkotaan"},
            {"val": "2", "label": "Perdesaan"},
            {"val": "3", "label": "Perkotaan+Perdesaan"},
        ]
        data_content = {}

        for region_code in provinces:
            for derived_variable in derived_variables:
                for derived_period in derived_periods:
                    value = (
                        int(derived_variable["val"]) / 10
                        + int(derived_period["val"]) / 100
                    )
                    key = f"{region_code}2103{derived_variable['val']}2021{derived_period['val']}"
                    data_content[key] = value

        payload = {
            "status": "OK",
            "data-availability": "available",
            "vervar": [
                {"val": region_code, "label": region_name}
                for region_code, region_name in provinces.values()
            ],
            "var": [{"val": "2103", "label": "Gini Ratio"}],
            "turvar": derived_variables,
            "tahun": [{"val": "2021", "label": "2021"}],
            "turtahun": derived_periods,
            "datacontent": data_content,
        }

        with patch.object(fetch_gini_stunting, "request_json", return_value=payload):
            return fetch_gini_stunting.values_for_year(
                "2103",
                2021,
                "2021",
                provinces,
                {},
                "gini",
            )

    def test_selects_combined_region_and_annual_value_from_multiple_dimensions(self) -> None:
        records = self.fetch_records([
            {"val": "1", "label": "Tahunan"},
            {"val": "2", "label": "Semester I"},
            {"val": "3", "label": "Semester II"},
        ])

        self.assertEqual(34, len(records))
        self.assertEqual({0.31}, {round(record["value"], 2) for record in records})

    def test_falls_back_to_second_semester_when_annual_value_is_unavailable(self) -> None:
        records = self.fetch_records([
            {"val": "2", "label": "Semester I"},
            {"val": "3", "label": "Semester II"},
        ])

        self.assertEqual(34, len(records))
        self.assertEqual({0.33}, {round(record["value"], 2) for record in records})


if __name__ == "__main__":
    unittest.main()
