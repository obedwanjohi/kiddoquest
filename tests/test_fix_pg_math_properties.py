"""
Property-based tests for fix_pg_math_options.py

Feature: pg-math-csv-qa-fix

Uses the `hypothesis` library. Each test runs a minimum of 100 examples.
"""

import sys
import os
import csv
import io
import tempfile

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from pathlib import Path
from hypothesis import given, settings, assume
from hypothesis import strategies as st

from fix_pg_math_options import (
    extract_leading_digit,
    extract_trailing_emoji,
    extract_concept_word,
    is_single_token,
    CONCEPT_WORDS,
    EMOJI_LIST,
)

# ---------------------------------------------------------------------------
# Helpers / generators
# ---------------------------------------------------------------------------

# Exclude multi-codepoint keycap sequences (e.g. 1️⃣) — these are edge cases
# not found in the actual CSV data and require separate grapheme cluster handling.
_SIMPLE_EMOJI_LIST = [e for e in EMOJI_LIST if len(e) == 1]

# A sampled single emoji from the curated list (single codepoint only)
single_emoji = st.sampled_from(_SIMPLE_EMOJI_LIST)

# Simple ASCII word (non-empty, no spaces)
ascii_word = st.from_regex(r'[A-Za-z]+', fullmatch=True)

# Non-digit, non-space suffix word
suffix_word = st.from_regex(r'[A-Za-z]+[A-Za-z]*', fullmatch=True)


# ---------------------------------------------------------------------------
# Property 1: Digit extraction correctness
# Feature: pg-math-csv-qa-fix, Property 1: Digit extraction correctness
# Validates: Requirements 1.1, 5.2
# ---------------------------------------------------------------------------

@given(
    n=st.integers(min_value=0, max_value=99),
    word=suffix_word,
)
@settings(max_examples=200)
def test_property1_digit_extraction_correctness(n, word):
    """
    For any string that starts with one or more digits followed by at least
    one space character, extract_leading_digit should return exactly those
    leading digits as a string.
    Validates: Requirements 1.1, 5.2
    """
    value = f"{n} {word}"
    result = extract_leading_digit(value)
    assert result == str(n), (
        f"extract_leading_digit({value!r}) returned {result!r}, expected {str(n)!r}"
    )


@given(
    word1=st.from_regex(r'[A-Za-z]+', fullmatch=True),
    word2=st.from_regex(r'[A-Za-z]+', fullmatch=True),
)
@settings(max_examples=100)
def test_property1_no_digit_returns_none(word1, word2):
    """
    For any multi-word string that does not start with digits,
    extract_leading_digit should return None.
    Validates: Requirements 1.1
    """
    value = f"{word1} {word2}"
    result = extract_leading_digit(value)
    assert result is None, (
        f"extract_leading_digit({value!r}) returned {result!r}, expected None"
    )


@given(n=st.integers(min_value=0, max_value=99))
@settings(max_examples=100)
def test_property1_single_token_returns_none(n):
    """
    For a value that is already a single token (no spaces), extract_leading_digit
    should return None regardless of content.
    Validates: Requirements 5.2 (single-token guard)
    """
    value = str(n)
    assert is_single_token(value)
    result = extract_leading_digit(value)
    assert result is None, (
        f"extract_leading_digit({value!r}) should return None for single token"
    )



# ---------------------------------------------------------------------------
# Property 2: Emoji extraction correctness
# Feature: pg-math-csv-qa-fix, Property 2: Emoji extraction correctness
# Validates: Requirements 2.2, 3.1, 5.1
# ---------------------------------------------------------------------------

@given(
    word1=st.from_regex(r'[A-Za-z]+', fullmatch=True),
    word2=st.from_regex(r'[A-Za-z]+', fullmatch=True),
    emoji=single_emoji,
)
@settings(max_examples=200)
def test_property2_emoji_extraction_correctness(word1, word2, emoji):
    """
    For any multi-word string whose final character is a single emoji,
    extract_trailing_emoji should return exactly that emoji.
    Validates: Requirements 2.2, 3.1, 5.1
    """
    value = f"{word1} {word2} {emoji}"
    result = extract_trailing_emoji(value)
    assert result == emoji, (
        f"extract_trailing_emoji({value!r}) returned {result!r}, expected {emoji!r}"
    )


@given(value=ascii_word)
@settings(max_examples=100)
def test_property2_single_token_returns_none(value):
    """
    For any single-token value (no spaces), extract_trailing_emoji should
    return None.
    Validates: Requirements 2.2 (single-token guard)
    """
    assume(is_single_token(value))
    result = extract_trailing_emoji(value)
    assert result is None, (
        f"extract_trailing_emoji({value!r}) should return None for single token"
    )


@given(value=st.from_regex(r'[A-Za-z]+ [A-Za-z]+', fullmatch=True))
@settings(max_examples=100)
def test_property2_no_emoji_returns_none(value):
    """
    For any multi-word string with no emoji, extract_trailing_emoji should
    return None.
    Validates: Requirements 2.2
    """
    result = extract_trailing_emoji(value)
    assert result is None, (
        f"extract_trailing_emoji({value!r}) should return None when no trailing emoji"
    )


