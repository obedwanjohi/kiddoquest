"""
expand_all_pp1_pp2_curriculum.py

Bulk-expands stub CSV files across PP1 and PP2 curriculum directories.
Only processes files that currently have fewer than 8 rows.

Usage:
    python expand_all_pp1_pp2_curriculum.py --test   # first 10 stub files only
    python expand_all_pp1_pp2_curriculum.py --all    # all stub files
"""
import sys
import csv
from pathlib import Path
from expand_curriculum_core import FIELDNAMES, write_csv, read_row_count
from expand_content_db import generate_rows

DIRS = [
    "database/csv_imports/pp1_cre",
    "database/csv_imports/pp1_english",
    "database/csv_imports/pp1_math",
    "database/csv_imports/pp2_cre",
    "database/csv_imports/pp2_english",
    "database/csv_imports/pp2_math",
]

MIN_ROWS = 8   # files with fewer than this are considered stubs


def get_lesson_title(path: Path) -> str:
    """Read the lesson_title from the first data row, or derive from filename."""
    try:
        with open(path, encoding="utf-8-sig", newline="") as fh:
            reader = csv.DictReader(fh)
            for row in reader:
                t = row.get("lesson_title", "").strip()
                if t:
                    return t
    except Exception:
        pass
    # derive from filename
    stem = path.stem
    import re
    stem = re.sub(r'^[^_]+_', '', stem, count=1)
    return stem.replace("_", " ").strip()


def collect_stubs(limit=None):
    stubs = []
    for d in DIRS:
        directory = Path(d)
        if not directory.is_dir():
            continue
        for csv_path in sorted(directory.glob("*.csv")):
            count = read_row_count(csv_path)
            if count < MIN_ROWS:
                stubs.append(csv_path)
    if limit:
        stubs = stubs[:limit]
    return stubs


def expand_file(path: Path) -> tuple[int, str]:
    title = get_lesson_title(path)
    rows = generate_rows(path, title)
    if not rows:
        return 0, f"SKIP (no generator matched): {path.name}"
    # Set lesson_title on all rows
    for r in rows:
        if not r.get("lesson_title"):
            r["lesson_title"] = title
    write_csv(path, rows)
    return len(rows), ""


def main():
    mode = "--test"
    if len(sys.argv) > 1:
        mode = sys.argv[1]

    limit = 10 if mode == "--test" else None
    stubs = collect_stubs(limit=limit)

    if not stubs:
        print("No stub files found (all files already have 8+ rows).")
        return

    print(f"Found {len(stubs)} stub file(s) to expand.")
    total_rows = 0
    skipped = 0
    for path in stubs:
        n, msg = expand_file(path)
        if msg:
            print(f"  {msg}")
            skipped += 1
        else:
            print(f"  {path.name}: {n} rows written")
            total_rows += n

    print(f"\nSummary:")
    print(f"  Files expanded : {len(stubs) - skipped}")
    print(f"  Files skipped  : {skipped}")
    print(f"  Total rows written: {total_rows}")


if __name__ == "__main__":
    main()
