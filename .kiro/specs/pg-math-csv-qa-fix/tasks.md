# Implementation Plan: Play Group Math CSV QA Fix

## Overview

Build `fix_pg_math_options.py` — a single-file Python script that remediates multi-word option
cells, clears "ops" garbage, and simplifies matching pair labels across all 135 Play Group Math
CSV files. Tests live in `tests/test_fix_pg_math_options.py` (unit) and
`tests/test_fix_pg_math_properties.py` (property-based).

## Tasks

- [x] 1. Set up the script file and shared helpers
  - Create `fix_pg_math_options.py` at the workspace root
  - Define `CONCEPT_WORDS` set and `EMOJI_LIST` constant
  - Implement `is_single_token(value: str) -> bool`
  - _Requirements: 1.2, 2.4, 3.2, 5.3_

- [x] 2. Implement the three extractor functions
  - [x] 2.1 Implement `extract_leading_digit(value)`
    - Match `^\d+\s` with `re`; return the digit string or `None`
    - Guard: return `None` if already a single token
    - _Requirements: 1.1, 5.2_
  - [x] 2.2 Write property test for digit extraction (Property 1)
    - **Property 1: Digit extraction correctness**
    - **Validates: Requirements 1.1, 5.2**
  - [x] 2.3 Implement `extract_trailing_emoji(value)`
    - Use `unicodedata` + regex to detect a single trailing emoji; return it or `None`
    - Guard: return `None` if already a single token
    - _Requirements: 2.2, 3.1, 5.1_
  - [x] 2.4 Write property test for emoji extraction (Property 2)
    - **Property 2: Emoji extraction correctness**
    - **Validates: Requirements 2.2, 3.1, 5.1**
  - [x] 2.5 Implement `extract_concept_word(value)`
    - Match first word against `CONCEPT_WORDS`; only fires if no trailing emoji
    - _Requirements: 2.3_
  - [x] 2.6 Write property test for concept-word extraction (Property 3)
    - **Property 3: Concept-word extraction correctness**
    - **Validates: Requirements 2.3**

- [x] 3. Implement `fix_option_cells` and `fix_ops_cell`
  - [x] 3.1 Implement `fix_option_cells(row, qtype)`
    - Dispatch on `question_type`: digit chain for `count_objects`; full chain for
      `multiple_choice` (Number-N special case first); emoji chain for `complete_pattern`;
      skip for `speak_repeat` and `matching`
    - Collect warnings per cell; return (row, cells_modified, warnings)
    - _Requirements: 1.1, 1.2, 1.3, 2.1–2.5, 3.1–3.3, 6.6_
  - [x] 3.2 Write property test for speak_repeat immutability (Property 6)
    - **Property 6: speak_repeat immutability**
    - **Validates: Requirements 6.6**
  - [x] 3.3 Implement `fix_ops_cell(row)`
    - Replace `count_emoji_or_image` with `""` when value == `"ops"` on matching rows
    - _Requirements: 4.1, 4.2_
  - [x] 3.4 Write property test for ops cleanup (Property 5)
    - **Property 5: Ops garbage cleanup**
    - **Validates: Requirements 4.1, 4.2**

- [x] 4. Implement `fix_pair_cells`
  - [x] 4.1 Implement `fix_pair_cells(row)`
    - Apply `extract_leading_digit` then `extract_trailing_emoji` to each of the six pair cells
    - Collect warnings; return (row, cells_modified, warnings)
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  - [x] 4.2 Write property test for single-token idempotence (Property 4)
    - **Property 4: Single-token idempotence**
    - **Validates: Requirements 1.2, 2.4, 3.2, 5.3**

- [x] 5. Checkpoint — run unit tests
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement `process_file` and `main`
  - [x] 6.1 Implement `process_file(path)`
    - Open with `utf-8-sig`; read via `csv.DictReader`
    - Call `fix_option_cells`, `fix_ops_cell` (matching rows), `fix_pair_cells` (matching rows)
      per row
    - Write back with `csv.DictWriter`, same field order, `utf-8` encoding
    - Return `(rows_processed, cells_modified, warnings)`
    - _Requirements: 6.2, 6.3, 6.5_
  - [x] 6.2 Write property test for header preservation (Property 7)
    - **Property 7: Header preservation**
    - **Validates: Requirements 6.2**
  - [x] 6.3 Implement `main()`
    - Accept directory path argument; glob `*.csv`; call `process_file` per file
    - Accumulate and print summary (files, cells modified, warnings)
    - Exit with code 1 if directory not found
    - _Requirements: 6.1, 6.4, 6.5_

- [x] 7. Final checkpoint — run full test suite
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Property tests require `hypothesis` (`pip install hypothesis`)
- The script can be run as: `python fix_pg_math_options.py database/csv_imports/play_group_math`
- Back up the CSV directory before running the script for the first time
