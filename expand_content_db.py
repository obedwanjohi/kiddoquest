"""expand_content_db.py — theme-keyed question templates"""
from expand_curriculum_core import mc, co, sr, mt

# Each generator function takes (title) and returns list of row dicts.
# The framework picks the best matching generator by keyword scanning the theme.

def _gen_letter(title, letter, words, emoji1, emoji2):
    L = letter.upper()
    w1, w2 = words[0].capitalize(), words[1].capitalize()
    e1, e2 = emoji1, emoji2
    slug = letter.lower()
    return [
        mc(title, f"Which letter makes the /{slug}/ sound?", f"letter_{slug}.webp", "audio_q1.mp3", L, "B" if L!="B" else "C", "D" if L!="D" else "E"),
        mc(title, f"Tap the UPPERCASE letter {L}!", f"upper_{slug}.webp", "audio_q2.mp3", L, "B" if L!="B" else "C", "M" if L!="M" else "N"),
        mc(title, f"Tap the lowercase letter {slug}!", f"lower_{slug}.webp", "audio_q3.mp3", slug, "b" if slug!="b" else "c", "d" if slug!="d" else "e"),
        mc(title, f"Which picture starts with /{slug}/? Touch it!", f"{slug}pic1.webp", "audio_q4.mp3", w1, "Dog" if w1!="Dog" else "Cat", "Sun" if w1!="Sun" else "Hat"),
        mc(title, f"Which animal / object starts with the letter {L}?", f"{slug}pic2.webp", "audio_q5.mp3", w2, "Ball" if w2!="Ball" else "Tree", "Fish" if w2!="Fish" else "Bird"),
        mc(title, f"Does {w1} start with /{slug}/?", f"{slug}_yes.webp", "audio_q6.mp3", "Yes", "No", "Maybe"),
        mc(title, f"Tap the letter that comes AFTER {L} in the alphabet!", f"after_{slug}.webp", "audio_q7.mp3",
           chr(ord(L)+1) if ord(L)<ord('Z') else "A",
           chr(ord(L)+2) if ord(L)<ord('Y') else "B",
           chr(ord(L)-1) if ord(L)>ord('A') else "Z"),
        sr(title, f"Say out loud: /{slug}/ /{slug}/ {w1.upper()}!", f"{slug}_speak.webp", "audio_q8.mp3", f"/{slug}/ /{slug}/ {w1}"),
        sr(title, f"Say out loud clearly: {L} IS FOR {w1.upper()}!", f"a4_{slug}.webp", "audio_q9.mp3", f"{L} Is For {w1}"),
        sr(title, f"Say: {w2.upper()} STARTS WITH {L}!", f"b_{slug}.webp", "audio_q10.mp3", f"{w2} Starts With {L}"),
        mt(title, f"Match uppercase {L} to lowercase {slug}!", L, slug, "B" if L!="B" else "C", "b" if slug!="b" else "c", "D" if L!="D" else "E", "d" if slug!="d" else "e"),
        mt(title, f"Match /{slug}/ pictures to letter {L}!", w1, L, w2, L, "Ant" if w1!="Ant" else "Apple", L),
        mt(title, "Match letter sounds!", f"/{slug}/", w1, f"/b/", "Ball", f"/c/", "Cat"),
    ]

LETTER_DATA = {
    "A": ("A", ["Apple","Ant"], "🍎", "🐜"),
    "B": ("B", ["Ball","Bear"], "⚽", "🐻"),
    "C": ("C", ["Cat","Car"], "🐱", "🚗"),
    "D": ("D", ["Dog","Duck"], "🐕", "🦆"),
    "E": ("E", ["Egg","Elephant"], "🥚", "🐘"),
    "F": ("F", ["Fish","Frog"], "🐟", "🐸"),
    "G": ("G", ["Goat","Giraffe"], "🐐", "🦒"),
    "H": ("H", ["Hat","House"], "🎩", "🏠"),
    "I": ("I", ["Igloo","Insect"], "🏔", "🐜"),
    "J": ("J", ["Jug","Jelly"], "🫙", "🍮"),
    "K": ("K", ["Kite","Kangaroo"], "🪁", "🦘"),
    "L": ("L", ["Lion","Leaf"], "🦁", "🍃"),
    "M": ("M", ["Monkey","Moon"], "🐒", "🌙"),
    "N": ("N", ["Nest","Net"], "🪹", "🕸"),
    "O": ("O", ["Owl","Orange"], "🦉", "🍊"),
    "P": ("P", ["Pen","Pig"], "✏", "🐷"),
    "Q": ("Q", ["Queen","Quill"], "👑", "✒"),
    "R": ("R", ["Rose","Ring"], "🌹", "💍"),
    "S": ("S", ["Sun","Star"], "☀", "⭐"),
    "T": ("T", ["Tree","Turtle"], "🌳", "🐢"),
    "U": ("U", ["Umbrella","Urn"], "☂", "🏺"),
    "V": ("V", ["Van","Vase"], "🚐", "💐"),
    "W": ("W", ["Water","Wheel"], "💧", "⚙"),
    "X": ("X", ["Xylophone","Xray"], "🎵", "🩻"),
    "Y": ("Y", ["Yoyo","Yak"], "🪀", "🐃"),
    "Z": ("Z", ["Zebra","Zoo"], "🦓", "🦁"),
}

def gen_letter_lesson(title, theme):
    for ltr, data in LETTER_DATA.items():
        if f"Letter {ltr}" in theme or f"Letter_{ltr}" in theme or theme.upper().startswith(f"LETTER {ltr}"):
            return _gen_letter(title, data[0], data[1], data[2], data[3])
    return None

# ---------------------------------------------------------------------------
# CVC Word families
# ---------------------------------------------------------------------------
CVC_FAMILIES = {
    "at": (["Cat","Hat","Mat","Bat","Rat"], "🐱","🎩","🧹"),
    "an": (["Man","Fan","Can","Pan","Van"], "🧑","💨","🥫"),
    "ig": (["Pig","Dig","Big","Fig","Wig"], "🐷","⛏","📦"),
    "op": (["Hop","Pop","Top","Mop","Cop"], "🐸","🍾","🕺"),
    "ug": (["Bug","Rug","Mug","Hug","Tug"], "🐛","🪡","☕"),
    "en": (["Hen","Pen","Ten","Den","Men"], "🐔","✏","🔟"),
    "in": (["Pin","Bin","Win","Tin","Fin"], "📌","🗑","🏆"),
    "og": (["Dog","Log","Fog","Hog","Jog"], "🐕","🪵","🌫"),
    "un": (["Sun","Run","Fun","Bun","Gun"], "☀","🏃","🎉"),
    "ed": (["Bed","Red","Fed","Led","Wed"], "🛏","🔴","🍽"),
}

def gen_cvc_lesson(title, theme):
    t = theme.lower()
    for fam, data in CVC_FAMILIES.items():
        if fam in t:
            words, e1, e2, e3 = data[0], data[1], data[2], data[3]
            w1,w2,w3,w4,w5 = words
            return [
                mc(title, f"Blend /c/ + /a/ + /t/? (think -{fam} words)", f"cvc_{fam}1.webp", "audio_q1.mp3", w1, "Dog","Sun"),
                mc(title, f"Which word rhymes with {w1}?", f"cvc_{fam}2.webp", "audio_q2.mp3", w2, "Bed","Cup"),
                mc(title, f"Touch the picture that rhymes with {w2}!", f"cvc_{fam}3.webp", "audio_q3.mp3", w3, "Frog","Bird"),
                mc(title, f"Which word ends in /{fam}/?", f"cvc_{fam}4.webp", "audio_q4.mp3", w4, "Tree","Fish"),
                mc(title, f"Touch the -{fam} word!", f"cvc_{fam}5.webp", "audio_q5.mp3", w5, "Jump","Rain"),
                mc(title, f"Spell out {w1} — how many letters?", f"cvc_spell.webp", "audio_q6.mp3", "3","4","5"),
                sr(title, f"Say out loud: {w1.upper()} — {w2.upper()} — {w3.upper()}!", f"cvc_say.webp", "audio_q7.mp3", f"{w1} {w2} {w3}"),
                sr(title, f"Say: THEY ALL RHYME WITH -{fam.upper()}!", f"cvc_rhyme.webp", "audio_q8.mp3", f"They All Rhyme With -{fam}"),
                sr(title, f"Spell it out: {'-'.join(w1.upper())} spells {w1.upper()}!", f"cvc_spell2.webp", "audio_q9.mp3", f"{'-'.join(w1.upper())} Spells {w1}"),
                mt(title, f"Match -{fam} words to pictures!", w1, e1, w2, e2, w3, e3),
                mt(title, "Match rhyming word pairs!", w1, w2, w3, w4, w5, w1),
                mt(title, "Match word to its meaning!", w1, "Pet animal" if "Cat" in words or "Dog" in words else f"A {w1}", w2, f"A {w2}", w3, f"A {w3}"),
            ]
    return None

