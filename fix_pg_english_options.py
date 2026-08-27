"""
fix_pg_english_options.py

Remediates Play Group English CSV files in-place:
  - "Letter X" → "X" in option cells and pair cells (multiple_choice & matching)
  - Award/trophy options → 🏆
  - NEVER modifies speak_repeat rows
"""

import csv
import re
import sys
from pathlib import Path

# Columns
OPTION_COLS = ["option_1", "option_2", "option_3", "option_4"]
PAIR_COLS = [
    "pair_1_left", "pair_1_right",
    "pair_2_left", "pair_2_right",
    "pair_3_left", "pair_3_right",
]

TROPHY_KEYWORDS = {"TROPHY", "DIPLOMA", "BADGE", "MEDAL", "CHAMPION"}

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def is_single_token(value: str) -> bool:
    return len(value.strip()) > 0 and " " not in value.strip()


def _fix_letter_x(value: str) -> str | None:
    """'Letter X' → 'X' (single uppercase letter only)."""
    m = re.match(r'^[Ll]etter\s+([A-Za-z])$', value.strip())
    if m:
        return m.group(1).upper()
    return None


def _fix_trophy(value: str) -> str | None:
    """Any value containing a trophy/award keyword → 🏆."""
    words = {w.upper().strip(".,!?'") for w in value.split()}
    if words & TROPHY_KEYWORDS:
        return "🏆"
    return None


def _apply_option_rules(value: str) -> str | None:
    """Return new value or None if no change needed."""
    if not value or is_single_token(value):
        return None
    result = _fix_trophy(value)
    if result:
        return result
    result = _fix_letter_x(value)
    if result:
        return result
    return None


def _apply_pair_rules(value: str) -> str | None:
    """For pair cells: only fix 'Letter X' → 'X'. Leave everything else."""
    if not value or is_single_token(value):
        return None
    result = _fix_letter_x(value)
    if result:
        return result
    return None


# ---------------------------------------------------------------------------
# Row-level fixers
# ---------------------------------------------------------------------------

def fix_option_cells(row: dict, qtype: str) -> tuple[dict, int, list[str]]:
    if qtype in ("speak_repeat", "matching"):
        return row, 0, []

    modified = 0
    warnings = []
    for col in OPTION_COLS:
        original = row.get(col, "")
        new_val = _apply_option_rules(original)
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1
    return row, modified, warnings


def fix_pair_cells(row: dict) -> tuple[dict, int, list[str]]:
    modified = 0
    for col in PAIR_COLS:
        original = row.get(col, "")
        new_val = _apply_pair_rules(original)
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1
    return row, modified, []


# ---------------------------------------------------------------------------
# File processor
# ---------------------------------------------------------------------------

def process_file(path: Path) -> tuple[int, int, list[str]]:
    try:
        with open(path, encoding="utf-8-sig", newline="") as fh:
            reader = csv.DictReader(fh)
            if reader.fieldnames is None:
                return 0, 0, [f"{path}: no header row"]
            fieldnames = [f for f in reader.fieldnames if f is not None]
            rows = []
            for row in reader:
                row.pop(None, None)
                rows.append(row)
    except Exception as exc:
        return 0, 0, [f"{path}: read error — {exc}"]

    total_modified = 0
    all_warnings: list[str] = []

    updated_rows = []
    for row in rows:
        qtype = row.get("question_type", "")
        row, n, w = fix_option_cells(row, qtype)
        total_modified += n
        all_warnings.extend(w)
        if qtype == "matching":
            row, n, _ = fix_pair_cells(row)
            total_modified += n
        updated_rows.append(row)

    try:
        with open(path, "w", encoding="utf-8", newline="") as fh:
            writer = csv.DictWriter(fh, fieldnames=fieldnames, extrasaction="ignore")
            writer.writeheader()
            writer.writerows(updated_rows)
    except Exception as exc:
        return len(rows), total_modified, all_warnings + [f"{path}: write error — {exc}"]

    return len(rows), total_modified, all_warnings


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------

def main() -> None:
    if len(sys.argv) < 2:
        print("Usage: python fix_pg_english_options.py <csv_directory>")
        sys.exit(1)

    directory = Path(sys.argv[1])
    if not directory.is_dir():
        print(f"Error: directory not found: {directory}")
        sys.exit(1)

    csv_files = sorted(directory.glob("*.csv"))
    if not csv_files:
        print(f"No .csv files found in {directory}")
        return

    total_files = 0
    total_cells = 0
    total_warnings: list[str] = []

    for csv_path in csv_files:
        rows, cells, warnings = process_file(csv_path)
        total_files += 1
        total_cells += cells
        if warnings:
            total_warnings.extend(warnings)
            print(f"  {csv_path.name}: {rows} rows, {cells} cells modified, "
                  f"{len(warnings)} warnings")

    print(f"\nSummary:")
    print(f"  Files processed : {total_files}")
    print(f"  Cells modified  : {total_cells}")
    print(f"  Warnings        : {len(total_warnings)}")

    if total_warnings:
        print("\nWarnings:")
        for w in total_warnings:
            print(f"  {w}")


if __name__ == "__main__":
    main()
