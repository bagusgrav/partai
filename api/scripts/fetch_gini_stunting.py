#!/usr/bin/env python3
"""Fetch province-level BPS Gini and stunting observations and import them via Artisan."""

from __future__ import annotations

import json
import os
import re
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path


API_DIR = Path(__file__).resolve().parents[1]
START_YEAR = 2021
END_YEAR = 2024


def environment_value(name: str, default: str = "") -> str:
    value = os.environ.get(name)
    if value is not None:
        return value.strip()

    env_file = API_DIR / ".env"
    if not env_file.is_file():
        return default

    for line in env_file.read_text(encoding="utf-8").splitlines():
        key, separator, raw_value = line.partition("=")
        if separator and key.strip() == name:
            value = raw_value.strip()
            if len(value) >= 2 and value[0] == value[-1] and value[0] in {"'", '"'}:
                return value[1:-1]
            return value.split(" #", maxsplit=1)[0].strip()
    return default


API_URL = environment_value(
    "BPS_API_URL",
    environment_value("BPS_API_BASE_URL", "https://webapi.bps.go.id/v1/api"),
).rstrip("/")
API_KEY = environment_value("BPS_API_KEY")
INDICATORS = {
    "gini": environment_value("BPS_GINI_VARIABLE_ID", "2103"),
    "stunting": environment_value("BPS_STUNTING_VARIABLE_ID", "419"),
}


def request_json(path: str) -> dict:
    url = f"{API_URL}/{path.lstrip('/')}"
    request = urllib.request.Request(
        url,
        headers={
            "Accept": "application/json",
            "User-Agent": "PARTAI-CivicTech/1.0",
        },
    )
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            payload = json.load(response)
    except (OSError, urllib.error.URLError, json.JSONDecodeError) as error:
        raise RuntimeError(f"BPS request failed for {redact(path)}: {redact(str(error))}") from error

    if not isinstance(payload, dict) or payload.get("status") != "OK":
        raise RuntimeError(f"BPS returned an invalid response for {redact(path)}")
    return payload


def redact(value: str) -> str:
    if not API_KEY:
        return value
    return value.replace(urllib.parse.quote(API_KEY, safe=""), "[redacted]").replace(API_KEY, "[redacted]")


def api_path(*segments: str) -> str:
    return "/".join(urllib.parse.quote(str(segment), safe="") for segment in segments)


def normalized_name(value: object) -> str:
    return re.sub(r"[^a-z0-9]", "", str(value).lower())


def select_aggregate_dimension(values: list, dimension_name: str) -> list:
    dimensions = [value for value in values if isinstance(value, dict)]
    if len(dimensions) <= 1:
        return dimensions

    def rank(dimension: dict) -> int | None:
        label = normalized_name(dimension.get("label", ""))
        if dimension_name == "turvar":
            if "total" in label or ("perkotaan" in label and "perdesaan" in label) or "gabungan" in label:
                return 0
        elif dimension_name == "turtahun":
            if any(name in label for name in ("tahunan", "annual", "yearly")):
                return 0
            if (
                "semesterii" in label
                or "semester2" in label
                or "semesterdua" in label
                or "akhir" in label and "tahun" in label
                or "desember" in label
                or label.startswith(("s2", "sem2"))
            ):
                return 1
        return None

    ranked = [(rank(dimension), dimension) for dimension in dimensions]
    matches = [(score, dimension) for score, dimension in ranked if score is not None]
    if not matches:
        return dimensions

    best_rank = min(score for score, _ in matches)
    return [dimension for score, dimension in matches if score == best_rank][:1]


def fetch_provinces() -> tuple[dict[str, tuple[str, str]], dict[str, tuple[str, str]]]:
    payload = request_json(api_path("domain", "type", "prov", "key", API_KEY))
    data = payload.get("data")
    domains = data[1] if isinstance(data, list) and len(data) > 1 else None
    if not isinstance(domains, list):
        raise RuntimeError("BPS returned an invalid province catalog")

    by_id = {}
    by_name = {}
    for domain in domains:
        if not isinstance(domain, dict):
            continue
        code = str(domain.get("domain_id", ""))
        name = str(domain.get("domain_name", "")).strip()
        if re.fullmatch(r"\d{4}", code) and code != "0000" and name:
            by_id[code] = (code, name)
            by_name[normalized_name(name)] = (code, name)

    if len(by_id) < 34:
        raise RuntimeError(f"BPS returned only {len(by_id)} provinces; expected at least 34")
    return by_id, by_name


def fetch_years(variable_id: str) -> dict[int, str]:
    page = 1
    pages = 1
    years = {}
    while page <= pages:
        segments = ["list", "model", "th", "domain", "0000", "var", variable_id]
        if page > 1:
            segments += ["page", page]
        payload = request_json(api_path(*segments, "key", API_KEY))
        data = payload.get("data")
        metadata = data[0] if isinstance(data, list) and data else {}
        rows = data[1] if isinstance(data, list) and len(data) > 1 else []
        if not isinstance(rows, list):
            raise RuntimeError(f"BPS returned an invalid period catalog for variable {variable_id}")
        for row in rows:
            if not isinstance(row, dict):
                continue
            try:
                year = int(row["th"])
                period_id = str(row["th_id"])
            except (KeyError, TypeError, ValueError):
                continue
            if START_YEAR <= year <= END_YEAR:
                years[year] = period_id
        if isinstance(metadata, dict) and str(metadata.get("pages", "")).isdigit():
            pages = max(1, int(metadata["pages"]))
        page += 1
    return years