# ---------------------------------------------------------------------------
# Math — Numbers / Counting
# ---------------------------------------------------------------------------
def gen_number_lesson(title, theme):
    t = theme.lower()
    # detect number ranges
    nums = re.findall(r'\d+', theme)
    if not nums:
        return None
    lo = int(nums[0])
    hi = int(nums[-1]) if len(nums) > 1 else lo + 5
    mid = (lo + hi) // 2
    return [
        mc(title, f"Tap the number {lo}!", f"num{lo}.webp", "audio_q1.mp3", str(lo), str(lo+1), str(lo+2)),
        mc(title, f"Which number comes AFTER {mid}?", f"after{mid}.webp", "audio_q2.mp3", str(mid+1), str(mid), str(mid+2)),
        mc(title, f"Which number comes BEFORE {hi}?", f"before{hi}.webp", "audio_q3.mp3", str(hi-1), str(hi), str(hi+1)),
        mc(title, f"Tap the number {hi}!", f"num{hi}.webp", "audio_q4.mp3", str(hi), str(hi-1), str(hi-2)),
        mc(title, f"Put these numbers in order. What comes first: {lo}, {mid}, or {hi}?", "order.webp", "audio_q5.mp3", str(lo), str(mid), str(hi)),
        co(title, f"Count the objects! How many are there?", "count1.webp", "audio_q6.mp3", str(lo+1), str(lo), str(lo+2), "⭐", lo+1),
        co(title, f"Count carefully! How many items do you see?", "count2.webp", "audio_q7.mp3", str(mid), str(mid-1), str(mid+1), "🔵", mid),
        co(title, f"How many total?", "count3.webp", "audio_q8.mp3", str(hi), str(hi-1), str(hi+1), "🌟", hi),
        sr(title, f"Count out loud: {', '.join(str(n) for n in range(lo, min(lo+6, hi+2)))}!", "count_say.webp", "audio_q9.mp3", " ".join(str(n) for n in range(lo, min(lo+6, hi+2)))),
        sr(title, f"Say the number {hi} out loud!", f"say{hi}.webp", "audio_q10.mp3", str(hi)),
        sr(title, f"Say: I CAN COUNT FROM {lo} TO {hi}!", "count_proud.webp", "audio_q11.mp3", f"I Can Count From {lo} To {hi}"),
        mt(title, "Match number to its name!", str(lo), _num_word(lo), str(mid), _num_word(mid), str(hi), _num_word(hi)),
        mt(title, "Match number to how many objects!", str(lo+1), f"{lo+1} items", str(mid), f"{mid} items", str(hi-1), f"{hi-1} items"),
        mt(title, "Match before and after!", str(mid-1), f"Before {mid}", str(mid+1), f"After {mid}", str(lo), f"First number"),
    ]

NUM_WORDS = ["Zero","One","Two","Three","Four","Five","Six","Seven","Eight","Nine","Ten",
             "Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen",
             "Eighteen","Nineteen","Twenty"]
def _num_word(n):
    if 0 <= n < len(NUM_WORDS):
        return NUM_WORDS[n]
    return str(n)

# ---------------------------------------------------------------------------
# Math — Addition / Subtraction
# ---------------------------------------------------------------------------
def gen_addition_lesson(title, theme):
    return [
        mc(title, "What is 2 + 1?", "add1.webp", "audio_q1.mp3", "3","2","4"),
        mc(title, "What is 3 + 2?", "add2.webp", "audio_q2.mp3", "5","4","6"),
        mc(title, "You have 4 apples and get 1 more. How many total?", "add3.webp", "audio_q3.mp3", "5","4","6"),
        mc(title, "What is 5 + 3?", "add4.webp", "audio_q4.mp3", "8","7","9"),
        mc(title, "What is 6 + 2?", "add5.webp", "audio_q5.mp3", "8","7","9"),
        mc(title, "4 birds + 3 birds = how many birds?", "add6.webp", "audio_q6.mp3", "7","6","8"),
        co(title, "Count all the stars! How many?", "stars.webp", "audio_q7.mp3", "6","5","7","⭐",6),
        co(title, "Count all the dots! How many?", "dots.webp", "audio_q8.mp3", "8","7","9","🔵",8),
        sr(title, "Say out loud: TWO PLUS TWO EQUALS FOUR!", "add_say.webp", "audio_q9.mp3", "Two Plus Two Equals Four"),
        sr(title, "Say: I LOVE ADDING NUMBERS!", "add_love.webp", "audio_q10.mp3", "I Love Adding Numbers"),
        sr(title, "Count on: FIVE, SIX, SEVEN, EIGHT!", "count_on.webp", "audio_q11.mp3", "Five Six Seven Eight"),
        mt(title, "Match addition to answer!", "2 + 3", "5", "4 + 1", "5", "3 + 3", "6"),
        mt(title, "Match addition sentences!", "1 + 4", "5", "5 + 2", "7", "6 + 2", "8"),
        mt(title, "Match story to sum!", "2 cats + 3 cats", "5", "4 dogs + 2 dogs", "6", "3 birds + 4 birds", "7"),
    ]

def gen_subtraction_lesson(title, theme):
    return [
        mc(title, "What is 5 - 2?", "sub1.webp", "audio_q1.mp3", "3","2","4"),
        mc(title, "What is 8 - 3?", "sub2.webp", "audio_q2.mp3", "5","4","6"),
        mc(title, "You have 6 sweets and eat 2. How many are left?", "sub3.webp", "audio_q3.mp3", "4","3","5"),
        mc(title, "What is 10 - 4?", "sub4.webp", "audio_q4.mp3", "6","5","7"),
        mc(title, "What is 9 - 3?", "sub5.webp", "audio_q5.mp3", "6","5","7"),
        mc(title, "7 birds - 2 birds = how many birds left?", "sub6.webp", "audio_q6.mp3", "5","4","6"),
        co(title, "5 apples take away 2. How many remain?", "sub_co.webp", "audio_q7.mp3", "3","2","4","🍎",3),
        co(title, "8 stars minus 3. How many stars left?", "star_sub.webp", "audio_q8.mp3", "5","4","6","⭐",5),
        sr(title, "Say out loud: TEN MINUS FOUR EQUALS SIX!", "sub_say.webp", "audio_q9.mp3", "Ten Minus Four Equals Six"),
        sr(title, "Say: SUBTRACTION TAKES AWAY!", "sub_away.webp", "audio_q10.mp3", "Subtraction Takes Away"),
        sr(title, "Count back: TEN, NINE, EIGHT, SEVEN!", "count_back.webp", "audio_q11.mp3", "Ten Nine Eight Seven"),
        mt(title, "Match subtraction to answer!", "5 - 2", "3", "8 - 3", "5", "10 - 4", "6"),
        mt(title, "Match take-away sentences!", "7 - 1", "6", "9 - 3", "6", "6 - 2", "4"),
        mt(title, "Match story to difference!", "5 sweets - 2 eaten", "3 left", "8 birds - 3 flew", "5 left", "10 fish - 4 gone", "6 left"),
    ]

# ---------------------------------------------------------------------------
# Math — Shapes
# ---------------------------------------------------------------------------
SHAPE_DATA = {
    "sphere": ("Sphere","Ball","Round","Rolls","⚽"),
    "cube": ("Cube","Box","Square sides","Stacks","🎲"),
    "cone": ("Cone","Party Hat","Pointy top","1 tip","🎉"),
    "cylinder": ("Cylinder","Can","Round ends","Rolls sideways","🥫"),
    "circle": ("Circle","Wheel","No corners","Round","⭕"),
    "square": ("Square","Window","4 equal sides","4 corners","🔲"),
    "triangle": ("Triangle","Sail","3 sides","3 corners","🔺"),
    "rectangle": ("Rectangle","Book","4 sides","Long shape","📖"),
}

