# Design Document: Play Group Math CSV QA Fix

## Overview

A single Python script (`fix_pg_math_options.py`) processes all 135 Play Group Math CSV files
in-place. It applies four transformations:

1. Extract leading digit from multi-word counting/quantity options and pair labels.
2. Extract trailing emoji from multi-word options and pair labels.
3. Extract leading concept word (uppercased) from multi-word options with no emoji.
4. Clear the `"ops"` garbage value from `count_emoji_or_image` on matching rows.

`speak_repeat` rows are always skipped — their option cells intentionally contain phrases.

---

## Architecture

```
fix_pg_math_options.py
│
├── main()                    ← CLI entry, iterates .csv files, prints summary
├── process_file(path)        ← reads, transforms, writes one CSV in-place
├── process_row(row, qtype)   ← dispatches to per-type transformers
│
├── fix_option_cells(row, qtype)   ← transforms option_1..option_4 for MC/count/pattern
├── fix_pair_cells(row)            ← transforms pair_1_left..pair_3_right for matching
├── fix_ops_cell(row)              ← clears "ops" in count_emoji_or_image for matching
│
└── Extractors (pure functions)
    ├── extract_leading_digit(value)   → str | None
    ├── extract_trailing_emoji(value)  → str | None
    └── extract_concept_word(value)    → str | None
```

The script has no external dependencies beyond the Python standard library (`csv`, `re`, `sys`,
`pathlib`, `unicodedata`).

---

## Components and Interfaces

### `extract_leading_digit(value: str) -> str | None`

Returns the leading integer string if the value starts with one or more digits followed by a
space, otherwise `None`.

```python
extract_leading_digit("1 Footprint")        # → "1"
extract_leading_digit("2 Footprints 🐾🐾")  # → "2"
extract_leading_digit("apple")              # → None
extract_leading_digit("1")                  # → None  (already single token, caller skips)
```

### `extract_trailing_emoji(value: str) -> str | None`

Returns the final character of the string if it is a Unicode emoji (category So, Sm, or in
known emoji ranges), otherwise `None`. Uses `unicodedata` for category detection plus a regex
for surrogate-pair / multi-codepoint sequences.

```python
extract_trailing_emoji("Giant Dinosaur 🦕")   # → "🦕"
extract_trailing_emoji("Bird ABOVE Sky 🐦")   # → "🐦"
extract_trailing_emoji("Number 5")            # → None
extract_trailing_emoji("🦕")                  # → None  (already single token, caller skips)
```

### `extract_concept_word(value: str) -> str | None`

Returns the first word uppercased if it matches the known concept-word list and the value is
multi-word with no trailing emoji.

Concept word list:
`BIG, SMALL, TALL, SHORT, HEAVY, LIGHT, ABOVE, BELOW, INSIDE, OUTSIDE, LEFT, RIGHT,
NEAR, FAR, LONG, SAME, DIFFERENT, ROUND, SQUARE, GIANT, TINY, HIGH, LOW, WIDE, NARROW`

```python
extract_concept_word("Big Footprint")          # → "BIG"
extract_concept_word("Tall Giraffe 🦒")        # → None  (has emoji → emoji rule wins)
extract_concept_word("Round Wall Clock")       # → "ROUND"
extract_concept_word("Safari Badge")           # → None  (not in list)
```

### `is_single_token(value: str) -> bool`

Returns `True` if the stripped value contains no spaces (single digit, single emoji, or single
word). Used as a short-circuit guard in all transformers.

### `fix_option_cells(row, qtype) -> (row, int, list[str])`

Applies the correct extraction chain to `option_1..option_4` based on `qtype`:

| question_type    | Extraction chain                                |
|------------------|-------------------------------------------------|
| count_objects    | `extract_leading_digit` → warn if no match      |
| multiple_choice  | `extract_leading_digit("Number N")` → `extract_trailing_emoji` → `extract_concept_word` → warn |
| complete_pattern | `extract_trailing_emoji` → warn                 |
| speak_repeat     | skip (return row unchanged)                     |
| matching         | skip (option cells are blank on matching rows)  |

Returns the updated row, a count of cells modified, and a list of warning strings.

### `fix_pair_cells(row) -> (row, int, list[str])`

For matching rows, applies `extract_leading_digit` then `extract_trailing_emoji` to each of the
six pair cells. Returns updated row, modified count, warnings.

### `fix_ops_cell(row) -> (row, int)`

For matching rows, replaces `count_emoji_or_image` with `""` if its current value is `"ops"`.
Returns updated row and 1 if changed, 0 otherwise.

### `process_file(path: Path) -> (int, int, list[str])`

