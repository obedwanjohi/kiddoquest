"""
fix_pp2_options.py

Remediates PP2 Math, English, and CRE CSV files in-place.
Delegates to the shared fix_pp1_options logic (identical data patterns).

Usage:
    python fix_pp2_options.py database/csv_imports/pp2_math/ \
                               database/csv_imports/pp2_english/ \
                               database/csv_imports/pp2_cre/
"""

import sys
# Re-use the shared fixer — all PP1/PP2 files have identical structure and defects
from fix_pp1_options import main

if __name__ == "__main__":
    main()