def gen_shape_lesson(title, theme):
    t = theme.lower()
    for key, data in SHAPE_DATA.items():
        if key in t:
            name, obj, prop1, prop2, emoji = data
            other1 = "Cube" if name!="Cube" else "Sphere"
            other2 = "Cone" if name!="Cone" else "Cylinder"
            return [
                mc(title, f"Which 3D shape looks like a {obj}?", f"{key}1.webp", "audio_q1.mp3", name, other1, other2),
                mc(title, f"A {name} has what special property?", f"{key}2.webp", "audio_q2.mp3", prop1, "Sharp Points","Flat Only"),
                mc(title, f"Touch the {name} in the picture!", f"{key}3.webp", "audio_q3.mp3", name, other1, "Pyramid"),
                mc(title, f"Which shape does a {obj} look like?", f"{key}4.webp", "audio_q4.mp3", name, "Cube","Triangle"),
                mc(title, f"A {name} can {prop2}. True or false?", f"{key}5.webp", "audio_q5.mp3", "True","False","Maybe"),
                mc(title, f"How many flat faces does a {name} have?", f"{key}6.webp", "audio_q6.mp3", "2" if key in ("cylinder","cone") else "6" if key=="cube" else "0", "1","3"),
                co(title, f"Count the {name} shapes!", f"{key}_count.webp", "audio_q7.mp3", "4","3","5", emoji, 4),
                sr(title, f"Say: A {name.upper()} LOOKS LIKE A {obj.upper()}!", f"{key}_say.webp", "audio_q8.mp3", f"A {name} Looks Like A {obj}"),
                sr(title, f"Say out loud: I KNOW MY SHAPES!", "shapes_say.webp", "audio_q9.mp3", "I Know My Shapes"),
                mt(title, "Match shape to real object!", name, obj, other1, "Dice" if other1=="Cube" else "Ball", other2, "Ice Cream" if other2=="Cone" else "Tin"),
                mt(title, "Match shape to property!", name, prop1, other1, "6 square faces" if other1=="Cube" else "Round", other2, "1 flat circle" if other2=="Cone" else "2 circles"),
                mt(title, "Match shape to description!", name, prop2, other1, "Stacks neatly" if other1=="Cube" else "Rolls", other2, "Rolls sideways" if other2=="Cylinder" else "Pointy top"),
            ]
    return None

# ---------------------------------------------------------------------------
# Math — Measurement / Patterns / Spatial / Money
# ---------------------------------------------------------------------------
def gen_measurement_lesson(title, theme):
    t = theme.lower()
    if "long" in t or "short" in t or "length" in t:
        return [
            mc(title,"Which is LONGER — a giraffe or a cat?","long1.webp","audio_q1.mp3","Giraffe","Cat","Mouse"),
            mc(title,"Touch the SHORTER pencil!","short1.webp","audio_q2.mp3","Short Pencil","Long Pencil","Ruler"),
            mc(title,"A snake is LONG. A worm is?","long2.webp","audio_q3.mp3","Short","Long","Tall"),
            mc(title,"Which ruler measures LENGTH?","ruler.webp","audio_q4.mp3","Ruler","Scale","Clock"),
            mc(title,"Is a bus LONGER or SHORTER than a bicycle?","bus.webp","audio_q5.mp3","Longer","Shorter","Same"),
            sr(title,"Say: THE GIRAFFE IS TALL AND LONG!","giraffe.webp","audio_q6.mp3","The Giraffe Is Tall And Long"),
            sr(title,"Say: SHORT AND LONG — OPPOSITES!","opp.webp","audio_q7.mp3","Short And Long Opposites"),
            mt(title,"Match to length!","Giraffe","Long","Ant","Short","Bus","Long"),
            mt(title,"Match opposites!","Long","Short","Tall","Short","Big","Small"),
        ]
    if "heavy" in t or "light" in t or "weight" in t or "mass" in t:
        return [
            mc(title,"Which is HEAVIER — an elephant or a feather?","heavy1.webp","audio_q1.mp3","Elephant","Feather","Leaf"),
            mc(title,"Touch the LIGHTER object!","light1.webp","audio_q2.mp3","Feather","Rock","Book"),
            mc(title,"A rock is HEAVY. A balloon is?","rock.webp","audio_q3.mp3","Light","Heavy","Same"),
            mc(title,"What do we use to measure weight?","scale.webp","audio_q4.mp3","Scale","Ruler","Clock"),
            mc(title,"Is a book HEAVIER than a pencil?","book.webp","audio_q5.mp3","Yes","No","Same"),
            sr(title,"Say: HEAVY AND LIGHT ARE OPPOSITES!","opp.webp","audio_q6.mp3","Heavy And Light Are Opposites"),
            sr(title,"Say: THE ELEPHANT IS HEAVY!","eleph.webp","audio_q7.mp3","The Elephant Is Heavy"),
            mt(title,"Match heavy or light!","Elephant","Heavy","Feather","Light","Stone","Heavy"),
            mt(title,"Match object to weight!","Book","Heavier","Pencil","Lighter","Rock","Heaviest"),
        ]
    if "full" in t or "empty" in t or "capacity" in t:
        return [
            mc(title,"Which cup is FULL?","full1.webp","audio_q1.mp3","Full Cup","Empty Cup","Half Cup"),
            mc(title,"Touch the EMPTY glass!","empty1.webp","audio_q2.mp3","Empty Glass","Full Glass","Broken Glass"),
            mc(title,"A full bottle has MORE or LESS water than an empty one?","bottle.webp","audio_q3.mp3","More","Less","Same"),
            mc(title,"Which shows HALF FULL?","half.webp","audio_q4.mp3","Half Full","Empty","Overflowing"),
            sr(title,"Say: FULL EMPTY HALF — I KNOW CAPACITY!","cap.webp","audio_q5.mp3","Full Empty Half I Know Capacity"),
            sr(title,"Say: THE BUCKET IS FULL OF WATER!","bucket.webp","audio_q6.mp3","The Bucket Is Full Of Water"),
            mt(title,"Match container state!","Full","Has Water","Empty","No Water","Half","Some Water"),
            mt(title,"Match opposites!","Full","Empty","Heavy","Light","Long","Short"),
        ]
    if "day" in t or "week" in t or "time" in t or "morning" in t or "night" in t:
        return [
            mc(title,"How many days are in a week?","days.webp","audio_q1.mp3","7","5","10"),
            mc(title,"Which day comes after Monday?","monday.webp","audio_q2.mp3","Tuesday","Wednesday","Sunday"),
            mc(title,"Which day comes before Friday?","friday.webp","audio_q3.mp3","Thursday","Saturday","Monday"),
            mc(title,"What do we say in the morning?","morning.webp","audio_q4.mp3","Good Morning","Good Night","Hello Night"),
            mc(title,"Which day do we go to church?","church.webp","audio_q5.mp3","Sunday","Monday","Friday"),
            sr(title,"Say the days: MONDAY TUESDAY WEDNESDAY!","days_say.webp","audio_q6.mp3","Monday Tuesday Wednesday"),
            sr(title,"Say: THERE ARE SEVEN DAYS IN A WEEK!","seven.webp","audio_q7.mp3","There Are Seven Days In A Week"),
            mt(title,"Match days in order!","1st Day","Monday","2nd Day","Tuesday","Last Day","Sunday"),
            mt(title,"Match time of day!","Morning","Wake Up","Afternoon","Play Time","Night","Sleep Time"),
        ]
    if "coin" in t or "money" in t or "shilling" in t or "market" in t or "buy" in t:
        return [
            mc(title,"What is the Kenyan currency called?","money1.webp","audio_q1.mp3","Shilling","Dollar","Pound"),
            mc(title,"If an apple costs 5 shillings and you have 10, how much change?","apple.webp","audio_q2.mp3","5","10","0"),
            mc(title,"Which coin has the highest value — 1, 5, or 10 shillings?","coins.webp","audio_q3.mp3","10","5","1"),
            mc(title,"Touch the 5 shilling coin!","coin5.webp","audio_q4.mp3","5 Shillings","1 Shilling","10 Shillings"),
            mc(title,"If you spend 3 shillings from 10, how many left?","spend.webp","audio_q5.mp3","7","6","8"),
            sr(title,"Say: I CAN BUY AND PAY WITH SHILLINGS!","pay.webp","audio_q6.mp3","I Can Buy And Pay With Shillings"),
            sr(title,"Say: TEN SHILLINGS MINUS FIVE IS FIVE!","change.webp","audio_q7.mp3","Ten Shillings Minus Five Is Five"),
            mt(title,"Match coin to value!","1 Shilling","1","5 Shillings","5","10 Shillings","10"),
            mt(title,"Match price to item!","Apple","5 Shillings","Banana","3 Shillings","Orange","4 Shillings"),
        ]
    # Generic measurement fallback
    return [
        mc(title,"What tool do we use to measure LENGTH?","ruler.webp","audio_q1.mp3","Ruler","Scale","Cup"),
        mc(title,"What tool do we use to measure WEIGHT?","scale.webp","audio_q2.mp3","Scale","Ruler","Clock"),
        mc(title,"What tool do we use to measure TIME?","clock.webp","audio_q3.mp3","Clock","Ruler","Scale"),
        mc(title,"Which is TALLER — a tree or a flower?","tall.webp","audio_q4.mp3","Tree","Flower","Grass"),
        sr(title,"Say: I CAN MEASURE LENGTH WEIGHT AND TIME!","meas.webp","audio_q5.mp3","I Can Measure Length Weight And Time"),
        mt(title,"Match measurement tools!","Ruler","Length","Scale","Weight","Clock","Time"),
    ]

