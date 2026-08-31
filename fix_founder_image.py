import os

path_double = os.path.join(os.getcwd(), 'public', 'images', 'founder.jpg.jpg')
path_clean = os.path.join(os.getcwd(), 'public', 'images', 'founder.jpg')

if os.path.exists(path_double):
    os.rename(path_double, path_clean)
    print("Fixed founder.jpg.jpg ---> founder.jpg")
else:
    print("founder.jpg.jpg not found or already renamed.")
