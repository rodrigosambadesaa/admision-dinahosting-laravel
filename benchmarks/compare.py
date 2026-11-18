#!/usr/bin/env python3
"""Compare original PHP, JS, Django and Laravel on the same runner.

Hot core: original resolvers without framework bootstrap. Laravel's core reuses
the ported PHP domain classes, not Artisan, to measure like-for-like operations.
CLI: separate whole-process PHP, Django and Laravel commands (no JS CLI).
Five process samples after a discarded process warmup; median CPU and peak RSS.
All datasets must match count, first, last and exact SHA256 of their sequences.
"""
from __future__ import annotations

import argparse
from datetime import datetime, timezone
import json
import os
from pathlib import Path
import platform
import random
import shutil
import statistics
import subprocess
import sys
import tempfile

SAMPLES = 5
CASES = (
    ("small_0_21", "ts:0", "ts:21", 5000),
    ("calendar_1970_2024", "1970-01-01 00:00:00", "2024-12-31 23:59:59", 1200),
    ("big_80_digits", "ts:0", "ts:" + "9" * 80, 90),
    ("big_250_digits", "ts:0", "ts:" + "9" * 250, 8),
)
CORE_RUNTIMES = ("php", "javascript", "python", "laravel")
CLI_RUNTIMES = ("php", "django", "laravel")
TIME = "/usr/bin/time"


def run(command: list[str], cwd: Path, env: dict[str, str]) -> dict:
    with tempfile.NamedTemporaryFile(prefix="fibonacci-time-", delete=False) as file:
        time_path = Path(file.name)
    try:
        completed = subprocess.run(
            [TIME, "-f", "%U %S %M %e", "-o", str(time_path), *command],
            cwd=cwd, env=env, capture_output=True, text=True,
            check=False, timeout=75,
        )
        if completed.returncode != 0:
            raise RuntimeError(
                f"Process {command[0]} exited {completed.returncode}: "
                f"{completed.stderr[-3000:]}; stdout={completed.stdout[-1000:]}"
            )
        fields = time_path.read_text().strip().split()
        if len(fields) != 4:
            raise ValueError(f"Unexpected /usr/bin/time output: {fields}")
        user, sys_time, rss, wall = map(float, fields)
        return {
            "payload": json.loads(completed.stdout),
            "rss_kib": int(rss),
            "cpu_ms": (user + sys_time) * 1000,
            "wall_ms": wall * 1000,
        }
    finally:
        time_path.unlink(missing_ok=True)


def result_key(payload: dict) -> tuple:
    return tuple(payload.get(key) for key in ("count", "first", "last", "sha256"))


def sample(name: str, commands: dict[str, list[str]], cwd: Path,
           env: dict[str, str], iterations: int | None = None) -> dict:
    reference = None
    for runtime, command in commands.items():
        data = run(command, cwd, env)["payload"]
        fingerprint = result_key(data) if iterations is not None else data["custom_range"]
        if reference is None:
            reference = fingerprint
        elif fingerprint != reference:
            raise AssertionError(f"Warmup results differ: {name} {runtime}")

    records: dict[str, list[dict]] = {r: [] for r in commands}
    rng = random.Random(1729)
    for _ in range(SAMPLES):
        order = list(commands)
        rng.shuffle(order)
        for runtime in order:
            observed = run(commands[runtime], cwd, env)
            payload = observed["payload"]
            fingerprint = result_key(payload) if iterations is not None else payload["custom_range"]
            if fingerprint != reference:
                raise AssertionError(f"Sample differs: {name} {runtime}")
            records[runtime].append({
                "cpu_ms": payload["cpu_ms"] / iterations if iterations is not None else observed["cpu_ms"],
                "peak_rss_kib": observed["rss_kib"],
                "wall_ms": observed["wall_ms"],
            })
    return {
        "case": name,
        "iterations_per_process": iterations,
        "samples_per_runtime": SAMPLES,
        "verification": dict(zip(("count", "first", "last", "sha256"), reference))
            if iterations is not None else reference,
        "runtimes": {
            runtime: {
                "cpu_ms": statistics.median([rec["cpu_ms"] for rec in values]),
                "peak_rss_kib": statistics.median([rec["peak_rss_kib"] for rec in values]),
                "wall_ms": statistics.median([rec["wall_ms"] for rec in values]),
                "samples": values,
            }
            for runtime, values in records.items()
        },
    }