def gen_pattern_lesson(title, theme):
    return [
        mc(title,"What comes next: Red Blue Red Blue ___?","pat1.webp","audio_q1.mp3","Red","Blue","Green"),
        mc(title,"What comes next: Circle Square Circle Square ___?","pat2.webp","audio_q2.mp3","Circle","Square","Triangle"),
        mc(title,"Complete: Apple Banana Apple Banana ___?","pat3.webp","audio_q3.mp3","Apple","Banana","Orange"),
        mc(title,"Which pattern is AB AB?","pat4.webp","audio_q4.mp3","Red Blue Red Blue","Red Red Blue","Blue Red Red"),
        mc(title,"What is the RULE of this pattern: 1 2 1 2?","pat5.webp","audio_q5.mp3","AB Pattern","AAB Pattern","ABC Pattern"),
        mc(title,"What comes next: 2 4 6 ___?","pat6.webp","audio_q6.mp3","8","7","9"),
        sr(title,"Say: RED BLUE RED BLUE — AB PATTERN!","pat_say.webp","audio_q7.mp3","Red Blue Red Blue AB Pattern"),
        sr(title,"Say: I CAN SPOT A PATTERN!","spot.webp","audio_q8.mp3","I Can Spot A Pattern"),
        mt(title,"Match pattern type!","AB","Red Blue Red Blue","AAB","Red Red Blue","AABB","Red Red Blue Blue"),
        mt(title,"Match what comes next!","Red Blue ___","Red","1 2 1 ___","2","A B A ___","B"),
        mt(title,"Match pattern to rule!","AB Pattern","Repeats 2","AAB Pattern","Two then one","Growing","Gets bigger"),
    ]

def gen_spatial_lesson(title, theme):
    t = theme.lower()
    if "front" in t or "behind" in t:
        pairs = [("In Front","Before","Behind","After","Next To","Beside")]
        opts = [("In Front Of","Behind","Inside"),("Behind","In Front Of","Under"),("Next To","Far From","Above")]
    elif "above" in t or "below" in t:
        opts = [("Above","Below","Inside"),("Below","Above","Beside"),("On Top","Under","Next To")]
        pairs = [("Above","Higher","Below","Lower","On Top","Highest")]
    elif "inside" in t or "outside" in t:
        opts = [("Inside","Outside","Above"),("Outside","Inside","Below"),("In","Out","Up")]
        pairs = [("Inside","In the Box","Outside","Not in Box","Beside","Next To")]
    elif "left" in t or "right" in t:
        opts = [("Left","Right","Up"),("Right","Left","Down"),("Left Hand","Right Hand","Both Hands")]
        pairs = [("Left","This Side","Right","Other Side","Both","Two Hands")]
    else:
        opts = [("Near","Far","Beside"),("Far","Near","Above"),("On","Under","Beside")]
        pairs = [("Near","Close By","Far","Far Away","Beside","Next To")]
    o = opts[0]
    p = pairs[0]
    return [
        mc(title,f"The cat is ___ the box. Which word fits?","pos1.webp","audio_q1.mp3",o[0],o[1],o[2]),
        mc(title,f"Touch the object that is {o[0]} the table!","pos2.webp","audio_q2.mp3",o[0]+" Object",o[1]+" Object","On Table"),
        mc(title,"Which word means the OPPOSITE of ABOVE?","opp.webp","audio_q3.mp3","Below","Above","Beside"),
        mc(title,"Which word means the OPPOSITE of INSIDE?","opp2.webp","audio_q4.mp3","Outside","Inside","Above"),
        mc(title,"The bird is flying ABOVE the tree. Where is the bird?","bird.webp","audio_q5.mp3","Higher Up","Lower Down","Inside Tree"),
        sr(title,f"Say: {o[0].upper()} AND {o[1].upper()} ARE OPPOSITES!","opp_say.webp","audio_q6.mp3",f"{o[0]} And {o[1]} Are Opposites"),
        sr(title,"Say: I KNOW POSITION WORDS!","pos_say.webp","audio_q7.mp3","I Know Position Words"),
        mt(title,"Match position words!",p[0],p[1],p[2],p[3],p[4],p[5]),
        mt(title,"Match opposites!","Above","Below","Inside","Outside","Left","Right"),
        mt(title,"Match position to description!","Above","Higher up","Below","Lower down","Beside","Next to"),
    ]

