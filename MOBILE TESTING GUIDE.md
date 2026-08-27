# 📱 Mobile Testing Quick Reference

> **Your PC's local IP:** `192.168.98.55`
> **Port:** `8081`

---

## 🚀 How to Test on Your Phone

### Step 1: Connect Your Phone
- Connect your phone to the **same Wi-Fi network** as your PC.
- This is the Wi-Fi/Ethernet that your PC uses to access the internet.

### Step 2: Open These URLs on Your Phone

Open your phone's browser (Chrome/Safari) and type:

#### 🎓 Admin Panel (Content Management)
```
http://192.168.98.55:8081/admin/login
```
- Email: `admin@kidlearn.com`
- Password: `password123`

#### 👨‍👩‍👧 Parent Portal (Guardian Login → Pick Child → Play)
```
http://192.168.98.55:8081/parent/login
```
- Email: `parent@kidlearn.com`
- Password: `password123`
- Child name: **Emma** (Lion avatar)

#### 🧪 Dev Showcases (No Login Needed — Quick Visual Tests)
```
http://192.168.98.55:8081/dev/showcase           ← UI Component Gallery
http://192.168.98.55:8081/dev/quiz-prototype      ← Quiz Player Prototype
http://192.168.98.55:8081/dev/qt01                ← QT-01 Polished Demo
```

---

## 🔍 What to Look For (Per Question Type)

| Check | ✅ Good | ❌ Bad |
|-------|--------|-------|
| **Layout (Portrait)** | Everything fits, no horizontal scroll | Content cut off, tiny text |
| **Layout (Landscape)** | Layout adapts nicely | Buttons overlap, weird gaps |
| **Touch Targets** | Buttons are big (≥ 48px), easy to tap | Buttons too small, miss-taps |
| **Animations** | Smooth, fun | Janky, slow, or nauseating |
| **Audio** | Plays on tap | Blocked or silent |
| **Wrong Answer** | Gentle encouragement | Says "Wrong!" or scary |
| **Right Answer** | Celebration (confetti, stars, Leo) | Nothing happens |
| **Transitions** | Smooth between questions | Jumps, freezes |

---

## 🎮 How to Reach Each Quiz from the Parent Flow

1. Log in at `/parent/login`
2. Tap **Emma** (the lion avatar)
3. You'll see the **Adventure Map** with worlds
4. Tap a world (e.g., "Whispering Forest")
5. Tap a mission/lesson
6. Complete the lesson, then tap **Start Quiz**

### Quizzes to Test:
| Quiz # | Title | Question Types Inside |
|--------|-------|----------------------|
| **#24** | Letter A — Quick Check | Multiple Choice, True/False, Fill-Blank, **Matching** |
| **#25** | Counting 1–5 Quiz | Count Objects, Multiple Choice, Pattern |
| **#26** | Color Red Quiz | Multiple Choice, True/False |
| **#27** | Farm Friends Quiz | Listen & Choose, Speak & Repeat |
| **#28** | Shapes Quiz | Sequence, Pattern |

---

## 🐛 If Something Doesn't Work

### Page doesn't load at all
1. Check your phone is on the **same Wi-Fi** as your PC
2. Make sure **Apache (XAMPP)** is running on your PC
3. Try pinging: on your phone, visit `http://192.168.98.55:8081/` — you should see the Laravel welcome page

### Page loads but looks broken (missing CSS/JS)
- The app uses **relative URLs** in the quiz engine, so it should adapt automatically
- If CSS is missing, do a hard refresh on your phone (pull down or clear browser cache)

### Form submission fails (CSRF or 419 error)
- The quiz engine uses a global CSRF token — should work on any host
- If you see 419, clear your browser cookies for that site and try again

### Audio doesn't play
- Mobile browsers block autoplay — the audio should trigger on tap
- Make sure your phone isn't on silent mode

---

## 📋 Testing Checklist Template (Copy for Each Type)

```
Question Type: QT-__ ____________________
Tested on: [Phone model / Browser]
Date: _________

Portrait layout:      ✅ / ❌
Landscape layout:     ✅ / ❌
Touch targets big:    ✅ / ❌
Animations smooth:    ✅ / ❌
Audio works:          ✅ / ❌
Wrong answer gentle:  ✅ / ❌
Right answer fun:     ✅ / ❌
Transitions clean:    ✅ / ❌
Score saved:          ✅ / ❌

Issues found:
1. 
2. 

Verdict: APPROVED / NEEDS WORK
```

---

## ⚙️ Troubleshooting Network Access

If your phone can't reach `192.168.98.55:8081`:

### Check 1: Is Apache running?
On PC, visit `http://localhost:8081/` — should load. If not, start XAMPP/Apache.

### Check 2: Windows Firewall
The firewall may block external connections. Open port 8081:
1. Search **"Windows Defender Firewall"** → Advanced Settings
2. Inbound Rules → New Rule
3. Port → TCP → `8081` → Allow
4. Name it "KidLearn Mobile 8081"

Or run this in an **Admin Command Prompt**:
```cmd
netsh advfirewall firewall add rule name="KidLearn Mobile 8081" dir=in action=allow protocol=TCP localport=8081
```

### Check 3: IP Address Changed
If you restart your PC/router, your IP might change. Re-run `ipconfig` to check.
Update any bookmarks on your phone.

### Check 4: Network Profile
Make sure your Wi-Fi is set to **Private**, not **Public** (Public blocks sharing).
- Settings → Network → Properties → Network profile = Private

---

## 💡 Pro Tip: Add to Home Screen

On your phone, open the URL, then:
- **Android (Chrome):** ⋮ menu → "Add to Home screen"
- **iPhone (Safari):** Share → "Add to Home Screen"

This makes it feel like a real app! 📲