1. Opens the file with `utf-8-sig` encoding (handles BOM if present).
2. Reads all rows using `csv.DictReader`.
3. Calls the three fix functions per row based on `question_type`.
4. Writes the result back with `csv.DictWriter` preserving field order.
5. Returns `(rows_processed, cells_modified, warnings)`.

### `main()`

Accepts one positional argument: the directory path. Iterates all `*.csv` files, calls
`process_file`, accumulates totals, prints summary.

---

## Data Models

Each CSV row is a `dict[str, str]` with these keys (in order):

```
question_type, lesson_title, prompt, prompt_image, prompt_audio,
correct_answer, option_1, option_2, option_3, option_4,
count_emoji_or_image, target_count, target_word,
pair_1_left, pair_1_right, pair_2_left, pair_2_right, pair_3_left, pair_3_right
```

The script treats all values as plain strings; no type casting is performed.

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of
a system — essentially, a formal statement about what the system should do. Properties serve as
the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

---

Property 1: Digit extraction correctness
*For any* string that starts with one or more digits followed by at least one space character,
`extract_leading_digit` should return exactly those leading digits as a string, and the fixer
should write only that digit string into the option cell.
**Validates: Requirements 1.1, 5.2**

---

Property 2: Emoji extraction correctness
*For any* multi-word string whose final character is a single emoji, `extract_trailing_emoji`
should return exactly that emoji character, and the fixer should write only that emoji into the
option/pair cell.
**Validates: Requirements 2.2, 3.1, 5.1**

---

Property 3: Concept-word extraction correctness
*For any* multi-word string that starts with a word from the known concept-word list and contains
no trailing emoji, `extract_concept_word` should return that word in uppercase.
**Validates: Requirements 2.3**

---

Property 4: Single-token idempotence
*For any* option or pair cell value that is already a single token (no spaces), the fixer should
not modify it — the output value equals the input value.
**Validates: Requirements 1.2, 2.4, 3.2, 5.3**

---

Property 5: Ops garbage cleanup
*For any* matching row, after the fixer runs, the `count_emoji_or_image` cell must not contain
the string `"ops"`.
**Validates: Requirements 4.1, 4.2**

---

Property 6: speak_repeat immutability
*For any* `speak_repeat` row, all four option cells must be byte-for-byte identical before and
after the fixer runs.
**Validates: Requirements 6.6**

---

Property 7: Header preservation
*For any* CSV file processed by the script, the header row written to disk must be identical to
the header row that was read from disk.
**Validates: Requirements 6.2**

---

## Error Handling

| Situation | Behaviour |
|-----------|-----------|
| Leading digit expected but absent | Log warning with file, row, cell; leave cell unchanged |
| Multi-word cell matches no rule | Log warning with file, row, cell; leave cell unchanged |
| File read error | Log error; skip file; continue to next |
| File write error | Log error; skip file; continue to next |
| Directory not found | Print error and exit with code 1 |

Warnings are collected in memory and printed after all files are processed, grouped by file.

---

## Testing Strategy

### Unit tests (`tests/test_fix_pg_math_options.py`)

Test specific examples and edge cases for each pure extractor function:

- `extract_leading_digit`: known good inputs, no-digit inputs, already-single-token guard
- `extract_trailing_emoji`: strings with trailing emoji, strings without, edge case of
  multi-codepoint emoji (e.g. 🐾🐾 as last chars)
- `extract_concept_word`: concept-word present vs. absent; emoji overrides concept-word rule
- `is_single_token`: single digit, single emoji, single word, multi-word

### Property-based tests (`tests/test_fix_pg_math_properties.py`)

Use `hypothesis` library. Each test runs minimum 100 examples.

| Test | Generator strategy |
|------|--------------------|
| P1 Digit extraction | `st.integers(min_value=0, max_value=20)` combined with `st.text(alphabet=st.characters(whitelist_categories=('Lu','Ll')))` to build `"N word"` strings |
| P2 Emoji extraction | `st.text()` prefix + single emoji from curated list |
| P3 Concept-word extraction | concept word + space + random suffix word, no trailing emoji |
| P4 Idempotence | `st.one_of(st.integers().map(str), st.sampled_from(EMOJI_LIST), st.from_regex(r'[A-Z]+', fullmatch=True))` |
| P5 Ops cleanup | generate matching rows dict with `count_emoji_or_image="ops"` or `""` |
| P6 speak_repeat immutability | generate full CSV row dict with `question_type="speak_repeat"` and random option values |
| P7 Header preservation | generate list of row dicts; write temp CSV; run `process_file`; compare headers |

Tag format: `# Feature: pg-math-csv-qa-fix, Property N: <property title>`
