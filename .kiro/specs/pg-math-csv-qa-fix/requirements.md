# Requirements Document

## Introduction

135 Play Group Math CSV lesson files (located in `database/csv_imports/play_group_math/`) contain
question data that will be imported into the KiddoQuest platform. A QA audit has identified three
categories of defects that must be remediated before import:

1. **Multi-word options** — answer options in `option_1`–`option_4` columns use descriptive
   phrases (e.g. "Giant Dinosaur 🦕", "Number 5", "1 Footprint") instead of the single
   token format required for toddler-age learners.
2. **"ops" garbage values** — two files have the literal string `"ops"` in the
   `count_emoji_or_image` column on matching rows.
3. **Multi-word matching pair labels** — `pair_*_left` / `pair_*_right` cells in matching rows
   use verbose phrases (e.g. "1 Apple", "1 Red Apple", "Big Footprint 🐾", "1 Footprint 🐾")
   instead of the minimal labels required by the importer.

The fix must be applied by an automated Python script that processes all 135 files in-place,
leaving structurally valid rows untouched.

## Glossary

- **CSV_File**: One of the 135 `.csv` files in `database/csv_imports/play_group_math/`.
- **Option_Cell**: Any of `option_1`, `option_2`, `option_3`, `option_4` (columns 7–10, 0-indexed
  6–9).
- **Single_Token**: A string that is either (a) a single integer digit/number, (b) a single emoji
  character, or (c) a single uppercase word with no spaces.
- **Multi_Word_Option**: An Option_Cell value that contains one or more space characters after
  stripping leading/trailing whitespace, making it not a Single_Token.
- **Counting_Type**: A `question_type` value of `count_objects`.
- **MC_Type**: A `question_type` value of `multiple_choice`.
- **Pattern_Type**: A `question_type` value of `complete_pattern`.
- **Speak_Type**: A `question_type` value of `speak_repeat`.
- **Matching_Type**: A `question_type` value of `matching`.
- **Garbage_Value**: The literal string `"ops"` appearing in `count_emoji_or_image` on a
  Matching_Type row.
- **Fixer_Script**: The Python script that applies all remediations (`fix_pg_math_options.py`).
- **Pair_Cell**: Any of `pair_1_left`, `pair_1_right`, `pair_2_left`, `pair_2_right`,
  `pair_3_left`, `pair_3_right` (columns 14–19, 0-indexed 13–18).

---

## Requirements

### Requirement 1: Option Simplification for Counting Questions

**User Story:** As a content engineer, I want all `count_objects` answer options to contain only
bare digits, so that toddlers see a clean number to tap without distracting text.

#### Acceptance Criteria

1. WHEN a row has `question_type` equal to `count_objects`, THE Fixer_Script SHALL replace every
   Multi_Word_Option in that row's Option_Cells with the leading integer extracted from the cell
   value (e.g. `"1 Footprint"` → `"1"`, `"2 Footprints"` → `"2"`).
2. WHEN a `count_objects` Option_Cell already contains a Single_Token digit, THE Fixer_Script SHALL
   leave it unchanged.
3. IF a `count_objects` Option_Cell contains no leading digit, THEN THE Fixer_Script SHALL leave
   the cell unchanged and log a warning.

---

### Requirement 2: Option Simplification for Multiple-Choice Questions

**User Story:** As a content engineer, I want `multiple_choice` answer options to be single tokens
— either a bare number, a single emoji, or one uppercase word — so that the option cards render
correctly on the toddler UI.

#### Acceptance Criteria

1. WHEN a `multiple_choice` row's Option_Cell value matches the pattern `"Number N"` (where N is
   an integer), THE Fixer_Script SHALL replace the cell with just `N` (e.g. `"Number 5"` → `"5"`).
2. WHEN a `multiple_choice` row's Option_Cell is a Multi_Word_Option ending with a single emoji
   character, THE Fixer_Script SHALL replace the cell with that trailing emoji only
   (e.g. `"Giant Dinosaur 🦕"` → `"🦕"`).