# ---------------------------------------------------------------------------
# CRE generic themes
# ---------------------------------------------------------------------------
def gen_cre_generic(title, theme):
    t = theme.lower()
    if "creation" in t or "god made" in t or "creator" in t:
        return [
            mc(title,"Who created the whole world?","creat1.webp","audio_q1.mp3","God","Nobody","A Machine"),
            mc(title,"God made the sun. What does the sun give us?","sun.webp","audio_q2.mp3","Light","Rain","Wind"),
            mc(title,"God made animals. Which animal lives in water?","water.webp","audio_q3.mp3","Fish","Lion","Eagle"),
            mc(title,"What colour did God make most leaves?","leaf.webp","audio_q4.mp3","Green","Blue","Red"),
            mc(title,"God made you. Does He love you?","love.webp","audio_q5.mp3","Yes Always","No","Sometimes"),
            mc(title,"God created the moon for what time?","moon.webp","audio_q6.mp3","Night","Day","Morning"),
            sr(title,"Say: GOD CREATED EVERYTHING!","creat_say.webp","audio_q7.mp3","God Created Everything"),
            sr(title,"Say: THANK YOU GOD FOR YOUR CREATION!","thanks.webp","audio_q8.mp3","Thank You God For Your Creation"),
            mt(title,"Match creation to where it lives!","Sun","Sky","Fish","Water","Trees","Land"),
            mt(title,"Match God's creation to what it does!","Sun","Gives Light","Rain","Waters Plants","Birds","Sing"),
            mt(title,"Match who made what!","God","Sun","God","Animals","God","Me"),
        ]
    if "jesus" in t or "christ" in t:
        return [
            mc(title,"Who is Jesus?","jesus1.webp","audio_q1.mp3","Son of God","A King","A Teacher"),
            mc(title,"Where was Jesus born?","born.webp","audio_q2.mp3","Bethlehem","Nazareth","Jerusalem"),
            mc(title,"What did Jesus do when sick people came to Him?","heal.webp","audio_q3.mp3","Healed Them","Sent Away","Ignored"),
            mc(title,"Jesus loves all children. True or false?","love.webp","audio_q4.mp3","True","False","Maybe"),
            mc(title,"Who is the Good Shepherd?","shep.webp","audio_q5.mp3","Jesus","David","Moses"),
            mc(title,"What did Jesus say to calm the storm?","storm.webp","audio_q6.mp3","Peace Be Still","Blow Harder","Go Away"),
            sr(title,"Say: JESUS LOVES ME!","love_say.webp","audio_q7.mp3","Jesus Loves Me"),
            sr(title,"Say: JESUS IS MY LORD AND SAVIOR!","lord.webp","audio_q8.mp3","Jesus Is My Lord And Savior"),
            mt(title,"Match Jesus miracles!","Fed 5000","5 Loaves","Calmed Storm","Peace Be Still","Healed Sick","Made Well"),
            mt(title,"Match Jesus facts!","Born In","Bethlehem","Grew Up","Nazareth","Died And Rose","For Us"),
        ]
    if "prayer" in t or "pray" in t:
        return [
            mc(title,"When can we talk to God in prayer?","pray1.webp","audio_q1.mp3","Anytime","Only Sunday","Never"),
            mc(title,"What do we fold when we pray?","hands.webp","audio_q2.mp3","Hands","Legs","Arms"),
            mc(title,"What do we say at the end of prayer?","amen.webp","audio_q3.mp3","Amen","Goodbye","Okay"),
            mc(title,"How many times a day did Daniel pray?","daniel.webp","audio_q4.mp3","3","1","10"),
            mc(title,"Does God hear our prayers?","hear.webp","audio_q5.mp3","Yes Always","No","Sometimes"),
            sr(title,"Say: GOD HEARS MY PRAYERS!","pray_say.webp","audio_q6.mp3","God Hears My Prayers"),
            sr(title,"Say: I TALK TO GOD EVERY DAY!","daily.webp","audio_q7.mp3","I Talk To God Every Day"),
            mt(title,"Match prayer times!","Morning","Morning Prayer","Meals","Table Grace","Bedtime","Night Prayer"),
            mt(title,"Match prayer actions!","Fold Hands","Bow Head","Say Thanks","Gratitude","Say Amen","End Prayer"),
        ]
    if "bible" in t or "scripture" in t or "word" in t:
        return [
            mc(title,"What is the Holy Bible?","bible1.webp","audio_q1.mp3","Gods Word","A Storybook","A Magazine"),
            mc(title,"How many main parts does the Bible have?","parts.webp","audio_q2.mp3","2","3","1"),
            mc(title,"What should we do with God's Word?","do.webp","audio_q3.mp3","Read & Obey","Ignore","Throw Away"),
            mc(title,"The Bible says God's Word is a lamp to our what?","lamp.webp","audio_q4.mp3","Feet","Head","House"),
            mc(title,"Who inspired the writers of the Bible?","inspire.webp","audio_q5.mp3","God","A Teacher","Nobody"),
            sr(title,"Say: THE BIBLE IS GODS WORD!","bible_say.webp","audio_q6.mp3","The Bible Is Gods Word"),
            sr(title,"Say: I LOVE READING THE BIBLE!","love.webp","audio_q7.mp3","I Love Reading The Bible"),
            mt(title,"Match Bible facts!","Holy Bible","Gods Word","Old Testament","Before Jesus","New Testament","Life of Jesus"),
            mt(title,"Match what we do with the Bible!","Read It","Daily","Obey It","Always","Share It","With Others"),
        ]
    if "church" in t or "worship" in t or "praise" in t or "sing" in t:
        return [
            mc(title,"Where do Christians go to worship God together?","church1.webp","audio_q1.mp3","Church","Market","School"),
            mc(title,"What day do most Christians worship at church?","day.webp","audio_q2.mp3","Sunday","Monday","Friday"),
            mc(title,"What do we do at church?","do.webp","audio_q3.mp3","Worship God","Buy Things","Play Sports"),
            mc(title,"What is God's house called?","house.webp","audio_q4.mp3","Church","Shop","School"),
            mc(title,"How should we behave in church?","behave.webp","audio_q5.mp3","Quietly & Respectfully","Loudly","Running"),
            sr(title,"Say: I LOVE GOING TO CHURCH!","church_say.webp","audio_q6.mp3","I Love Going To Church"),
            sr(title,"Say: PRAISE THE LORD WITH JOY!","praise.webp","audio_q7.mp3","Praise The Lord With Joy"),
            mt(title,"Match church activities!","Church","House of God","Sunday","Worship Day","Believers","Gods Family"),
            mt(title,"Match worship actions!","Sing","Praise","Pray","Talk to God","Give","Offering"),
        ]
    if "share" in t or "kind" in t or "help" in t or "love" in t or "value" in t:
        return [
            mc(title,"What does God want us to do for others?","kind1.webp","audio_q1.mp3","Be Kind","Be Mean","Ignore"),
            mc(title,"When a friend is sad what should we do?","sad.webp","audio_q2.mp3","Comfort Them","Laugh","Ignore"),
            mc(title,"What does sharing show?","share.webp","audio_q3.mp3","Love & Care","Selfishness","Greed"),
            mc(title,"The Bible says love your what?","neigh.webp","audio_q4.mp3","Neighbour","Enemy","Only Yourself"),
            mc(title,"God is pleased when we are?","please.webp","audio_q5.mp3","Kind","Mean","Rude"),
            sr(title,"Say: I WILL BE KIND TO EVERYONE!","kind_say.webp","audio_q6.mp3","I Will Be Kind To Everyone"),
            sr(title,"Say: SHARING IS CARING!","share.webp","audio_q7.mp3","Sharing Is Caring"),
            mt(title,"Match Christian values!","Love","God & People","Kindness","To All","Prayer","Daily Talk"),
            mt(title,"Match kind actions!","Share Toys","Be Generous","Help Friends","Be Kind","Say Sorry","Forgive"),
        ]
    # Generic CRE fallback
    return [
        mc(title,"Who made everything we see?","god1.webp","audio_q1.mp3","God","A Person","Nobody"),
        mc(title,"Does God love you?","love.webp","audio_q2.mp3","Yes Always","No","Sometimes"),
        mc(title,"What do we do when we talk to God?","pray.webp","audio_q3.mp3","Pray","Sing","Run"),
        mc(title,"Where do Christians worship on Sunday?","church.webp","audio_q4.mp3","Church","Market","Home Only"),
        mc(title,"How should we treat our friends?","kind.webp","audio_q5.mp3","With Kindness","With Anger","Ignore"),
        sr(title,"Say: GOD LOVES ME AND I LOVE GOD!","love_say.webp","audio_q6.mp3","God Loves Me And I Love God"),
        sr(title,"Say: I AM A CHILD OF GOD!","child.webp","audio_q7.mp3","I Am A Child Of God"),
        mt(title,"Match Christian life!","God","Creator","Jesus","Savior","Bible","Gods Word"),
        mt(title,"Match what we do!","Pray","Talk to God","Sing","Praise God","Share","Show Love"),
    ]