def values_for_year(
    variable_id: str,
    year: int,
    period_id: str,
    provinces_by_id: dict[str, tuple[str, str]],
    provinces_by_name: dict[str, tuple[str, str]],
    indicator_name: str,
) -> list[dict]:
    payload = request_json(
        api_path("list", "model", "data", "domain", "0000", "var", variable_id, "th", period_id, "key", API_KEY)
    )
    if payload.get("data-availability") != "available":
        raise RuntimeError(f"BPS has no {indicator_name} data for {year}")

    data_content = payload.get("datacontent")
    dimensions = []
    for dimension_name in ("vervar", "var", "turvar", "tahun", "turtahun"):
        values = payload.get(dimension_name)
        dimensions.append(values if isinstance(values, list) and values else [{"val": ""}])
    if not isinstance(data_content, dict):
        raise RuntimeError(f"BPS returned an invalid {indicator_name} table for {year}")

    verticals, variables, derived_variables, periods, derived_periods = dimensions
    derived_variables = select_aggregate_dimension(derived_variables, "turvar")
    derived_periods = select_aggregate_dimension(derived_periods, "turtahun")
    values_by_region = {}
    for vertical in verticals:
        if not isinstance(vertical, dict):
            continue
        label = str(vertical.get("label", "")).strip()
        code = str(vertical.get("val", ""))
        normalized_label = normalized_name(label)
        province = provinces_by_id.get(code) or provinces_by_name.get(normalized_label)
        if province is None and (code == "0000" or normalized_label in {"indonesia", "nasional"}):
            province = ("9999", "Nasional (Indonesia)")
        if province is None:
            continue
        candidates = []
        for variable in variables:
            if not isinstance(variable, dict) or str(variable.get("val", "")) != variable_id:
                continue
            for derived_variable in derived_variables:
                if not isinstance(derived_variable, dict):
                    continue
                for period in periods:
                    if not isinstance(period, dict) or str(period.get("val", "")) != period_id:
                        continue
                    for derived_period in derived_periods:
                        if not isinstance(derived_period, dict):
                            continue
                        key = "".join(
                            str(part.get("val", ""))
                            for part in (vertical, variable, derived_variable, period, derived_period)
                        )
                        value = data_content.get(key)
                        if isinstance(value, (int, float)) and not isinstance(value, bool):
                            candidates.append(float(value))
                        elif isinstance(value, str):
                            try:
                                candidates.append(float(value))
                            except ValueError:
                                continue
        if len(candidates) != 1:
            raise RuntimeError(f"BPS returned {len(candidates)} values for {label} {indicator_name} {year}")
        if not (candidates[0] == candidates[0] and abs(candidates[0]) != float("inf")):
            raise RuntimeError(f"BPS returned a non-finite value for {label} {indicator_name} {year}")
        values_by_region[province[0]] = (province[1], candidates[0])

    if len(values_by_region) < 34:
        raise RuntimeError(
            f"BPS returned only {len(values_by_region)} provinces for {indicator_name} {year}; expected at least 34"
        )
    return [
        {
            "indicator_name": indicator_name,
            "region_code": code,
            "region_name": name,
            "period_year": year,
            "value": value,
        }
        for code, (name, value) in sorted(values_by_region.items())
    ]


def main() -> int:
    arguments = sys.argv[1:]
    if arguments == ["--check-config"]:
        return 0 if API_KEY else 1
    if any(argument not in {"--strict"} for argument in arguments):
        print("Usage: fetch_gini_stunting.py [--strict] [--check-config]", file=sys.stderr)
        return 2

    strict = "--strict" in arguments
    records = []
    warnings = []

    if not API_KEY:
        warnings.append("BPS_API_KEY is not configured; no BPS indicators were fetched.")
    else:
        try:
            provinces_by_id, provinces_by_name = fetch_provinces()
        except (OSError, RuntimeError, urllib.error.URLError) as error:
            warnings.append(f"Unable to fetch BPS provinces: {redact(str(error))}")
        else:
            for name, variable_id in INDICATORS.items():
                if not variable_id.isdigit():
                    warnings.append(f"Invalid BPS variable ID for {name}: {variable_id}")
                    continue

                try:
                    years = fetch_years(variable_id)
                except (OSError, RuntimeError, urllib.error.URLError) as error:
                    warnings.append(
                        f"Unable to fetch BPS periods for {name} (variable {variable_id}): {redact(str(error))}"
                    )
                    continue

                if not years:
                    warnings.append(
                        f"BPS has no observations from {START_YEAR} to {END_YEAR} for variable {variable_id}"
                    )
                    continue

                missing_years = sorted(set(range(START_YEAR, END_YEAR + 1)) - years.keys())
                if missing_years:
                    warnings.append(
                        f"BPS variable {variable_id} is missing years: {', '.join(map(str, missing_years))}"
                    )

                for year, period_id in sorted(years.items()):
                    try:
                        records.extend(
                            values_for_year(
                                variable_id,
                                year,
                                period_id,
                                provinces_by_id,
                                provinces_by_name,
                                name,
                            )
                        )
                    except (OSError, RuntimeError, urllib.error.URLError) as error:
                        warnings.append(
                            f"Unable to fetch BPS {name} observations for {year}: {redact(str(error))}"
                        )

    try:
        importer_command = ["php", "artisan", "bps:import-economic-indicators", "--stdin"]
        if strict:
            importer_command.append("--strict")
        result = subprocess.run(
            importer_command,
            cwd=API_DIR,
            input=json.dumps({"records": records, "warnings": warnings}).encode(),
            check=False,
        )
        return result.returncode
    except (OSError, subprocess.SubprocessError) as error:
        print(f"BPS import failed: {redact(str(error))}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
