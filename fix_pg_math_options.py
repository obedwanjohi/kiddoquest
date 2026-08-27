"""
fix_pg_math_options.py

Remediates Play Group Math CSV files in-place:
  - Simplifies multi-word option cells (count_objects, multiple_choice, complete_pattern)
  - Clears "ops" garbage from count_emoji_or_image on matching rows
  - Simplifies matching pair label cells
"""

import csv
import re
import sys
import unicodedata
from pathlib import Path

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

CONCEPT_WORDS = {
    "BIG", "SMALL", "TALL", "SHORT", "HEAVY", "LIGHT",
    "ABOVE", "BELOW", "INSIDE", "OUTSIDE", "LEFT", "RIGHT",
    "NEAR", "FAR", "LONG", "SAME", "DIFFERENT",
    "ROUND", "SQUARE", "GIANT", "TINY", "HIGH", "LOW",
    "WIDE", "NARROW",
    # Category 6 — capacity labels
    "HALF", "FULL", "EMPTY",
}

# Category 2 — colour words (leading colour → uppercased colour word)
COLOR_WORDS = {
    "RED", "BLUE", "YELLOW", "GREEN", "PURPLE",
    "ORANGE", "PINK", "WHITE", "BLACK", "BROWN",
}

# Category 3 — size words (already in CONCEPT_WORDS; listed here for clarity)
# BIG, SMALL, GIANT, TINY, TALL, SHORT are all in CONCEPT_WORDS

# Category 4 — container/category leading words
CATEGORY_WORDS = {
    "FRUIT", "VEGETABLE", "CIRCLE", "SQUARE", "TRIANGLE",
}

# Category 1 — award / trophy keywords → replacement token
TROPHY_KEYWORDS = {"TROPHY", "CHEST"}