# ---------------------------------------------------------------------------
# English — Grammar (nouns, verbs, adjectives, opposites, sight words)
# ---------------------------------------------------------------------------
def gen_grammar_lesson(title, theme):
    t = theme.lower()
    if "noun" in t or "naming" in t:
        category = "people" if "people" in t or "person" in t else "animal" if "animal" in t else "place" if "place" in t else "thing"
        if category == "people":
            examples = [("Teacher","Person"),("Doctor","Person"),("Baby","Person"),("Mother","Person"),("Farmer","Person")]
        elif category == "animal":
            examples = [("Dog","Animal"),("Cat","Animal"),("Lion","Animal"),("Bird","Animal"),("Fish","Animal")]
        elif category == "place":
            examples = [("School","Place"),("Church","Place"),("Market","Place"),("Home","Place"),("Park","Place")]
        else:
            examples = [("Book","Thing"),("Ball","Thing"),("Chair","Thing"),("Pen","Thing"),("Bag","Thing")]
        e = examples
        return [
            mc(title,f"Which word is a NOUN (naming word) for a {category}?","noun1.webp","audio_q1.mp3",e[0][0],"Run","Happy"),
            mc(title,f"Touch the picture of a {e[1][0]}!","noun2.webp","audio_q2.mp3",e[1][0],"Jump","Big"),
            mc(title,f"A noun names a {category}. Which is a noun?","noun3.webp","audio_q3.mp3",e[2][0],"Fast","Blue"),
            mc(title,f"Which word names a {category}?","noun4.webp","audio_q4.mp3",e[3][0],"Slowly","Run"),
            mc(title,f"Is {e[0][0]} a noun?","noun5.webp","audio_q5.mp3","Yes","No","Maybe"),
            sr(title,f"Say: {e[0][0].upper()} IS A NOUN!","noun_say.webp","audio_q6.mp3",f"{e[0][0]} Is A Noun"),
            sr(title,"Say: NOUNS ARE NAMING WORDS!","noun_rule.webp","audio_q7.mp3","Nouns Are Naming Words"),
            mt(title,f"Match {category} nouns!",e[0][0],e[0][1],e[1][0],e[1][1],e[2][0],e[2][1]),
            mt(title,"Match noun to type!",e[3][0],category.capitalize(),e[4][0],category.capitalize(),"Run","Action Word"),
            mt(title,"Match noun or not!",e[0][0],"Noun","Jump","Verb","Happy","Adjective"),
        ]
    if "verb" in t or "action" in t:
        verbs = [("Run","Move fast"),("Jump","Leap up"),("Sing","Make music"),("Dance","Move to beat"),("Read","Look at book")]
        return [
            mc(title,"Which word is a VERB (action word)?","verb1.webp","audio_q1.mp3","Run","Table","Blue"),
            mc(title,"Touch the picture showing JUMPING!","verb2.webp","audio_q2.mp3","Jumping","Sleeping","Eating"),
            mc(title,"Which is an ACTION word?","verb3.webp","audio_q3.mp3","Sing","Chair","Red"),
            mc(title,"What action does a dog do?","verb4.webp","audio_q4.mp3","Run","Chair","Blue"),
            mc(title,"Is DANCE an action word (verb)?","verb5.webp","audio_q5.mp3","Yes","No","Maybe"),
            sr(title,"Say: RUN JUMP SING DANCE — ACTION WORDS!","verb_say.webp","audio_q6.mp3","Run Jump Sing Dance Action Words"),
            sr(title,"Say: VERBS ARE ACTION WORDS!","verb_rule.webp","audio_q7.mp3","Verbs Are Action Words"),
            mt(title,"Match verb to its action!","Run","Move Fast","Jump","Leap Up","Sing","Make Music"),
            mt(title,"Match action word to picture!","Dance","Dancing","Read","Reading","Write","Writing"),
            mt(title,"Match verb or noun!","Run","Verb","Table","Noun","Sing","Verb"),
        ]
    if "adjective" in t or "colour" in t or "color" in t or "size" in t:
        return [
            mc(title,"Which word DESCRIBES a noun (adjective)?","adj1.webp","audio_q1.mp3","Red","Run","Table"),
            mc(title,"Touch the BIG ball!","adj2.webp","audio_q2.mp3","Big Ball","Small Ball","Flat Ball"),
            mc(title,"Which colour word is an adjective?","adj3.webp","audio_q3.mp3","Blue","Run","Chair"),
            mc(title,"Touch the word that describes SIZE?","adj4.webp","audio_q4.mp3","Tall","Jump","Book"),
            mc(title,"Is HAPPY an adjective (describing word)?","adj5.webp","audio_q5.mp3","Yes","No","Maybe"),
            sr(title,"Say: RED BIG HAPPY — DESCRIBING WORDS!","adj_say.webp","audio_q6.mp3","Red Big Happy Describing Words"),
            sr(title,"Say: ADJECTIVES DESCRIBE NOUNS!","adj_rule.webp","audio_q7.mp3","Adjectives Describe Nouns"),
            mt(title,"Match adjectives to what they describe!","Red","Colour","Big","Size","Happy","Feeling"),
            mt(title,"Match colour adjectives!","Red","Apple","Yellow","Sun","Green","Leaf"),
            mt(title,"Match size adjectives!","Big","Elephant","Small","Ant","Tall","Giraffe"),
        ]
    if "opposite" in t:
        pairs_opp = [("Hot","Cold"),("Big","Small"),("Up","Down"),("Fast","Slow"),("Happy","Sad")]
        return [
            mc(title,"What is the OPPOSITE of HOT?","opp1.webp","audio_q1.mp3","Cold","Hot","Warm"),
            mc(title,"What is the OPPOSITE of BIG?","opp2.webp","audio_q2.mp3","Small","Big","Large"),
            mc(title,"What is the OPPOSITE of UP?","opp3.webp","audio_q3.mp3","Down","Up","High"),
            mc(title,"What is the OPPOSITE of FAST?","opp4.webp","audio_q4.mp3","Slow","Fast","Quick"),
            mc(title,"What is the OPPOSITE of HAPPY?","opp5.webp","audio_q5.mp3","Sad","Happy","Joyful"),
            sr(title,"Say: HOT AND COLD ARE OPPOSITES!","opp_say.webp","audio_q6.mp3","Hot And Cold Are Opposites"),
            sr(title,"Say: OPPOSITES ARE DIFFERENT!","diff.webp","audio_q7.mp3","Opposites Are Different"),
            mt(title,"Match opposites!","Hot","Cold","Big","Small","Up","Down"),
            mt(title,"Match more opposites!","Fast","Slow","Happy","Sad","Day","Night"),
            mt(title,"Match describing opposites!","Tall","Short","Heavy","Light","Full","Empty"),
        ]
    if "sight" in t or "the " in t.lower() or " and " in t.lower():
        return [
            mc(title,"Which is a sight word? (a very common word)","sight1.webp","audio_q1.mp3","The","Elephant","Jumping"),
            mc(title,"Touch the word THE in this sentence!","sight2.webp","audio_q2.mp3","The","A","An"),
            mc(title,"Fill the blank: ___ cat is big.","sight3.webp","audio_q3.mp3","The","Run","Jump"),
            mc(title,"Which sight word goes here: ___ dog is small?","sight4.webp","audio_q4.mp3","A","The","Big"),
            mc(title,"Touch the word AND!","sight5.webp","audio_q5.mp3","And","Or","But"),
            sr(title,"Say: THE AND IS IN ON — SIGHT WORDS!","sight_say.webp","audio_q6.mp3","The And Is In On Sight Words"),
            sr(title,"Say: I KNOW MY SIGHT WORDS!","know.webp","audio_q7.mp3","I Know My Sight Words"),
            mt(title,"Match sight words to sentences!","The","The cat","And","Cat and dog","Is","It is red"),
            mt(title,"Match more sight words!","In","In the box","On","On the mat","Under","Under the table"),
            mt(title,"Match sight word to meaning!","The","Points to something","And","Joins two things","Is","Describes state"),
        ]
    # Generic English fallback
    return [
        mc(title,"Which is a noun (naming word)?","eng1.webp","audio_q1.mp3","Cat","Run","Big"),
        mc(title,"Which is a verb (action word)?","eng2.webp","audio_q2.mp3","Jump","Table","Blue"),
        mc(title,"Which is an adjective (describing word)?","eng3.webp","audio_q3.mp3","Happy","Run","Dog"),
        mc(title,"Touch the UPPERCASE letter A!","eng4.webp","audio_q4.mp3","A","a","b"),
        mc(title,"Which word rhymes with CAT?","eng5.webp","audio_q5.mp3","Hat","Dog","Sun"),
        sr(title,"Say: I LOVE ENGLISH!","eng_say.webp","audio_q6.mp3","I Love English"),
        sr(title,"Say: NOUNS VERBS ADJECTIVES!","gram.webp","audio_q7.mp3","Nouns Verbs Adjectives"),
        mt(title,"Match word types!","Cat","Noun","Run","Verb","Happy","Adjective"),
        mt(title,"Match opposites!","Hot","Cold","Big","Small","Up","Down"),
    ]

