import os

TARGET_DIR = os.path.join(os.getcwd(), 'public', 'images', 'm11')

renamed = 0
for filename in os.listdir(TARGET_DIR):
    if filename.endswith('.webp.webp'):
        old_path = os.path.join(TARGET_DIR, filename)
        new_filename = filename[:-5] # Strip off the last .webp
        new_path = os.path.join(TARGET_DIR, new_filename)
        os.rename(old_path, new_path)
        print(f"Fixed: {filename} ---> {new_filename}")
        renamed += 1

print(f"\n🎉 DONE! Fixed {renamed} double .webp extensions!")