EMOJI_LIST = [
    "🦕", "🦖", "🐘", "🦒", "🦁", "🐯", "🐻", "🐼", "🐨", "🐸",
    "🐵", "🐔", "🐧", "🐦", "🦆", "🦅", "🦉", "🦇", "🐺", "🐗",
    "🐴", "🦄", "🐝", "🐛", "🦋", "🐌", "🐞", "🐜", "🦟", "🦗",
    "🐢", "🐍", "🦎", "🦕", "🦖", "🦑", "🦐", "🦞", "🦀", "🐡",
    "🐠", "🐟", "🐬", "🐳", "🐋", "🦈", "🐊", "🐅", "🐆", "🦓",
    "🦍", "🦧", "🦣", "🐪", "🐫", "🦘", "🦬", "🐃", "🐂", "🐄",
    "🐎", "🐖", "🐏", "🐑", "🦙", "🐐", "🦌", "🐕", "🐩", "🦮",
    "🐈", "🐓", "🦃", "🦤", "🦚", "🦜", "🦢", "🦩", "🕊", "🐇",
    "🦝", "🦨", "🦡", "🦫", "🦦", "🦥", "🐁", "🐀", "🐿", "🦔",
    "🐾", "🐉", "🌵", "🌲", "🌳", "🌴", "🌱", "🌿", "🍀", "🎋",
    "🍁", "🍂", "🍃", "🍇", "🍈", "🍉", "🍊", "🍋", "🍌", "🍍",
    "🥭", "🍎", "🍏", "🍐", "🍑", "🍒", "🍓", "🫐", "🥝", "🍅",
    "🥥", "🥑", "🍆", "🥔", "🥕", "🌽", "🌶", "🫑", "🥒", "🥬",
    "🥦", "🧄", "🧅", "🍄", "🥜", "🌰", "🍞", "🥐", "🥖", "🫓",
    "🥨", "🧀", "🥚", "🍳", "🧈", "🥞", "🧇", "🥓", "🥩", "🍗",
    "🍖", "🌭", "🍔", "🍟", "🍕", "🫔", "🌮", "🌯", "🫙", "🥗",
    "🥘", "🫕", "🍝", "🍜", "🍲", "🍛", "🍣", "🍱", "🥟", "🦪",
    "🍤", "🍙", "🍚", "🍘", "🍥", "🥮", "🍢", "🧆", "🥚", "🍡",
    "🍧", "🍨", "🍦", "🥧", "🧁", "🍰", "🎂", "🍮", "🍭", "🍬",
    "🍫", "🍿", "🍩", "🍪", "🌰", "🥜", "🍯", "🧃", "🥤", "🧋",
    "☕", "🍵", "🫖", "🍶", "🍺", "🍻", "🥂", "🍷", "🥃", "🍸",
    "🍹", "🧉", "🍾", "🧊", "🥄", "🍴", "🍽", "🥢", "🧂",
    "⚽", "🏀", "🏈", "⚾", "🥎", "🎾", "🏐", "🏉", "🥏", "🎱",
    "🪀", "🏓", "🏸", "🏒", "🏑", "🥍", "🏏", "🪃", "🥅", "⛳",
    "🪁", "🏹", "🎣", "🤿", "🥊", "🥋", "🎽", "🛹", "🛼", "🛷",
    "⛸", "🥌", "🎿", "⛷", "🏂", "🪂", "🏋", "🤼", "🤸", "⛹",
    "🤺", "🏇", "🧘", "🏄", "🏊", "🤽", "🚣", "🧗", "🚵", "🚴",
    "🏆", "🥇", "🥈", "🥉", "🏅", "🎖", "🎗", "🎫", "🎟", "🎪",
    "🎭", "🎨", "🎬", "🎤", "🎧", "🎼", "🎵", "🎶", "🎹", "🥁",
    "🪘", "🎷", "🎺", "🎸", "🪕", "🎻", "🎲", "♟", "🎯", "🎳",
    "🎮", "🎰", "🧩",
    "🌍", "🌎", "🌏", "🌐", "🗺", "🧭", "🏔", "⛰", "🌋", "🗻",
    "🏕", "🏖", "🏜", "🏝", "🏞", "🏟", "🏛", "🏗", "🧱", "🏘",
    "🏚", "🏠", "🏡", "🏢", "🏣", "🏤", "🏥", "🏦", "🏨", "🏩",
    "🏪", "🏫", "🏬", "🏭", "🏯", "🏰", "💒", "🗼", "🗽", "⛪",
    "🕌", "🛕", "🕍", "⛩", "🕋", "⛲", "⛺", "🌁", "🌃", "🏙",
    "🌄", "🌅", "🌆", "🌇", "🌉", "♨", "🎠", "🛝", "🎡", "🎢",
    "💈", "🎪", "🚂", "🚃", "🚄", "🚅", "🚆", "🚇", "🚈", "🚉",
    "🚊", "🚝", "🚞", "🚋", "🚌", "🚍", "🚎", "🚐", "🚑", "🚒",
    "🚓", "🚔", "🚕", "🚖", "🚗", "🚘", "🚙", "🛻", "🚚", "🚛",
    "🚜", "🏎", "🏍", "🛵", "🦽", "🦼", "🛺", "🚲", "🛴", "🛹",
    "🛼", "🚏", "🛣", "🛤", "⛽", "🛞", "🚨", "🚥", "🚦", "🛑",
    "🚧", "⚓", "🛟", "⛵", "🛶", "🚤", "🛳", "⛴", "🛥", "🚢",
    "✈", "🛩", "🛫", "🛬", "🛂", "🛃", "🛄", "🛅", "💺", "🚁",
    "🚟", "🚠", "🚡", "🛰", "🚀", "🛸", "🌠", "🌌", "⛱", "🎆",
    "🎇", "🎑", "💥", "🎃", "🎄", "🎆", "🎇", "✨", "🎉", "🎊",
    "🎋", "🎍", "🎎", "🎏", "🎐", "🧧", "🎀", "🎁", "🔮", "🪄",
    "🎭", "🩰", "🎨", "🖼", "🎰", "🚪", "🛋", "🪑", "🚽", "🪠",
    "🚿", "🛁", "🪤", "🪒", "🧴", "🧷", "🧹", "🧺", "🧻", "🪣",
    "🧼", "🫧", "🪥", "🧽", "🧯", "🛒", "🚪", "🪞", "🪟", "🛏",
    "🛋", "🚿", "🛁", "🧴", "🧷",
    "❤", "🧡", "💛", "💚", "💙", "💜", "🖤", "🤍", "🤎", "💔",
    "💯", "💢", "💬", "💭", "💤", "💦", "💨", "🕳", "💣", "💬",
    "👋", "🤚", "🖐", "✋", "🖖", "👌", "🤌", "🤏", "✌", "🤞",
    "🤟", "🤘", "🤙", "👈", "👉", "👆", "🖕", "👇", "☝", "🫵",
    "👍", "👎", "✊", "👊", "🤛", "🤜", "👏", "🙌", "🫶", "👐",
    "🤲", "🤝", "🙏", "✍", "💅", "🤳", "💪", "🦾", "🦿", "🦵",
    "🦶", "👂", "🦻", "👃", "🧠", "🫀", "🫁", "🦷", "🦴", "👀",
    "👁", "👅", "👄", "🫦", "👶", "🧒", "👦", "👧", "🧑", "👱",
    "🌸", "🌹", "🥀", "🌺", "🌻", "🌼", "💐", "🌷",
    "⭐", "🌟", "✨", "💫", "⚡", "☄", "🌈", "☀", "🌤", "⛅",
    "🌥", "☁", "🌦", "🌧", "⛈", "🌩", "🌨", "❄", "☃", "⛄",
    "🌬", "💨", "🌀", "🌊", "🌈",
    "🔴", "🟠", "🟡", "🟢", "🔵", "🟣", "⚫", "⚪", "🟤",
    "🔶", "🔷", "🔸", "🔹", "🔺", "🔻", "💠", "🔘", "🔳", "🔲",
    "🏁", "🚩", "🎌", "🏴", "🏳",
    "1️⃣", "2️⃣", "3️⃣", "4️⃣", "5️⃣", "6️⃣", "7️⃣", "8️⃣", "9️⃣", "0️⃣",
    "🔟", "#️⃣", "*️⃣",
    "🍑", "🍒", "🍓",
]

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def is_single_token(value: str) -> bool:
    """
    Returns True if the stripped value is a single token:
      (a) a single integer/digit string (no spaces),
      (b) a single emoji character (no spaces), or
      (c) a single word with no spaces.

    Essentially: True iff stripped value contains no space characters.

    Requirements: 1.2, 2.4, 3.2, 5.3
    """
    stripped = value.strip()
    return " " not in stripped and len(stripped) > 0