# ---------------------------------------------------------------------------
# English — Stories / Rhymes / Writing Readiness
# ---------------------------------------------------------------------------
STORY_DATA = {
    "three little pigs": [
        mc("","Which house could the wolf NOT blow down?","pig1.webp","audio_q1.mp3","Brick House","Straw House","Stick House"),
        mc("","Who tried to blow down the pigs houses?","pig2.webp","audio_q2.mp3","Big Bad Wolf","Little Pig","Farmer"),
        mc("","What material was the STRONGEST house made of?","pig3.webp","audio_q3.mp3","Bricks","Straw","Sticks"),
        mc("","Which pig built the straw house?","pig4.webp","audio_q4.mp3","First Pig","Third Pig","Second Pig"),
        mc("","What lesson does this story teach?","pig5.webp","audio_q5.mp3","Build Strong","Be Lazy","Run Away"),
    ],
    "goldilocks": [
        mc("","Who walked into the three bears house?","gold1.webp","audio_q1.mp3","Goldilocks","Red Riding Hood","Cinderella"),
        mc("","Which porridge was JUST RIGHT for Goldilocks?","gold2.webp","audio_q2.mp3","Baby Bears","Papa Bears","Mama Bears"),
        mc("","How many bears were in the story?","gold3.webp","audio_q3.mp3","3","2","4"),
    ],
    "tortoise": [
        mc("","Who won the race by being slow and steady?","tort1.webp","audio_q1.mp3","Tortoise","Hare","Fox"),
        mc("","Why did the Hare lose the race?","tort2.webp","audio_q2.mp3","He Slept","He Was Slow","He Got Lost"),
        mc("","What lesson does this story teach?","tort3.webp","audio_q3.mp3","Slow & Steady Wins","Fast Always Wins","Give Up"),
    ],
    "gingerbread": [
        mc("","Who ran away from everyone in the story?","ging1.webp","audio_q1.mp3","Gingerbread Man","Little Boy","Farmer"),
        mc("","What did the Gingerbread Man say?","ging2.webp","audio_q2.mp3","Run Run Catch Me","Stop Stop Help","Walk Walk Slowly"),
        mc("","Who finally caught the Gingerbread Man?","ging3.webp","audio_q3.mp3","The Fox","The Farmer","The Boy"),
    ],
    "boy who cried wolf": [
        mc("","What did the boy keep pretending?","wolf1.webp","audio_q1.mp3","Wolf Is Coming","Fire Is Coming","Rain Is Coming"),
        mc("","When the real wolf came did anyone believe the boy?","wolf2.webp","audio_q2.mp3","No","Yes","Maybe"),
        mc("","What does this story teach about HONESTY?","wolf3.webp","audio_q3.mp3","Always Tell Truth","Lie For Fun","Cry Often"),
    ],
    "lion and mouse": [
        mc("","Who helped the Lion escape the net?","lion1.webp","audio_q1.mp3","Mouse","Elephant","Rabbit"),
        mc("","The Lion laughed at the Mouse. Was that kind?","lion2.webp","audio_q2.mp3","No","Yes","Maybe"),
        mc("","What lesson does this story teach?","lion3.webp","audio_q3.mp3","Small Can Help Big","Big Always Wins","Run Away"),
    ],
}

def gen_story_lesson(title, theme):
    t = theme.lower()
    for key, rows in STORY_DATA.items():
        if any(word in t for word in key.split()):
            base = [r.copy() for r in rows]
            for r in base:
                r["lesson_title"] = title
            base += [
                sr(title,"Say: I LOVE THIS STORY!","story_say.webp","audio_q6.mp3","I Love This Story"),
                sr(title,"Say: STORIES TEACH US LESSONS!","lesson.webp","audio_q7.mp3","Stories Teach Us Lessons"),
                mt(title,"Match story characters!",base[0]["option_1"],"Main Character","Wolf" if "wolf" in t else "Bear","Problem Maker","Home" if "pig" in t else "Forest","Setting"),
                mt(title,"Match story to moral!","Be Honest" if "wolf" in t else "Build Strong" if "pig" in t else "Slow Steady","Moral","Hard Work","Lesson","Be Kind","Value"),
            ]
            return base
    # Generic story fallback
    return [
        mc(title,"Stories have a BEGINNING MIDDLE and what?","story1.webp","audio_q1.mp3","End","Start","Middle"),
        mc(title,"Who are the people or animals in a story called?","story2.webp","audio_q2.mp3","Characters","Settings","Morals"),
        mc(title,"Where a story takes place is called the what?","story3.webp","audio_q3.mp3","Setting","Character","Moral"),
        mc(title,"The lesson we learn from a story is the what?","story4.webp","audio_q4.mp3","Moral","Setting","Character"),
        mc(title,"Touch the picture showing the BEGINNING of the story!","story5.webp","audio_q5.mp3","Beginning","Middle","End"),
        sr(title,"Say: STORIES TEACH US LESSONS!","lesson.webp","audio_q6.mp3","Stories Teach Us Lessons"),
        sr(title,"Say: I LOVE READING STORIES!","read.webp","audio_q7.mp3","I Love Reading Stories"),
        mt(title,"Match story parts!","Beginning","Start","Middle","Action","End","Conclusion"),
        mt(title,"Match story elements!","Character","Person or Animal","Setting","Where & When","Moral","Lesson Learned"),
    ]

def gen_rhyme_lesson(title, theme):
    t = theme.lower()
    # detect rhyme pairs
    for pair in [("cat","hat"),("dog","frog"),("sun","fun"),("star","car"),("man","can"),("big","pig")]:
        if pair[0] in t or pair[1] in t:
            w1, w2 = pair
            return [
                mc(title,f"Which word rhymes with {w1.upper()}?","rhyme1.webp","audio_q1.mp3",w2.capitalize(),"Dog" if w2!="dog" else "Cat","Bird"),
                mc(title,f"Touch the picture that rhymes with {w2.upper()}!","rhyme2.webp","audio_q2.mp3",w1.capitalize(),"Fish","Tree"),
                mc(title,f"Do {w1.upper()} and {w2.upper()} rhyme?","rhyme3.webp","audio_q3.mp3","Yes","No","Maybe"),
                mc(title,f"Which word rhymes with {w1.upper()}? HAT or DOG?","rhyme4.webp","audio_q4.mp3","Hat" if w1=="cat" else w2.capitalize(),"Dog","Bird"),
                mc(title,"Rhyming words have the same what at the END?","rhyme5.webp","audio_q5.mp3","Sound","Letter","Shape"),
                sr(title,f"Say: {w1.upper()} AND {w2.upper()} RHYME!","rhyme_say.webp","audio_q6.mp3",f"{w1.capitalize()} And {w2.capitalize()} Rhyme"),
                sr(title,"Say: RHYMING WORDS SOUND THE SAME!","same.webp","audio_q7.mp3","Rhyming Words Sound The Same"),
                mt(title,"Match rhyming pairs!",w1.capitalize(),w2.capitalize(),"Dog","Frog" if w1!="dog" else "Log","Sun","Fun" if w1!="sun" else "Run"),
                mt(title,"Match word families!",f"-{w1[-2:]}",w1.capitalize(),f"-at","Cat","og","Dog"),
            ]
    # generic rhyme
    return [
        mc(title,"Which word rhymes with CAT?","rhyme1.webp","audio_q1.mp3","Hat","Dog","Sun"),
        mc(title,"Do SUN and FUN rhyme?","rhyme2.webp","audio_q2.mp3","Yes","No","Maybe"),
        mc(title,"Which word rhymes with STAR?","rhyme3.webp","audio_q3.mp3","Car","Dog","Hat"),
        sr(title,"Say: CAT AND HAT RHYME!","cat_hat.webp","audio_q4.mp3","Cat And Hat Rhyme"),
        sr(title,"Say: RHYMING WORDS ARE FUN!","fun.webp","audio_q5.mp3","Rhyming Words Are Fun"),
        mt(title,"Match rhyming pairs!","Cat","Hat","Dog","Frog","Sun","Fun"),
        mt(title,"Match more rhymes!","Star","Car","Man","Can","Big","Pig"),
    ]

