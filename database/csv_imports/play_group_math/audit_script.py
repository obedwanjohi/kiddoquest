import csv
import os
from pathlib import Path

# Define the directory
csv_dir = r"c:\xampp\htdocs\kid\database\csv_imports\play_group_math"

# Expected header (19 columns)
EXPECTED_HEADER = [
    'question_type', 'lesson_title', 'prompt', 'prompt_image', 'prompt_audio',
    'correct_answer', 'option_1', 'option_2', 'option_3', 'option_4',
    'count_emoji_or_image', 'target_count', 'target_word',
    'pair_1_left', 'pair_1_right', 'pair_2_left', 'pair_2_right',
    'pair_3_left', 'pair_3_right'
]

# Issues storage
all_issues = []

def is_multi_word(text):
    """Check if text contains multi-word phrases (spaces)"""
    if not text or text.strip() == '':
        return False
    # Check if it contains spaces (multi-word)
    # Exclude single numbers, emojis, single uppercase words
    text = text.strip()
    if text.isdigit():
        return False
    # Check if it's a single uppercase word (like BIG, SMALL, RED)
    if text.isupper() and ' ' not in text:
        return False
    # If it has spaces, it's multi-word
    if ' ' in text:
        return True
    return False

def audit_file(filepath):
    """Audit a single CSV file"""
    filename = os.path.basename(filepath)
    issues = []
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            reader = csv.reader(f)
            rows = list(reader)
            
        if not rows:
            issues.append(f"File is empty")
            return issues
            
        # Check header
        header = rows[0]
        if len(header) != 19:
            issues.append(f"Line 1: Header has {len(header)} columns (expected 19)")
        
        # Audit each data row
        for line_num, row in enumerate(rows[1:], start=2):
            if not row or len(row) < 19:
                issues.append(f"Line {line_num}: Row has {len(row)} columns (expected 19)")
                continue
                
            question_type = row[0].strip() if row[0] else ''
            prompt_image = row[3].strip() if row[3] else ''
            prompt_audio = row[4].strip() if row[4] else ''
            
            # CRITERION 2: Audio & Image Prompt Completeness
            if not prompt_image:
                issues.append(f"Line {line_num}: Missing prompt_image")
            if not prompt_audio:
                issues.append(f"Line {line_num}: Missing prompt_audio")
            
            # CRITERION 1: Toddler Age-Appropriateness (multi-word options)
            # Check option_1, option_2, option_3, option_4 for multiple_choice, count_objects, complete_pattern
            if question_type in ['multiple_choice', 'count_objects', 'complete_pattern']:
                for opt_idx in [6, 7, 8, 9]:  # option_1, option_2, option_3, option_4
                    option = row[opt_idx].strip() if row[opt_idx] else ''
                    if option and is_multi_word(option):
                        issues.append(f"Line {line_num}: Multi-word option '{option}' in column {opt_idx+1} - REPLACE with single number/emoji/uppercase word")
            
            # CRITERION 3: CSV Column Structure & Matching Alignment
            if question_type == 'matching':
                # Columns G through M (indices 10-12) must be BLANK
                for col_idx in [10, 11, 12]:  # count_emoji_or_image, target_count, target_word
                    cell_value = row[col_idx].strip() if row[col_idx] else ''
                    if cell_value:
                        issues.append(f"Line {line_num}: Matching question has data '{cell_value}' in column {col_idx+1} (should be BLANK for matching)")
                
                # Matching pairs MUST be in columns N through S (indices 13-18)
                # Check if pairs are actually present
                pairs_present = any(row[i].strip() for i in [13, 14, 15, 16, 17, 18])
                if not pairs_present:
                    issues.append(f"Line {line_num}: Matching question missing pair data in columns N-S")
                    
    except Exception as e:
        issues.append(f"Error reading file: {str(e)}")
    
    return issues

# Audit all CSV files
csv_files = list(Path(csv_dir).glob('*.csv'))
print(f"Found {len(csv_files)} CSV files to audit\n")

for csv_file in sorted(csv_files):
    issues = audit_file(csv_file)
    if issues:
        all_issues.append((csv_file.name, issues))
        print(f"ISSUES in {csv_file.name}:")
        for issue in issues:
            print(f"  - {issue}")
        print()

# Generate summary report
print("\n" + "="*80)
print("QA AUDIT SUMMARY REPORT")
print("="*80)
print(f"Total files audited: {len(csv_files)}")
print(f"Files with issues: {len(all_issues)}")
print()

if all_issues:
    for filename, issues in all_issues:
        print(f"\n{'='*80}")
        print(f"FILE: {filename}")
        print(f"{'='*80}")
        for issue in issues:
            print(f"  - {issue}")
else:
    print("No issues found!")

print("\n" + "="*80)
