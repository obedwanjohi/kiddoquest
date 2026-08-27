"""
fix_pg_cre_options.py

Remediates Play Group CRE CSV files in-place:
  - Simplifies "Adjective Noun" option cells to the noun (single token)
  - Simplifies "Adjective Noun" pair label cells the same way
  - Replaces award/trophy options with 🏆
  - NEVER modifies speak_repeat rows
"""

import csv
import re
import sys
import unicodedata
from pathlib import Path

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

# Descriptive adjectives that prefix a meaningful noun in CRE options.
# When a 2-word value starts with one of these, we extract the trailing noun.
DESCRIPTOR_WORDS = {
    # colours
    "RED", "BLUE", "GREEN", "YELLOW", "GOLDEN", "GOLD", "WHITE", "BLACK",
    "PURPLE", "ORANGE", "PINK", "BROWN", "BRIGHT", "DARK", "COLORFUL",
    "COLOURFUL", "SHINY",
    # size / quality
    "BIG", "SMALL", "TALL", "SHORT", "TINY", "GIANT", "GREAT", "GRAND",
    "LITTLE", "HOLY", "SPECIAL",
    # night/day
    "NIGHT", "CALM", "ROUGH",
    # CRE-specific
    "WOODEN", "PRETTY", "HAPPY", "SAD", "ANGRY",
}

# Award/trophy keyword → replacement
TROPHY_KEYWORDS = {"TROPHY", "DIPLOMA", "BADGE", "MEDAL", "CHAMPION"}

# Columns
OPTION_COLS = ["option_1", "option_2", "option_3", "option_4"]
PAIR_COLS = [
    "pair_1_left", "pair_1_right",
    "pair_2_left", "pair_2_right",
    "pair_3_left", "pair_3_right",
]

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def is_single_token(value: str) -> bool:
    """True if stripped value has no spaces."""
    s = value.strip()
    return len(s) > 0 and " " not in s


def _fix_trophy(value: str) -> str | None:
    """If any word is a trophy/award keyword → 🏆."""
    words = {w.upper().strip(".,!?'") for w in value.split()}
    if words & TROPHY_KEYWORDS:
        return "🏆"
    return None


def _fix_descriptor_noun(value: str) -> str | None:
    """
    If value is exactly 2 words and the first word is a known descriptor,
    return the second word uppercased (the noun).

    e.g. "Bright Sun" → "SUN", "Green Tree" → "TREE", "Gold Cross" → "CROSS"

    Does NOT fire for 3+ word values — those are intentional phrases.
    """
    parts = value.strip().split()
    if len(parts) != 2:
        return None
    first, second = parts
    if first.upper() in DESCRIPTOR_WORDS:
        return second.upper()
    return None


def _fix_the_a_prefix(value: str) -> str | None:
    """
    Strip a leading article ("The", "A", "An") from a 2-word value and
    return the noun uppercased.

    e.g. "The Wind" → "WIND", "A Car" → "CAR", "An Angel" → "ANGEL"
    """
    parts = value.strip().split()
    if len(parts) != 2:
        return None
    if parts[0].upper() in ("THE", "A", "AN"):
        return parts[1].upper()
    return None


# ---------------------------------------------------------------------------
# Cell fixers
# ---------------------------------------------------------------------------

def _apply_rules(value: str) -> tuple[str | None, bool]:
    """
    Apply all rules to a single cell value.
    Returns (new_value, should_warn).
    new_value is None if no rule matched (leave unchanged).
    should_warn is True only for cells that look like they *should* be single
    tokens but aren't — i.e. the value is exactly 2 words and neither word is
    a known descriptor/article. 3+ word values are intentional phrases and
    never warned about.
    """
    if not value or is_single_token(value):
        return None, False

    # Rule 1: trophy/award
    result = _fix_trophy(value)
    if result:
        return result, False

    # Rule 2: descriptor + noun (exactly 2 words)
    result = _fix_descriptor_noun(value)
    if result:
        return result, False

    # Rule 3: article + noun ("The Wind", "A Car")
    result = _fix_the_a_prefix(value)
    if result:
        return result, False

    # 3+ word values are intentional phrases — leave silently
    parts = value.strip().split()
    if len(parts) >= 3:
        return None, False

    # Exactly 2 words, no rule matched — genuinely unhandled
    return None, True


def fix_option_cells(row: dict, qtype: str) -> tuple[dict, int, list[str]]:
    """Apply option simplification rules. skip speak_repeat and matching."""
    if qtype in ("speak_repeat", "matching"):
        return row, 0, []

    modified = 0
    warnings = []

    for col in OPTION_COLS:
        original = row.get(col, "")
        new_val, warn = _apply_rules(original)
        if warn:
            warnings.append(f"{qtype} {col}: unhandled 2-word value: {original!r}")
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1

    return row, modified, warnings


def fix_pair_cells(row: dict) -> tuple[dict, int, list[str]]:
    """Apply pair label simplification for matching rows."""
    modified = 0
    warnings = []

    for col in PAIR_COLS:
        original = row.get(col, "")
        new_val, warn = _apply_rules(original)
        if warn:
            warnings.append(f"pair {col}: unhandled 2-word value: {original!r}")
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1

    return row, modified, warnings


# ---------------------------------------------------------------------------
# File processor
# ---------------------------------------------------------------------------

def process_file(path: Path) -> tuple[int, int, list[str]]:
    """Read, transform, write back one CSV file in-place."""
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
            row, n, w = fix_pair_cells(row)
            total_modified += n
            all_warnings.extend(w)

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
        print("Usage: python fix_pg_cre_options.py <csv_directory>")
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
