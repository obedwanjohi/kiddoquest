# MASTER ROADMAP: BZabc Kids App

## Current Status: 
✅ **Kid UI Foundations (Complete):** Video player with Immersive Theater Mode, squishy speed controls, auto-scroll, TTS audio, and custom UI framework.
✅ **Admin & Content Creation (Complete):** Mega Builder, 13+ Question Types, image/audio syncing, and World/Mission structure.
✅ **Quiz Engine (Complete):** Dynamic rendering of question types and answer validation.

---

## EXECUTION STRATEGY: What's Next?
We are building a unified, single-app experience. Parents will not go to a separate website to manage settings; everything lives inside the app behind a **Secure Parent PIN Gate**. 

### The Order of Operations:
1. **PHASE 1 (NEXT): The World Map & Reward Economy** - We must finish the child's core gameplay loop first. They need a place to spend their earned stars.
2. **PHASE 2: Adaptive Question Banking** - Implementing the backend logic so kids don't memorize patterns.
3. **PHASE 3: Unified Parent Dashboard & AI Reporting** - Building the PIN-gated "Control Room" inside the app.
4. **PHASE 4: Pre-Launch & Monetization** - Stripe integration, landing pages, and final content generation.

---

## 🚀 PHASE 1: The World Map & Reward Economy (UP NEXT)

### 1. The Core Concept
Children thrive on visual progression and tangible rewards. The interface they use to select missions shouldn't be a boring list; it should be an expansive, illustrated "Adventure Map." Completing missions will fuel a virtual economy, allowing them to earn currency to customize their experience.

### 2. The Adventure Map (Visual Progression)
*   **The Path:** Missions are laid out as stepping-stone nodes on a scrolling, beautifully illustrated map (e.g., crossing a jungle, climbing a mountain). 
*   **Node States:** 
    *   *Locked:* Grayed out with a padlock icon (building anticipation for what's next).
    *   *Active:* Glowing and pulsating, showing exactly where the child needs to focus.
    *   *Completed:* Turned to solid gold, displaying 1, 2, or 3 stars based on their score.
*   **The Avatar Marker:** A character (like Leo the Lion) stands on the active node and physically walks to the next node when a mission is completed.

### 3. The Virtual Economy (Earning)
Instead of just getting a "Good Job!" screen, performance is tied to an in-app currency (e.g., "Star Coins").
*   **Base Earnings:** Completing a mission awards a base amount of coins.
*   **Performance Bonuses:** Earning 3 stars on a mission grants a massive coin bonus.
*   **Streak Multipliers:** Playing for 3 days in a row triggers a "Streak Bonus," doubling the coins earned to encourage daily habit-building.

### 4. The Reward Shop (Spending)
The child will have access to a "Shop" tab where they can exercise agency and spend their hard-earned coins.
*   **Avatar Customization:** Kids can purchase cosmetic items like silly hats, glasses, superhero capes, or different outfits for their profile avatar.
*   **The Sticker Book:** A secondary reward system where kids buy "Sticker Packs." Opening a pack reveals random digital stickers that they can place freely onto a blank digital canvas to create their own scenes.

---

## 🧠 PHASE 2: Adaptive Reassessment & AI Reporting (Question Banking)

### 1. The Problem: The "Memorization" Loop
When a child fails a mission and retries it immediately, presenting the exact same questions leads to pattern memorization rather than actual learning. We need a way to test concept comprehension using fresh variations of the same problem.

### 2. The Solution: Dynamic Question Banking
Instead of hardcoding fixed questions to a mission, missions will dynamically pull questions from a central `QuestionBank`. 
*   The educational intro/video remains exactly the same to reinforce the core concept.
*   The quiz engine pulls a randomized pool of "backup" questions for the reassessment.

### 3. Technical Implementation: The Exclusion Filter
To guarantee true freshness, the system will not rely on pure randomness. 
*   When generating a retest, the backend queries the `QuestionBank`.
*   It applies an **Exclusion Filter**: `WHERE question_id NOT IN (recently_attempted_ids)`.
*   This ensures that the child mathematically cannot receive the exact same questions they failed in their previous attempt.

---

## 🛡️ PHASE 3: Parental Controls, Reporting & The PIN Gate

### 1. The Unified App "PIN Gate" Architecture
To keep the experience seamless, parents will not log into a separate web portal. 
*   The Kid UI (Map) will feature a small "Settings" gear icon.
*   Tapping it prompts a **Parent Gate** (e.g., a 4-digit PIN or an advanced math question).
*   Correct entry unlocks the unified Parent Dashboard directly inside the app.

### 2. AI Parent Reporting Engine
* **Data Feed:** As the child plays, the app records granular data (time spent, struggle metrics).
* **LLM Processing:** The backend injects this JSON data into an AI prompt to generate plain-English, encouraging reports.
* **The Dashboard Display:** Shows "The Milestone", "The Focus Area", and "The Offline Activity" (an AI-suggested physical activity to reinforce learning).

### 3. Screen Time & Pacing Limits
*   **Mission/Time Limits:** Set daily maximums to prevent binge-playing.
*   **The "Sleep Mode" UX:** When a limit is reached, the Kid UI transitions to a gentle "Sleep Mode" screen (Leo sleeping), explaining it's time to rest until tomorrow.

### 4. Curriculum Focus (Subject Locking)
*   **Subject Toggles:** Temporarily turn off specific tracks (e.g., lock Literacy to focus only on Numeracy).
*   **Progression Gates:** Require a 100% score on milestone missions before allowing the child to proceed to the next map region.

---

## 🚀 PHASE 4: Content Creation & Pre-Launch
*   **Monetization:** Stripe integration for subscriptions and free trials.
*   **Onboarding:** Public landing page and unified registration flow.
*   **Content:** Using the Mega Builder to populate the QuestionBank, record videos, and build out the curriculum for ages 3-6.