# ---------------------------------------------------------------------------
# Extractor functions
# ---------------------------------------------------------------------------

def extract_leading_digit(value: str) -> str | None:
    """
    Returns the leading digit string if value starts with one or more digits
    followed by a space character (e.g. "1 Footprint" -> "1").

    Returns None if:
      - the value is already a single token (no spaces), or
      - the value does not start with digits followed by a space.

    Requirements: 1.1, 5.2
    """
    if is_single_token(value):
        return None
    m = re.match(r'^(\d+)\s', value)
    if m:
        return m.group(1)
    return None


def extract_trailing_digit(value: str) -> str | None:
    """
    Returns the trailing digit string if value ends with a space followed by
    one or more digits (e.g. "Tractor 3" -> "3").

    Returns None if already a single token or no trailing digit found.
    """
    if is_single_token(value):
        return None
    m = re.search(r'\s(\d+)$', value)
    if m:
        return m.group(1)
    return None


def _is_emoji_char(char: str) -> bool:
    """Returns True if char is a Unicode emoji character."""
    if not char:
        return False
    cp = ord(char)
    # Common emoji Unicode ranges
    emoji_ranges = [
        (0x1F600, 0x1F64F),  # Emoticons
        (0x1F300, 0x1F5FF),  # Misc Symbols and Pictographs
        (0x1F680, 0x1F6FF),  # Transport and Map
        (0x1F700, 0x1F77F),  # Alchemical Symbols
        (0x1F780, 0x1F7FF),  # Geometric Shapes Extended
        (0x1F800, 0x1F8FF),  # Supplemental Arrows-C
        (0x1F900, 0x1F9FF),  # Supplemental Symbols and Pictographs
        (0x1FA00, 0x1FA6F),  # Chess Symbols
        (0x1FA70, 0x1FAFF),  # Symbols and Pictographs Extended-A
        (0x2600,  0x26FF),   # Misc symbols
        (0x2700,  0x27BF),   # Dingbats
        (0xFE00,  0xFE0F),   # Variation Selectors
        (0x1F1E0, 0x1F1FF),  # Flags
        (0x231A,  0x231B),   # Watch, hourglass
        (0x23E9,  0x23F3),   # Various clock/timer
        (0x23F8,  0x23FA),   # Pause/stop buttons
        (0x25AA,  0x25AB),   # Small squares
        (0x25B6,  0x25B6),   # Play button
        (0x25C0,  0x25C0),   # Reverse button
        (0x25FB,  0x25FE),   # Medium squares
        (0x2614,  0x2615),   # Umbrella with rain, hot beverage
        (0x2648,  0x2653),   # Zodiac signs
        (0x267F,  0x267F),   # Wheelchair
        (0x2693,  0x2693),   # Anchor
        (0x26A1,  0x26A1),   # Lightning
        (0x26AA,  0x26AB),   # Circles
        (0x26BD,  0x26BE),   # Soccer/Baseball
        (0x26C4,  0x26C5),   # Snowman/Sun
        (0x26CE,  0x26CE),   # Ophiuchus
        (0x26D4,  0x26D4),   # No entry
        (0x26EA,  0x26EA),   # Church
        (0x26F2,  0x26F3),   # Fountain/Golf
        (0x26F5,  0x26F5),   # Sailboat
        (0x26FA,  0x26FA),   # Tent
        (0x26FD,  0x26FD),   # Fuel pump
        (0x2702,  0x2702),   # Scissors
        (0x2705,  0x2705),   # Check mark
        (0x2708,  0x270D),   # Airplane..writing hand
        (0x270F,  0x270F),   # Pencil
        (0x2712,  0x2712),   # Black nib
        (0x2714,  0x2714),   # Check mark
        (0x2716,  0x2716),   # X mark
        (0x271D,  0x271D),   # Latin cross
        (0x2721,  0x2721),   # Star of David
        (0x2728,  0x2728),   # Sparkles
        (0x2733,  0x2734),   # Spoked asterisks
        (0x2744,  0x2744),   # Snowflake
        (0x2747,  0x2747),   # Sparkle
        (0x274C,  0x274C),   # Cross mark
        (0x274E,  0x274E),   # Cross mark
        (0x2753,  0x2755),   # Question marks
        (0x2757,  0x2757),   # Exclamation mark
        (0x2763,  0x2764),   # Heart
        (0x2795,  0x2797),   # Plus/minus/division
        (0x27A1,  0x27A1),   # Arrow
        (0x27B0,  0x27B0),   # Curly loop
        (0x27BF,  0x27BF),   # Double curly loop
        (0x2934,  0x2935),   # Arrows
        (0x2B05,  0x2B07),   # Arrows
        (0x2B1B,  0x2B1C),   # Squares
        (0x2B50,  0x2B50),   # Star
        (0x2B55,  0x2B55),   # Circle
        (0x3030,  0x3030),   # Wavy dash
        (0x303D,  0x303D),   # Part alternation mark
        (0x3297,  0x3297),   # Circled ideograph congratulation
        (0x3299,  0x3299),   # Circled ideograph secret
    ]
    cat = unicodedata.category(char)
    if cat in ('So', 'Sm'):
        return True
    for lo, hi in emoji_ranges:
        if lo <= cp <= hi:
            return True
    return False