def markdown(data: dict) -> str:
    lines = [
        "# Fibonacci: comparable CPU and peak RSS across four runtimes",
        "",
        f"Runner: {data['platform']}. Versions: {data['versions']}.",
        "",
        "## Hot core (CPU milliseconds per resolve; excludes framework startup)",
        "",
        "| Case | Runtime | CPU ms / operation | Peak RSS MiB |",
        "|---|---|---:|---:|",
    ]
    for entry in data["core"]:
        for runtime in CORE_RUNTIMES:
            result = entry["runtimes"][runtime]
            lines.append(
                f"| {entry['case']} | {runtime} | {result['cpu_ms']:.5f} "
                f"| {result['peak_rss_kib'] / 1024:.2f} |"
            )
    lines.extend([
        "",
        "## Full CLI (startup, argument parsing, serialization and shutdown included)",
        "",
        "| Case | Runtime | CPU ms | Peak RSS MiB | Wall ms |",
        "|---|---|---:|---:|---:|",
    ])
    for entry in data["cli"]:
        for runtime in CLI_RUNTIMES:
            result = entry["runtimes"][runtime]
            lines.append(
                f"| {entry['case']} | {runtime} | {result['cpu_ms']:.2f} "
                f"| {result['peak_rss_kib'] / 1024:.2f} | {result['wall_ms']:.2f} |"
            )
    lines.extend([
        "",
        "Five independent process samples per case and language, after one excluded process warmup.",
        "For the core, each process also excludes one in-process warmup from the reported CPU usage.",
        "Core times use process user+system CPU; CLI CPU uses GNU time for the whole process.",
        "Peak RSS includes the process and runtime, not just allocated Fibonacci arrays.",
        "All result sequences are verified by count, first and last value and SHA-256.",
        "JavaScript uses Node/V8, not a full browser. It does not have an equivalent CLI.",
        "Host sharing, runtime JIT and process startup influence timings; treat small differences cautiously.",
        f"Git commits: {data['commits']}.",
    ])
    return "\n".join(lines) + "\n"


def git_rev(path: Path) -> str:
    return subprocess.run(
        ["git", "-C", str(path), "rev-parse", "HEAD"],
        text=True, capture_output=True, check=True,
    ).stdout.strip()


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--original-dir", type=Path, required=True)
    parser.add_argument("--django-dir", type=Path, required=True)
    parser.add_argument("--laravel-dir", type=Path, default=Path(__file__).resolve().parents[1])
    parser.add_argument("--output-dir", type=Path, default=Path("benchmark-results"))
    args = parser.parse_args()

    original, django, laravel = (
        args.original_dir.resolve(), args.django_dir.resolve(), args.laravel_dir.resolve(),
    )
    for needed in (
        original / "PHP/fibonacci.php",
        original / "JAVASCRIPT/fibonacci.js",
        original / "benchmarks/php_core.php",
        original / "benchmarks/js_core.cjs",
        original / "benchmarks/python_core.py",
        django / "manage.py",
        laravel / "artisan",
        laravel / "benchmarks/laravel_core.php",
        laravel / "vendor/autoload.php",
    ):
        if not needed.is_file():
            raise FileNotFoundError(needed)
    for program in (TIME, "php", "node", "git"):
        if not shutil.which(program):
            raise RuntimeError(f"Missing executable: {program}")

    env = {
        **os.environ,
        "BENCH_ORIGINAL_ROOT": str(original),
        "BENCH_DJANGO_ROOT": str(django),
        "DJANGO_DEBUG": "1",
        "DJANGO_SECRET_KEY": "benchmark-development-only-key",
        "APP_ENV": "testing",
        "APP_DEBUG": "false",
        "APP_KEY": "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=",
        "SESSION_DRIVER": "array",
        "CACHE_STORE": "array",
    }
    data = {
        "timestamp_utc": datetime.now(timezone.utc).isoformat(),
        "platform": platform.platform(),
        "versions": {
            "php": subprocess.check_output(["php", "-r", "echo PHP_VERSION;"], text=True),
            "node": subprocess.check_output(["node", "--version"], text=True).strip(),
            "python": platform.python_version(),
        },
        "commits": {"original": git_rev(original), "django": git_rev(django), "laravel": git_rev(laravel)},
        "core": [],
        "cli": [],
    }
    for name, start, end, iterations in CASES:
        commands = {
            "php": ["php", str(original / "benchmarks/php_core.php"), str(iterations), start, end],
            "javascript": ["node", str(original / "benchmarks/js_core.cjs"), str(iterations), start, end],
            "python": [sys.executable, str(original / "benchmarks/python_core.py"), str(iterations), start, end],
            "laravel": ["php", str(laravel / "benchmarks/laravel_core.php"), str(iterations), start, end],
        }
        result = sample(name, commands, original, env, iterations)
        data["core"].append(result)
        print("Core " + name + ": " + ", ".join(
            f"{k}={result['runtimes'][k]['cpu_ms']:.4f}ms" for k in CORE_RUNTIMES), flush=True)

    for name, start, end, _ in (CASES[0], CASES[3]):
        commands = {
            "php": ["php", str(original / "PHP/fibonacci.php"), start, end],
            "django": [sys.executable, str(django / "manage.py"), "fibonacci", start, end],
            "laravel": ["php", str(laravel / "artisan"), "fibonacci", start, end],
        }
        result = sample(name, commands, original, env)
        data["cli"].append(result)
        print("CLI " + name + ": " + ", ".join(
            f"{k}={result['runtimes'][k]['cpu_ms']:.2f}ms" for k in CLI_RUNTIMES), flush=True)

    args.output_dir.mkdir(parents=True, exist_ok=True)
    (args.output_dir / "results.json").write_text(json.dumps(data, indent=2) + "\n", encoding="utf8")
    report = markdown(data)
    (args.output_dir / "summary.md").write_text(report, encoding="utf8")
    print(report)


if __name__ == "__main__":
    main()
