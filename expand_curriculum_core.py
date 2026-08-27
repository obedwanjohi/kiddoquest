"""
expand_curriculum_core.py  —  core helpers and content database
"""
import csv, re
from pathlib import Path

FIELDNAMES = [
    "question_type","lesson_title","prompt","prompt_image","prompt_audio",
    "correct_answer","option_1","option_2","option_3","option_4",
    "count_emoji_or_image","target_count","target_word",
    "pair_1_left","pair_1_right","pair_2_left","pair_2_right",
    "pair_3_left","pair_3_right",
]

def _blank():
    return {f: "" for f in FIELDNAMES}

def mc(t, p, img, aud, o1, o2, o3):
    r = _blank()
    r.update(question_type="multiple_choice", lesson_title=t, prompt=p,
             prompt_image=img, prompt_audio=aud, correct_answer="1",
             option_1=o1, option_2=o2, option_3=o3)
    return r

def co(t, p, img, aud, o1, o2, o3, emoji, count):
    r = _blank()
    r.update(question_type="count_objects", lesson_title=t, prompt=p,
             prompt_image=img, prompt_audio=aud, correct_answer="1",
             option_1=o1, option_2=o2, option_3=o3,
             count_emoji_or_image=emoji, target_count=str(count))
    return r

def sr(t, p, img, aud, phrase):
    r = _blank()
    r.update(question_type="speak_repeat", lesson_title=t, prompt=p,
             prompt_image=img, prompt_audio=aud, correct_answer="1",
             option_1=phrase, target_word=phrase)
    return r

def mt(t, p, p1l, p1r, p2l, p2r, p3l, p3r):
    r = _blank()
    r.update(question_type="matching", lesson_title=t, prompt=p,
             correct_answer="1",
             pair_1_left=p1l, pair_1_right=p1r,
             pair_2_left=p2l, pair_2_right=p2r,
             pair_3_left=p3l, pair_3_right=p3r)
    return r

def write_csv(path: Path, rows: list):
    with open(path, "w", encoding="utf-8", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=FIELDNAMES, extrasaction="ignore")
        w.writeheader()
        w.writerows(rows)

def read_row_count(path: Path) -> int:
    try:
        with open(path, encoding="utf-8-sig", newline="") as fh:
            return sum(1 for _ in csv.DictReader(fh))
    except Exception:
        return 0

def theme_from_path(path: Path) -> str:
    name = path.stem
    # strip leading ID like "PP1-CRE-1.1.1_" or "PP2-MATH-X.33_"
    name = re.sub(r'^[A-Z0-9\-]+\.[A-Z0-9\-]+\.[A-Za-z0-9]+_', '', name)
    name = re.sub(r'^[A-Z0-9]+\-[A-Z0-9]+\-[A-Z0-9.]+_', '', name)
    return name.replace("_", " ").strip()