def extract_trailing_emoji(value: str) -> str | None:
    """
    Returns the final character of the string if it is a single emoji,
    otherwise None.

    Returns None if the value is already a single token (no spaces).

    Uses unicodedata category detection plus emoji Unicode range checks.
    Multi-codepoint emoji sequences (e.g. keycap sequences) are handled by
    checking the last grapheme cluster.

    Requirements: 2.2, 3.1, 5.1
    """
    if is_single_token(value):
        return None
    stripped = value.strip()
    if not stripped:
        return None

    # Use regex to find the last emoji (including ZWJ sequences, variation selectors, etc.)
    # Pattern matches emoji sequences including modifiers and ZWJ sequences
    emoji_pattern = re.compile(
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
        r'\uFE00-\uFE0F'
        r'\U0001F1E0-\U0001F1FF'
        r'\u231A-\u231B'
        r'\u23E9-\u23F3'
        r'\u25AA-\u25AB'
        r'\u25B6\u25C0'
        r'\u25FB-\u25FE'
        r'\u2614-\u2615'
        r'\u2648-\u2653'
        r'\u267F\u2693\u26A1'
        r'\u26AA-\u26AB'
        r'\u26BD-\u26BE'
        r'\u26C4-\u26C5'
        r'\u2702\u2705'
        r'\u2708-\u270D'
        r'\u270F\u2712\u2714\u2716'
        r'\u2728\u2733-\u2734\u2744\u2747'
        r'\u274C\u274E\u2753-\u2755\u2757'
        r'\u2763-\u2764\u2795-\u2797'
        r'\u27A1\u27B0\u27BF'
        r'\u2934-\u2935\u2B05-\u2B07'
        r'\u2B1B-\u2B1C\u2B50\u2B55'
        r'\u3030\u303D\u3297\u3299'
        r'][\uFE0F\u20D0-\u20FF]*'
        r'(?:\u200D[\U0001F600-\U0001F64F\U0001F300-\U0001F5FF\U0001F680-\U0001F6FF'
        r'\U0001F900-\U0001F9FF\U0001FA00-\U0001FAFF\u2600-\u27BF][\uFE0F\u20D0-\u20FF]*)*',
        re.UNICODE
    )

    matches = list(emoji_pattern.finditer(stripped))
    if not matches:
        return None

    last_match = matches[-1]
    # The emoji must be at the end of the string
    if last_match.end() == len(stripped):
        return last_match.group(0)
    return None