# ---------------------------------------------------------------------------
# Property 3: Concept-word extraction correctness
# Feature: pg-math-csv-qa-fix, Property 3: Concept-word extraction correctness
# Validates: Requirements 2.3
# ---------------------------------------------------------------------------

@given(
    concept=st.sampled_from(sorted(CONCEPT_WORDS)),
    suffix=suffix_word,
)
@settings(max_examples=200)
def test_property3_concept_word_extraction_correctness(concept, suffix):
    """
    For any multi-word string that starts with a concept word and has no
    trailing emoji, extract_concept_word should return that word in uppercase.
    Validates: Requirements 2.3
    """
    # Mix case on the concept word to test case-insensitivity
    value = f"{concept.capitalize()} {suffix}"
    assume(" " in value)
    assume(extract_trailing_emoji(value) is None)
    result = extract_concept_word(value)
    assert result == concept.upper(), (
        f"extract_concept_word({value!r}) returned {result!r}, expected {concept.upper()!r}"
    )


@given(
    concept=st.sampled_from(sorted(CONCEPT_WORDS)),
    suffix=suffix_word,
    emoji=single_emoji,
)
@settings(max_examples=100)
def test_property3_emoji_takes_precedence(concept, suffix, emoji):
    """
    For any multi-word string starting with a concept word but ending with an
    emoji, extract_concept_word should return None (emoji rule wins).
    Validates: Requirements 2.3
    """
    value = f"{concept.capitalize()} {suffix} {emoji}"
    assume(" " in value)
    result = extract_concept_word(value)
    assert result is None, (
        f"extract_concept_word({value!r}) should return None when trailing emoji present"
    )


@given(
    non_concept=st.from_regex(r'[A-Za-z]+', fullmatch=True).filter(
        lambda w: w.upper() not in CONCEPT_WORDS
    ),
    suffix=suffix_word,
)
@settings(max_examples=100)
def test_property3_non_concept_word_returns_none(non_concept, suffix):
    """
    For any multi-word string whose first word is not a concept word,
    extract_concept_word should return None.
    Validates: Requirements 2.3
    """
    value = f"{non_concept} {suffix}"
    assume(extract_trailing_emoji(value) is None)
    result = extract_concept_word(value)
    assert result is None, (
        f"extract_concept_word({value!r}) should return None for non-concept first word"
    )


# Need these imports for the remaining properties
from fix_pg_math_options import (
    fix_option_cells,
    fix_ops_cell,
    fix_pair_cells,
    process_file,
    OPTION_COLS,
    PAIR_COLS,
)


# ---------------------------------------------------------------------------
# Shared row builder helper
# ---------------------------------------------------------------------------

_FIELDNAMES = [
    "question_type", "lesson_title", "prompt", "prompt_image", "prompt_audio",
    "correct_answer", "option_1", "option_2", "option_3", "option_4",
    "count_emoji_or_image", "target_count", "target_word",
    "pair_1_left", "pair_1_right", "pair_2_left", "pair_2_right",
    "pair_3_left", "pair_3_right",
]


def _make_row(**kwargs) -> dict:
    """Build a minimal row dict with empty defaults."""
    row = {f: "" for f in _FIELDNAMES}
    row.update(kwargs)
    return row


def _write_temp_csv(rows: list[dict]) -> str:
    """Write rows to a temp CSV file and return the path."""
    fd, path = tempfile.mkstemp(suffix=".csv")
    os.close(fd)
    with open(path, "w", encoding="utf-8", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=_FIELDNAMES)
        writer.writeheader()
        writer.writerows(rows)
    return path


# ---------------------------------------------------------------------------
# Property 4: Single-token idempotence
# Feature: pg-math-csv-qa-fix, Property 4: Single-token idempotence
# Validates: Requirements 1.2, 2.4, 3.2, 5.3
# ---------------------------------------------------------------------------

@given(
    qtype=st.sampled_from(["count_objects", "multiple_choice", "complete_pattern"]),
    token=st.one_of(
        st.integers(min_value=0, max_value=20).map(str),
        st.from_regex(r'[A-Z]+', fullmatch=True),
        st.sampled_from(_SIMPLE_EMOJI_LIST),
    ),
)
@settings(max_examples=200)
def test_property4_single_token_idempotence(qtype, token):
    """
    For any option cell value that is already a single token (no spaces),
    fix_option_cells should not modify it.
    Validates: Requirements 1.2, 2.4, 3.2, 5.3
    """
    row = _make_row(
        question_type=qtype,
        option_1=token, option_2=token, option_3=token, option_4=token,
    )
    result_row, modified, _ = fix_option_cells(row, qtype)
    assert modified == 0, (
        f"fix_option_cells modified single-token {token!r} for qtype={qtype!r}"
    )
    for col in OPTION_COLS:
        assert result_row[col] == token, (
            f"fix_option_cells changed {col} from {token!r} to {result_row[col]!r}"
        )


