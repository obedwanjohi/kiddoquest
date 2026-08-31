import os

TARGET_DIR = os.path.join(os.getcwd(), 'public', 'videos')

renamed = 0
for filename in os.listdir(TARGET_DIR):
    if filename.lower().endswith(('.mp4', '.webm')):
        old_path = os.path.join(TARGET_DIR, filename)
        # Normalize name: replace spaces with underscores, lowercase, fix double extensions
        clean_name = filename.lower().replace(' ', '_')
        if clean_name.endswith('.mp4.mp4'):
            clean_name = clean_name[:-4]
        
        new_path = os.path.join(TARGET_DIR, clean_name)
        if old_path != new_path:
            os.rename(old_path, new_path)
            print(f"Fixed Video: {filename} ---> {clean_name}")
            renamed += 1

print(f"\n🎉 DONE! Fixed {renamed} video filenames!")
