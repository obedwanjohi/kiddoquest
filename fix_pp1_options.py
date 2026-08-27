"""
fix_pp1_options.py

Remediates PP1 Math, English, and CRE CSV files in-place:
  - Clears "ops" from count_emoji_or_image on matching rows
  - Clears stray garbage text from option_4 on matching rows
  - Extracts trailing emoji from multi-word+emoji option/pair cells
  - Extracts leading digit from "N Word 🐾" cells
  - Extracts trailing noun from "Adj Noun" (2-word, no emoji) cells
  - NEVER modifies speak_repeat rows
"""

import csv
import re
import sys
import unicodedata
from pathlib import Path
from glob import glob

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

OPTION_COLS = ["option_1", "option_2", "option_3", "option_4"]
PAIR_COLS = [
    "pair_1_left", "pair_1_right",
    "pair_2_left", "pair_2_right",
    "pair_3_left", "pair_3_right",
]

# Words that prefix a meaningful noun — strip them to get the noun
DESCRIPTOR_WORDS = {
    "RED", "BLUE", "GREEN", "YELLOW", "GOLDEN", "GOLD", "WHITE", "BLACK",
    "PURPLE", "ORANGE", "PINK", "BROWN", "BRIGHT", "DARK", "COLORFUL",
    "BIG", "SMALL", "TALL", "SHORT", "TINY", "GIANT", "GREAT", "LITTLE",
    "HOLY", "CUTE", "TINY", "HAPPY", "BABY",
}

TROPHY_KEYWORDS = {"TROPHY", "DIPLOMA", "BADGE", "MEDAL", "CHAMPION"}

# Known garbage values that appear erroneously in option_4 on matching rows
GARBAGE_OPTION4 = {"rendering", "contract", "ops", "null", "none", "undefined"}

# ---------------------------------------------------------------------------
# Emoji detection (single-codepoint ranges)
# ---------------------------------------------------------------------------

_EMOJI_RE = re.compile(
    r'[\U0001F600-\U0001F64F'
    r'\U0001F300-\U0001F5FF'
    r'\U0001F680-\U0001F6FF'
    r'\U0001F700-\U0001F77F'
    r'\U0001F780-\U0001F7FF'
    r'\U0001F800-\U0001F8FF'
    r'\U0001F900-\U0001F9FF'
    r'\U0001FA00-\U0001FA6F'
    r'\U0001FA70-\U0001FAFF'
    r'\u2600-\u26FF'
    r'\u2700-\u27BF'
    r'\U0001F1E0-\U0001F1FF'
    r'][\uFE0F\u20D0-\u20FF]*'
    r'(?:\u200D[\U0001F600-\U0001F64F\U0001F300-\U0001F5FF\U0001F680-\U0001F6FF'
    r'\U0001F900-\U0001F9FF\U0001FA00-\U0001FAFF\u2600-\u27BF][\uFE0F\u20D0-\u20FF]*)*',
    re.UNICODE,
)


def _trailing_emoji(value: str) -> str | None:
    """Return the trailing emoji cluster if present at end of string."""
    stripped = value.strip()
    matches = list(_EMOJI_RE.finditer(stripped))
    if matches and matches[-1].end() == len(stripped):
        return matches[-1].group(0)
    return None


def _strip_emojis(value: str) -> str:
    """Remove all emoji from a string and strip whitespace."""
    return _EMOJI_RE.sub("", value).strip()


# ---------------------------------------------------------------------------
# Cell transformation rules
# ---------------------------------------------------------------------------

def is_single_token(value: str) -> bool:
    s = value.strip()
    return len(s) > 0 and " " not in s


def _fix_trophy(value: str) -> str | None:
    words = {w.upper().strip(".,!?'") for w in value.split()}
    if words & TROPHY_KEYWORDS:
        return "🏆"
    return None


def _fix_letter_x(value: str) -> str | None:
    """'Letter X' → 'X'."""
    m = re.match(r'^[Ll]etter\s+([A-Za-z])$', value.strip())
    if m:
        return m.group(1).upper()
    return None