@given(
    token=st.one_of(
        st.integers(min_value=0, max_value=20).map(str),
        st.from_regex(r'[A-Z]+', fullmatch=True),
        st.sampled_from(_SIMPLE_EMOJI_LIST),
    ),
)
@settings(max_examples=200)
def test_property4_pair_cell_single_token_idempotence(token):
    """
    For any pair cell value that is already a single token, fix_pair_cells
    should not modify it.
    Validates: Requirements 5.3
    """
    row = _make_row(
        question_type="matching",
        pair_1_left=token, pair_1_right=token,
        pair_2_left=token, pair_2_right=token,
        pair_3_left=token, pair_3_right=token,
    )
    result_row, modified, _ = fix_pair_cells(row)
    assert modified == 0, (
        f"fix_pair_cells modified single-token {token!r}"
    )


# ---------------------------------------------------------------------------
# Property 5: Ops garbage cleanup
# Feature: pg-math-csv-qa-fix, Property 5: Ops garbage cleanup
# Validates: Requirements 4.1, 4.2
# ---------------------------------------------------------------------------

@given(
    initial_val=st.one_of(st.just("ops"), st.just(""), st.text(min_size=0, max_size=5)),
)
@settings(max_examples=200)
def test_property5_ops_garbage_cleanup(initial_val):
    """
    For any matching row, after fix_ops_cell runs, count_emoji_or_image must
    not contain 'ops'.
    Validates: Requirements 4.1, 4.2
    """
    row = _make_row(question_type="matching", count_emoji_or_image=initial_val)
    result_row, _ = fix_ops_cell(row)
    assert result_row["count_emoji_or_image"] != "ops", (
        f"fix_ops_cell left 'ops' in count_emoji_or_image (input was {initial_val!r})"
    )


@given(initial_val=st.text(min_size=0, max_size=10).filter(lambda s: s != "ops"))
@settings(max_examples=100)
def test_property5_non_ops_unchanged(initial_val):
    """
    For any matching row where count_emoji_or_image is not 'ops', fix_ops_cell
    should leave it unchanged.
    Validates: Requirements 4.2
    """
    row = _make_row(question_type="matching", count_emoji_or_image=initial_val)
    result_row, changed = fix_ops_cell(row)
    assert changed == 0, (
        f"fix_ops_cell modified non-ops value {initial_val!r}"
    )
    assert result_row["count_emoji_or_image"] == initial_val


# ---------------------------------------------------------------------------
# Property 6: speak_repeat immutability
# Feature: pg-math-csv-qa-fix, Property 6: speak_repeat immutability
# Validates: Requirements 6.6
# ---------------------------------------------------------------------------

@given(
    opt1=st.text(min_size=0, max_size=30),
    opt2=st.text(min_size=0, max_size=30),
    opt3=st.text(min_size=0, max_size=30),
    opt4=st.text(min_size=0, max_size=30),
)
@settings(max_examples=200)
def test_property6_speak_repeat_immutability(opt1, opt2, opt3, opt4):
    """
    For any speak_repeat row, all four option cells must be byte-for-byte
    identical before and after fix_option_cells runs.
    Validates: Requirements 6.6
    """
    row = _make_row(
        question_type="speak_repeat",
        option_1=opt1, option_2=opt2, option_3=opt3, option_4=opt4,
    )
    original_opts = {col: row[col] for col in OPTION_COLS}
    result_row, modified, _ = fix_option_cells(row, "speak_repeat")
    assert modified == 0, "fix_option_cells reported modifications for speak_repeat"
    for col in OPTION_COLS:
        assert result_row[col] == original_opts[col], (
            f"fix_option_cells changed {col} for speak_repeat: "
            f"{original_opts[col]!r} -> {result_row[col]!r}"
        )


# ---------------------------------------------------------------------------
# Property 7: Header preservation
# Feature: pg-math-csv-qa-fix, Property 7: Header preservation
# Validates: Requirements 6.2
# ---------------------------------------------------------------------------

@given(
    qtypes=st.lists(
        st.sampled_from(["count_objects", "multiple_choice", "speak_repeat", "matching"]),
        min_size=1,
        max_size=10,
    )
)
@settings(max_examples=100)
def test_property7_header_preservation(qtypes):
    """
    For any CSV file processed by process_file, the header row written to
    disk must be identical to the header row that was read from disk.
    Validates: Requirements 6.2
    """
    rows = [_make_row(question_type=qt) for qt in qtypes]
    path = _write_temp_csv(rows)
    try:
        process_file(Path(path))
        with open(path, encoding="utf-8", newline="") as fh:
            reader = csv.DictReader(fh)
            written_fields = list(reader.fieldnames or [])
        assert written_fields == _FIELDNAMES, (
            f"Header mismatch after process_file: got {written_fields}"
        )
    finally:
        os.unlink(path)