3. WHEN a `multiple_choice` row's Option_Cell is a Multi_Word_Option that contains no emoji and
   starts with a known adjective/concept word (e.g. BIG, SMALL, TALL, SHORT, HEAVY, LIGHT, ABOVE,
   BELOW, INSIDE, OUTSIDE, LEFT, RIGHT, NEAR, FAR, LONG, SHORT, SAME, DIFFERENT), THE
   Fixer_Script SHALL replace the cell with that leading concept word in uppercase
   (e.g. `"Big Footprint 🐾"` → `"BIG"` is superseded by rule 2 which extracts emoji first;
   this rule applies when no trailing emoji exists).
4. WHEN a `multiple_choice` row's Option_Cell is already a Single_Token, THE Fixer_Script SHALL
   leave it unchanged.
5. IF a `multiple_choice` Option_Cell is a Multi_Word_Option that does not match any rule above,
   THEN THE Fixer_Script SHALL leave the cell unchanged and log a warning with the file name, row
   number, and cell value.

---

### Requirement 3: Option Simplification for Pattern Questions

**User Story:** As a content engineer, I want `complete_pattern` answer options to be single emojis
or single concept words, consistent with the toddler UI display.

#### Acceptance Criteria

1. WHEN a `complete_pattern` row's Option_Cell is a Multi_Word_Option ending with a single emoji,
   THE Fixer_Script SHALL replace the cell with that trailing emoji only
   (e.g. `"Small 🐭"` → `"🐭"`).
2. WHEN a `complete_pattern` row's Option_Cell is already a Single_Token, THE Fixer_Script SHALL
   leave it unchanged.
3. IF a `complete_pattern` Option_Cell is a Multi_Word_Option with no trailing emoji, THEN THE
   Fixer_Script SHALL leave the cell unchanged and log a warning.

---

### Requirement 4: Garbage Value Removal from Matching Rows

**User Story:** As a content engineer, I want `matching` rows to have an empty
`count_emoji_or_image` cell (not `"ops"`), so that the importer does not reject those rows.

#### Acceptance Criteria

1. WHEN a `matching` row's `count_emoji_or_image` cell contains the Garbage_Value `"ops"`, THE
   Fixer_Script SHALL replace it with an empty string.
2. WHEN a `matching` row's `count_emoji_or_image` cell is already empty, THE Fixer_Script SHALL
   leave it unchanged.

---

### Requirement 5: Matching Pair Label Simplification

**User Story:** As a content engineer, I want matching pair labels to use minimal tokens rather
than verbose phrases, so the match cards display cleanly in the toddler UI.

#### Acceptance Criteria

1. WHEN a `matching` row's Pair_Cell value is a Multi_Word_Option ending with a single emoji, THE
   Fixer_Script SHALL replace it with that trailing emoji only
   (e.g. `"Dinosaur 🦕"` → `"🦕"`, `"Bird 🐦"` → `"🐦"`).
2. WHEN a `matching` row's Pair_Cell matches the pattern `"N Word(s)"` where N is a leading digit
   (e.g. `"1 Apple"`, `"2 Footprints"`, `"3 Footprints 🐾🐾🐾"`), THE Fixer_Script SHALL replace
   it with just `N` (e.g. `"1 Apple"` → `"1"`, `"2 Footprints"` → `"2"`).
3. WHEN a `matching` row's Pair_Cell is already a Single_Token, THE Fixer_Script SHALL leave it
   unchanged.
4. IF a `matching` row's Pair_Cell is a Multi_Word_Option that does not match any rule above, THEN
   THE Fixer_Script SHALL leave it unchanged and log a warning.

---

### Requirement 6: Script Execution and File Handling

**User Story:** As a content engineer, I want to run a single script that processes all 135 files
in-place with a summary report, so I can verify the fix in one step.

#### Acceptance Criteria

1. THE Fixer_Script SHALL accept a directory path as its input argument and process every `.csv`
   file found directly in that directory.
2. WHEN processing a CSV_File, THE Fixer_Script SHALL preserve the original 19-column header row
   exactly.
3. WHEN processing a CSV_File, THE Fixer_Script SHALL overwrite the file in-place with the
   corrected content using the same encoding (UTF-8).
4. WHEN processing is complete, THE Fixer_Script SHALL print a summary showing: total files
   processed, total cells modified, total warnings emitted.
5. IF a CSV_File cannot be read or written, THEN THE Fixer_Script SHALL log the error and continue
   processing the remaining files.
6. THE Fixer_Script SHALL NOT modify any row whose `question_type` is `speak_repeat`, as those
   option cells intentionally contain phrases.
