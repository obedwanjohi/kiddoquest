import os
import csv
import re

CSV_DIR = r"database\csv_imports\play_group_math"
OUTPUT_FILE = r"database\csv_imports\PG_MATH_Master_Prompts.csv"

STYLE_SUFFIX = "2D soft-shaded children's illustration, friendly rounded shapes, vibrant saturated colors, clean simple composition, kid-friendly educational style"

unique_images = set()
output_rows = [["mission_code", "filename", "prompt_text", "background_type", "category"]]

if not os.path.exists(CSV_DIR):
    print(f"Error: Directory {CSV_DIR} not found. Run this from the project root.")
    exit(1)

for filename in os.listdir(CSV_DIR):
    if not filename.endswith('.csv'):
        continue
        
    filepath = os.path.join(CSV_DIR, filename)
    
    # Extract mission code, e.g., PG-MATH-1.1.1
    match = re.match(r"^(PG-MATH-[A-Z0-9\.]+)_", filename)
    mission_code = match.group(1) if match else "UNKNOWN"
    
    try:
        with open(filepath, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(f)
            header = next(reader, None)
            
            for row in reader:
                if len(row) < 5:
                    continue
                    
                question_type = row[0].strip()
                prompt_text = row[2].strip()
                prompt_image = row[3].strip()
                
                if not prompt_image:
                    continue
                    
                if prompt_image in unique_images:
                    continue
                    
                unique_images.add(prompt_image)
                
                bg = 'white'
                category = question_type
                
                if question_type == 'count_objects':
                    category = 'count_object'
                    prompt_lower = prompt_text.lower()
                    if 'in the' in prompt_lower or 'on the' in prompt_lower or 'under the' in prompt_lower:
                        bg = 'scene'
                    else:
                        bg = 'white'
                elif question_type == 'multiple_choice':
                    category = 'multiple_choice'
                    bg = 'white'
                elif question_type == 'speak_repeat':
                    category = 'speak_visual'
                    bg = 'white'
                else:
                    bg = 'white'
                    
                # Clean up prompt text to make it suitable for image generation
                clean_prompt = re.sub(r'^(How many|Count all the|Count all|Count the|Count|Listen to Leo:|Listen:|Which picture shows|Which|Tap the card with|Tap the|Tap|Say out loud:)\s*', '', prompt_text, flags=re.IGNORECASE)
                clean_prompt = clean_prompt.strip(" ?!")
                if clean_prompt:
                    clean_prompt = clean_prompt[0].upper() + clean_prompt[1:]
                
                bg_instruction = ", white background" if bg == 'white' else ""
                
                final_prompt = f"A single {clean_prompt.lower()}, {STYLE_SUFFIX}{bg_instruction}"
                
                output_rows.append([mission_code, prompt_image, final_prompt, bg, category])
    except Exception as e:
        print(f"Error reading {filename}: {e}")

# Save the output CSV
try:
    with open(OUTPUT_FILE, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerows(output_rows)
    print(f"Successfully generated {len(unique_images)} unique prompts!")
    print(f"Saved to: {OUTPUT_FILE}")
except Exception as e:
    print(f"Error writing output file: {e}")
