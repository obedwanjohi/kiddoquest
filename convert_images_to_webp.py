import os
from PIL import Image

TARGET_DIR = os.path.join(os.getcwd(), 'public', 'images', 'm11')

if not os.path.exists(TARGET_DIR):
    print(f"Error: Directory '{TARGET_DIR}' does not exist.")
    exit(1)

converted_count = 0
for filename in os.listdir(TARGET_DIR):
    ext = os.path.splitext(filename)[1].lower()
    if ext in ['.png', '.jpg', '.jpeg']:
        old_path = os.path.join(TARGET_DIR, filename)
        new_filename = os.path.splitext(filename)[0] + '.webp'
        new_path = os.path.join(TARGET_DIR, new_filename)
        
        try:
            im = Image.open(old_path)
            im.save(new_path, 'WEBP', quality=85)
            os.remove(old_path) # Remove original non-webp file
            print(f"Converted: {filename} ---> {new_filename}")
            converted_count += 1
        except Exception as e:
            print(f"Error converting {filename}: {e}")

print(f"\n🎉 DONE! Converted {converted_count} images to .webp format successfully!")
