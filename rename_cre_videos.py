import os
import sys

TARGET_DIR = os.path.join(os.getcwd(), 'public', 'videos')

if not os.path.exists(TARGET_DIR):
    os.makedirs(TARGET_DIR, exist_ok=True)

files = [f for f in os.listdir(TARGET_DIR) if f.lower().endswith(('.mp4', '.webm'))]
files.sort(key=lambda f: os.path.getmtime(os.path.join(TARGET_DIR, f)))

print(f"Found {len(files)} video files in '{TARGET_DIR}'.")

expected_names = [f"cre_m{m}_intro.mp4" for m in range(1, 26)]

renamed_count = 0
for idx, old_filename in enumerate(files):
    if idx >= len(expected_names):
        break
    new_filename = expected_names[idx]
    old_path = os.path.join(TARGET_DIR, old_filename)
    new_path = os.path.join(TARGET_DIR, new_filename)
    
    if old_path != new_path:
        os.rename(old_path, new_path + ".tmp")
        renamed_count += 1

for m in range(1, 26):
    tmp_name = os.path.join(TARGET_DIR, f"cre_m{m}_intro.mp4.tmp")
    final_name = os.path.join(TARGET_DIR, f"cre_m{m}_intro.mp4")
    if os.path.exists(tmp_name):
        os.rename(tmp_name, final_name)

print(f"\n🎉 SUCCESS! Renamed {renamed_count} video files into cre_m1_intro.mp4 through cre_m25_intro.mp4!")
