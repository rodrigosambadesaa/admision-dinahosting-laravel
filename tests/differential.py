#!/usr/bin/env python3
"""Compare exact CLI JSON and original JS sequences against the Laravel port."""
from __future__ import annotations

import json
import os
from pathlib import Path
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
ORIGINAL = Path(os.environ.get("REFERENCE_ORIGINAL", ROOT / "reference")).resolve()
DJANGO = Path(os.environ.get("REFERENCE_DJANGO", ROOT / "django")).resolve()

CASES = [
    ("ts:0", "ts:21"),
    ("ts:21", "ts:0"),
    ("ts:-50", "ts:-1"),
    ("ts:1", "ts:1"),
    ("ts:5", "ts:13"),
    ("ts:354224848179261915075", "ts:354224848179261915075"),
    ("ts:" + "9" * 150, "ts:" + "9" * 150),
    ("ts:+" + "9" * 256, "ts:+" + "9" * 256),
    ("2024-02-29 00:00:00", "2024-02-29 23:59:59"),
    ("2026-06-30 23:59:59", "2026-06-01 00:00:00"),
    ("ts:0", "1970-01-01 00:00:13"),
    ("0000-01-01 00:00:00", "0000-01-01 00:00:00"),
    ("0000-02-29 00:00:00", "0000-02-29 00:00:00"),
    ("0099-12-31 23:59:59", "0099-12-31 23:59:59"),
]

JS_SCRIPT = r"""
const s = require(process.argv[1]);
const r = new s.CustomRangeProvider(process.argv[2], process.argv[3]).getRange();
const values = new s.FibonacciRangeResolver(new s.FibonacciSequenceGenerator()).resolve(r);
console.log(JSON.stringify({
    start: String(r.startTimestamp),
    end: String(r.endTimestamp),
    values: values.map(String)
}));
"""


def capture(command: list[str], cwd: Path) -> dict:
    process = subprocess.run(
        command, cwd=cwd, env={**os.environ, "APP_ENV": "testing",
                               "DJANGO_DEBUG": "1", "SESSION_DRIVER": "array"},
        capture_output=True, text=True, check=False, timeout=30,
    )
    if process.returncode:
        raise AssertionError(f"{command[0]} exited {process.returncode}:\n{process.stderr}\n{process.stdout}")
    try:
        return json.loads(process.stdout)
    except json.JSONDecodeError as exc:
        raise AssertionError(f"{command[0]} did not return clean JSON: {process.stdout}") from exc


def main() -> None:
    assert (ROOT / "artisan").is_file()
    assert (ORIGINAL / "PHP/fibonacci.php").is_file()
    assert (ORIGINAL / "JAVASCRIPT/fibonacci.js").is_file()
    assert (DJANGO / "manage.py").is_file()

    for start, end in CASES:
        laravel = capture(["php", str(ROOT / "artisan"), "fibonacci", start, end], ROOT)
        original = capture(["php", str(ORIGINAL / "PHP/fibonacci.php"), start, end], ORIGINAL)
        django = capture([sys.executable, str(DJANGO / "manage.py"), "fibonacci", start, end], DJANGO)
        assert laravel == original == django, f"CLI mismatch for {start} / {end}"
        js = capture(["node", "-e", JS_SCRIPT, str(ORIGINAL / "JAVASCRIPT/fibonacci.js"), start, end], ORIGINAL)
        custom = laravel["custom_range"]
        assert (js["start"], js["end"], js["values"]) == (
            custom["range"]["start_timestamp"], custom["range"]["end_timestamp"],
            custom["fibonacci_timestamps"]), f"JS mismatch for {start} / {end}"
        print(f"PASS PHP / JS / Django / Laravel: {start} .. {end}")
    print(f"All {len(CASES)} cross-runtime cases passed.")


if __name__ == "__main__":
    main()