def extract_concept_word(value: str) -> str | None:
    """
    Returns the first word uppercased if it matches the CONCEPT_WORDS list,
    the value is multi-word, and the value has no trailing emoji.

    Returns None if any of those conditions fail.

    Requirements: 2.3
    """
    if is_single_token(value):
        return None
    # If there's a trailing emoji, the emoji rule takes precedence
    if extract_trailing_emoji(value) is not None:
        return None
    first_word = value.strip().split()[0].upper()
    if first_word in CONCEPT_WORDS:
        return first_word
    return None


# ---------------------------------------------------------------------------
# Row-level fixers
# ---------------------------------------------------------------------------

OPTION_COLS = ["option_1", "option_2", "option_3", "option_4"]
PAIR_COLS = [
    "pair_1_left", "pair_1_right",
    "pair_2_left", "pair_2_right",
    "pair_3_left", "pair_3_right",
]


def _fix_number_n(value: str) -> str | None:
    """Handle 'Number N' pattern -> return 'N' string."""
    m = re.match(r'^[Nn]umber\s+(\d+)$', value.strip())
    if m:
        return m.group(1)
    return None


def _fix_trophy(value: str) -> str | None:
    """Category 1: if any word in value is a trophy/chest keyword → '🏆'."""
    words = {w.upper().strip(".,!?") for w in value.split()}
    if words & TROPHY_KEYWORDS:
        return "🏆"
    return None


def _fix_color_leading(value: str) -> str | None:
    """Category 2: if first word is a colour word → return it uppercased."""
    first = value.strip().split()[0].upper() if value.strip() else ""
    if first in COLOR_WORDS:
        return first
    return None


def _fix_category_leading(value: str) -> str | None:
    """Category 4: if first word is a category word → return it uppercased."""
    first = value.strip().split()[0].upper() if value.strip() else ""
    if first in CATEGORY_WORDS:
        return first
    return None


def _fix_compound_ampersand(value: str) -> str | None:
    """Category 5: 'Word1 Word2 & ...' → uppercase first word."""
    if "&" in value:
        first = value.strip().split()[0].upper()
        if first in COLOR_WORDS or first in CONCEPT_WORDS or first in CATEGORY_WORDS:
            return first
        # fall back to just extracting the first word before &
        before = value.split("&")[0].strip()
        if before:
            return before.split()[0].upper()
    return None