def gen_writing_lesson(title, theme):
    t = theme.lower()
    if "straight" in t or "line" in t:
        return [
            mc(title,"Which line goes straight from top to bottom?","line1.webp","audio_q1.mp3","Straight Line","Curved Line","Zigzag"),
            mc(title,"Touch the STRAIGHT line!","line2.webp","audio_q2.mp3","Straight","Curved","Wavy"),
            mc(title,"A straight line has how many curves?","line3.webp","audio_q3.mp3","None","One","Many"),
            sr(title,"Say: STRAIGHT LINES GO TOP TO BOTTOM!","straight.webp","audio_q4.mp3","Straight Lines Go Top To Bottom"),
            mt(title,"Match line types!","Straight","No curves","Curved","Has curve","Zigzag","Has angles"),
        ]
    if "curve" in t or "loop" in t:
        return [
            mc(title,"Which line is CURVED?","curv1.webp","audio_q1.mp3","Curved Line","Straight Line","Zigzag"),
            mc(title,"A circle is made of what type of line?","circle.webp","audio_q2.mp3","Curved","Straight","Zigzag"),
            sr(title,"Say: CURVED LINES MAKE LETTERS LIKE C AND O!","curve.webp","audio_q3.mp3","Curved Lines Make Letters Like C And O"),
            mt(title,"Match line to letters they make!","Curved","C O G","Straight","I L T","Zigzag","Z N M"),
        ]
    return [
        mc(title,"We hold a pencil to write. What hand do most people use?","pen1.webp","audio_q1.mp3","Right Hand","Left Foot","Both Feet"),
        mc(title,"What do we write ON?","paper.webp","audio_q2.mp3","Paper","Wall","Floor"),
        mc(title,"What do we write WITH?","pencil.webp","audio_q3.mp3","Pencil","Spoon","Stick"),
        sr(title,"Say: I CAN WRITE MY NAME!","name.webp","audio_q4.mp3","I Can Write My Name"),
        mt(title,"Match writing tools!","Pencil","Writes","Eraser","Rubs Out","Ruler","Draws Lines"),
    ]

# ---------------------------------------------------------------------------
# Data handling / Pictograph / Sorting
# ---------------------------------------------------------------------------
def gen_data_lesson(title, theme):
    return [
        mc(title,"We use a PICTOGRAPH to show what?","data1.webp","audio_q1.mp3","Data / Information","A Story","A Song"),
        mc(title,"In a pictograph each picture stands for how many?","pic.webp","audio_q2.mp3","1","5","10"),
        mc(title,"If 3 children like apples and 5 like bananas which is MORE popular?","fav.webp","audio_q3.mp3","Banana","Apple","Same"),
        mc(title,"What does SORT mean?","sort.webp","audio_q4.mp3","Put in Groups","Mix Together","Throw Away"),
        mc(title,"We sort objects by what?","sortby.webp","audio_q5.mp3","Colour Shape Size","Name Number Sound","Nothing"),
        co(title,"Count the fruit in the chart!","chart1.webp","audio_q6.mp3","5","4","6","🍎",5),
        sr(title,"Say: A PICTOGRAPH SHOWS DATA!","data_say.webp","audio_q7.mp3","A Pictograph Shows Data"),
        sr(title,"Say: I CAN READ A CHART!","chart_say.webp","audio_q8.mp3","I Can Read A Chart"),
        mt(title,"Match data words!","Pictograph","Shows Data","More","Bigger Amount","Less","Smaller Amount"),
        mt(title,"Match sorting categories!","Colour","Red Blue Green","Shape","Circle Square","Size","Big Small"),
    ]

# ---------------------------------------------------------------------------
# Master / Review / Trophy lessons (PP1 & PP2 grand master files)
# ---------------------------------------------------------------------------
def gen_master_lesson(title, theme):
    t = theme.lower()
    subject = "CRE" if "cre" in t or "bible" in t or "jesus" in t or "god" in t else \
              "English" if "english" in t or "phonics" in t or "grammar" in t else "Math"
    trophy_phrase = f"{subject} Master"
    return [
        mc(title,f"Tap the GOLDEN {subject.upper()} TROPHY!","trophy.webp","audio_q1.mp3","🏆","❌","⭕"),
        mc(title,"You have completed many missions! How does that make you feel?","feel.webp","audio_q2.mp3","Proud","Sad","Bored"),
        mc(title,"What do we call someone who completes all missions?","master.webp","audio_q3.mp3","Champion","Beginner","Learner"),
        mc(title,"Hard work and practice leads to?","hard.webp","audio_q4.mp3","Success","Failure","Nothing"),
        mc(title,"Touch the medal for completing this {subject} journey!","medal.webp","audio_q5.mp3","Medal","Pencil","Book"),
        sr(title,f"Say: I AM A {subject.upper()} CHAMPION!","champ.webp","audio_q6.mp3",f"I Am A {subject} Champion"),
        sr(title,"Say: I WORKED HARD AND I DID IT!","did_it.webp","audio_q7.mp3","I Worked Hard And I Did It"),
        sr(title,f"Say: I COMPLETED ALL {subject.upper()} MISSIONS!","done.webp","audio_q8.mp3",f"I Completed All {subject} Missions"),
        mt(title,"Match achievement words!","Trophy","Winner","Medal","Champion","Badge","Achievement"),
        mt(title,"Match what you learned!","Letters","Phonics" if subject=="English" else "Numbers" if subject=="Math" else "God",
           "Words","Grammar" if subject=="English" else "Addition" if subject=="Math" else "Jesus",
           "Stories","Reading" if subject=="English" else "Shapes" if subject=="Math" else "Bible"),
    ]

# ---------------------------------------------------------------------------
# Main dispatch function
# ---------------------------------------------------------------------------
import re as _re

def generate_rows(path: "Path", title: str) -> list:
    theme = path.stem
    # strip leading ID
    theme = _re.sub(r'^[^_]+_', '', theme, count=1)
    theme_clean = theme.replace("_", " ")
    t = theme_clean.lower()

    # Try specific generators first
    rows = gen_letter_lesson(title, theme_clean)
    if rows: return rows

    rows = gen_cvc_lesson(title, theme_clean)
    if rows: return rows

    if any(x in t for x in ["addition","adding","sum","plus"]):
        return gen_addition_lesson(title, theme_clean)
    if any(x in t for x in ["subtraction","minus","take away","subtract"]):
        return gen_subtraction_lesson(title, theme_clean)
    if any(x in t for x in ["number","count","counting","zero","digit"]) and any(c.isdigit() for c in theme_clean):
        rows = gen_number_lesson(title, theme_clean)
        if rows: return rows
    if any(x in t for x in ["sphere","cube","cone","cylinder","circle","square","triangle","rectangle","shape"]):
        rows = gen_shape_lesson(title, theme_clean)
        if rows: return rows
    if any(x in t for x in ["pattern","aab","abc","sequence","repeat"]):
        return gen_pattern_lesson(title, theme_clean)
    if any(x in t for x in ["long","short","heavy","light","tall","full","empty","capacity","weight","measure","coin","money","shilling","day","week","time"]):
        return gen_measurement_lesson(title, theme_clean)
    if any(x in t for x in ["in front","behind","above","below","inside","outside","left","right","near","far","spatial","position"]):
        return gen_spatial_lesson(title, theme_clean)
    if any(x in t for x in ["sorting","sort","pictograph","data","chart","graph","more","fewer"]):
        return gen_data_lesson(title, theme_clean)
    if any(x in t for x in ["noun","verb","adjective","opposite","grammar","sight word","sentence"]):
        return gen_grammar_lesson(title, theme_clean)
    if any(x in t for x in ["rhyme","cat hat","dog frog","sun fun","star car"]):
        return gen_rhyme_lesson(title, theme_clean)
    if any(x in t for x in ["story","tortoise","hare","pig","goldilocks","gingerbread","wolf","lion"]):
        return gen_story_lesson(title, theme_clean)
    if any(x in t for x in ["tracing","writing","straight line","curve","loop","prewriting","pre-writing"]):
        return gen_writing_lesson(title, theme_clean)
    if any(x in t for x in ["master","champion","grand","trophy","century","review","challenge"]):
        return gen_master_lesson(title, theme_clean)
    # CRE themes
    if any(x in t for x in ["god","jesus","christ","bible","church","pray","praise","sing","creation","share","kind","love","value","worship","daniel","noah","david","moses"]):
        return gen_cre_generic(title, theme_clean)
    # Fallback number lesson if digits present
    if any(c.isdigit() for c in theme_clean):
        rows = gen_number_lesson(title, theme_clean)
        if rows: return rows
    # Ultimate fallback
    return gen_master_lesson(title, theme_clean)