def _apply_cell_rules(value: str) -> str | None:
    """
    Return simplified value or None if no change needed.
    Rules (in priority order):
      1. Trophy keyword → 🏆
      2. "Letter X" → "X"
      3. "N Word(s) [emoji]" → "N"  (leading digit)
      4. Trailing emoji on multi-word → emoji only
      5. "Adj Noun [emoji]" (2 words no emoji, first is descriptor) → "Noun" uppercased
    """
    if not value:
        return None

    # Strip and check if already single token (after removing trailing emoji check)
    stripped = value.strip()

    # Rule 1: trophy
    result = _fix_trophy(stripped)
    if result:
        return result

    # Rule 2: Letter X
    result = _fix_letter_x(stripped)
    if result:
        return result

    # If already single token, nothing to do
    if is_single_token(stripped):
        return None

    # Remove emojis to get the text part
    text_part = _strip_emojis(stripped).strip()
    emoji_part = _trailing_emoji(stripped)

    # Rule 3: leading digit followed by space → extract digit
    m = re.match(r'^(\d+)\s', text_part)
    if m:
        return m.group(1)

    # Rule 4: trailing emoji on multi-word value → emoji only
    if emoji_part and " " in text_part:
        return emoji_part

    # Rule 5: two-word "Adj Noun" (no emoji, descriptor first) → noun uppercased
    if not emoji_part:
        parts = text_part.split()
        if len(parts) == 2 and parts[0].upper() in DESCRIPTOR_WORDS:
            return parts[1].upper()

    return None


# ---------------------------------------------------------------------------
# Row-level fixers
# ---------------------------------------------------------------------------

def fix_option_cells(row: dict, qtype: str) -> tuple[dict, int, list[str]]:
    """Transform option_1..4. Skip speak_repeat and matching."""
    if qtype in ("speak_repeat", "matching"):
        return row, 0, []

    modified = 0
    warnings = []
    for col in OPTION_COLS:
        original = row.get(col, "")
        new_val = _apply_cell_rules(original)
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1
    return row, modified, warnings


def fix_matching_option4(row: dict, qtype: str) -> tuple[dict, int]:
    """
    On matching rows, option_4 is unused but sometimes contains stray garbage
    text (e.g. 'rendering', 'contract'). Clear it.
    """
    if qtype != "matching":
        return row, 0
    val = row.get("option_4", "").strip().lower()
    if val in GARBAGE_OPTION4 or (val and val not in ("", "0", "1")):
        # Only clear known garbage strings — don't touch legitimate values
        if row.get("option_4", "").strip().lower() in GARBAGE_OPTION4:
            row["option_4"] = ""
            return row, 1
    return row, 0


def fix_ops_cell(row: dict) -> tuple[dict, int]:
    """Clear 'ops' from count_emoji_or_image on matching rows."""
    col = "count_emoji_or_image"
    if row.get(col, "") == "ops":
        row[col] = ""
        return row, 1
    return row, 0


def fix_pair_cells(row: dict) -> tuple[dict, int, list[str]]:
    """Simplify pair label cells on matching rows."""
    modified = 0
    warnings = []
    for col in PAIR_COLS:
        original = row.get(col, "")
        new_val = _apply_cell_rules(original)
        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1
    return row, modified, warnings


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
            row, n = fix_ops_cell(row)
            total_modified += n

            row, n = fix_matching_option4(row, qtype)
            total_modified += n

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
        print("Usage: python fix_pp1_options.py <csv_directory> [<csv_directory2> ...]")
        sys.exit(1)

    all_dirs = sys.argv[1:]
    total_files = 0
    total_cells = 0
    total_warnings: list[str] = []

    for dir_arg in all_dirs:
        directory = Path(dir_arg)
        if not directory.is_dir():
            print(f"Error: directory not found: {directory}")
            continue

        csv_files = sorted(directory.glob("*.csv"))
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