def fix_option_cells(row: dict, qtype: str) -> tuple[dict, int, list[str]]:
    """
    Apply extraction chain to option_1..option_4 based on question_type.

    Returns (updated_row, cells_modified_count, warnings_list).

    Requirements: 1.1, 1.2, 1.3, 2.1-2.5, 3.1-3.3, 6.6
    """
    if qtype in ("speak_repeat", "matching"):
        return row, 0, []

    modified = 0
    warnings = []

    for col in OPTION_COLS:
        original = row.get(col, "")
        if not original or is_single_token(original):
            continue

        new_val = None

        if qtype == "count_objects":
            new_val = extract_leading_digit(original)
            if new_val is None:
                warnings.append(
                    f"count_objects: no leading digit in {col}={original!r}"
                )

        elif qtype == "multiple_choice":
            # Rule order: Number-N → trophy → compound → emoji → concept word → leading digit
            #             → colour → category → warn
            new_val = _fix_number_n(original)
            if new_val is None:
                new_val = _fix_trophy(original)
            if new_val is None:
                new_val = _fix_compound_ampersand(original)
            if new_val is None:
                new_val = extract_trailing_emoji(original)
            if new_val is None:
                new_val = extract_concept_word(original)
            if new_val is None:
                new_val = extract_leading_digit(original)
            if new_val is None:
                new_val = _fix_color_leading(original)
            if new_val is None:
                new_val = _fix_category_leading(original)
            if new_val is None:
                warnings.append(
                    f"multiple_choice: no rule matched {col}={original!r}"
                )

        elif qtype == "complete_pattern":
            new_val = extract_trailing_emoji(original)
            if new_val is None:
                warnings.append(
                    f"complete_pattern: no trailing emoji in {col}={original!r}"
                )

        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1

    return row, modified, warnings


def fix_ops_cell(row: dict) -> tuple[dict, int]:
    """
    For matching rows, replace count_emoji_or_image with '' if it equals 'ops'.

    Returns (updated_row, 1_if_changed_else_0).

    Requirements: 4.1, 4.2
    """
    col = "count_emoji_or_image"
    if row.get(col, "") == "ops":
        row[col] = ""
        return row, 1
    return row, 0


def fix_pair_cells(row: dict) -> tuple[dict, int, list[str]]:
    """
    For matching rows, apply extract_leading_digit then extract_trailing_emoji
    to each of the six pair cells.

    Returns (updated_row, cells_modified_count, warnings_list).

    Requirements: 5.1, 5.2, 5.3, 5.4
    """
    modified = 0
    warnings = []

    for col in PAIR_COLS:
        original = row.get(col, "")
        if not original or is_single_token(original):
            continue

        # Rule order: leading digit → trailing digit → trophy → emoji → colour → size/concept → category → warn
        new_val = extract_leading_digit(original)
        if new_val is None:
            new_val = extract_trailing_digit(original)
        if new_val is None:
            new_val = _fix_trophy(original)
        if new_val is None:
            new_val = extract_trailing_emoji(original)
        if new_val is None:
            new_val = _fix_compound_ampersand(original)
        if new_val is None:
            new_val = extract_concept_word(original)
        if new_val is None:
            new_val = _fix_color_leading(original)
        if new_val is None:
            new_val = _fix_category_leading(original)
        if new_val is None:
            warnings.append(f"matching pair: no rule matched {col}={original!r}")

        if new_val is not None and new_val != original:
            row[col] = new_val
            modified += 1

    return row, modified, warnings


# ---------------------------------------------------------------------------
# File processor
# ---------------------------------------------------------------------------

def process_file(path: Path) -> tuple[int, int, list[str]]:
    """
    Read, transform, and write back one CSV file in-place.

    Returns (rows_processed, cells_modified, warnings).

    Requirements: 6.2, 6.3, 6.5
    """
    try:
        with open(path, encoding="utf-8-sig", newline="") as fh:
            reader = csv.DictReader(fh)
            fieldnames = reader.fieldnames
            if fieldnames is None:
                return 0, 0, [f"{path}: no header row found"]
            # Strip None keys (blank trailing columns) — keep only named columns
            fieldnames = [f for f in fieldnames if f is not None]
            rows = []
            for row in reader:
                # Drop the None key from each row
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

            row, n = fix_ops_cell(row)
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
    """
    Accept a directory path argument; glob *.csv; call process_file per file.
    Print summary. Exit with code 1 if directory not found.

    Requirements: 6.1, 6.4, 6.5
    """
    if len(sys.argv) < 2:
        print("Usage: python fix_pg_math_options.py <csv_directory>")
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
        total_warnings.extend(warnings)
        if warnings:
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
