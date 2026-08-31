import os
import sys

# Directory containing the raw generated audio files
TARGET_DIR = os.path.join(os.getcwd(), 'public', 'audio', 'm11')

if not os.path.exists(TARGET_DIR):
    print(f"Error: Target directory '{TARGET_DIR}' does not exist.")
    sys.exit(1)

# List all mp3 files in the directory
files = [f for f in os.listdir(TARGET_DIR) if f.lower().endswith('.mp3')]

# Sort files by file modification time so generation sequence is preserved
files.sort(key=lambda f: os.path.getmtime(os.path.join(TARGET_DIR, f)))

print(f"Found {len(files)} MP3 audio files in '{TARGET_DIR}'.")

# Generate expected sequence: m1_q1 to m25_q8
expected_names = []
for m in range(1, 26):
    for q in range(1, 9):
        expected_names.append(f"cre_m{m}_q{q}.mp3")

# Perform atomic rename
renamed_count = 0
for idx, old_filename in enumerate(files):
    if idx >= len(expected_names):
        break
    new_filename = expected_names[idx]
    old_path = os.path.join(TARGET_DIR, old_filename)
    new_path = os.path.join(TARGET_DIR, new_filename)
    
    if old_path != new_path:
        # If new file exists temporarily, use temporary staging
        os.rename(old_path, new_path + ".tmp")
        renamed_count += 1

# Finalize temp extensions
for m in range(1, 26):
    for q in range(1, 9):
        tmp_name = os.path.join(TARGET_DIR, f"cre_m{m}_q{q}.mp3.tmp")
        final_name = os.path.join(TARGET_DIR, f"cre_m{m}_q{q}.mp3")
        if os.path.exists(tmp_name):
            os.rename(tmp_name, final_name)

print(f"\n🎉 SUCCESS! Renamed {renamed_count} audio files into cre_m1_q1.mp3 through cre_m25_q8.mp3!")
