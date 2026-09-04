# 📘 KiddoQuest — Technical Handover & API Specification Document

**Version**: 1.0 (Production Release)  
**Target Platform**: Native Android / iOS (Flutter / React Native / Native Kotlin & Swift)  
**Backend Core**: Laravel 12 + PostgreSQL (Render / Supabase)  
**Web Engine**: Blade + Alpine.js + PWA Service Worker  

---

## 📑 Table of Contents
1. [System Architecture Overview](#1-system-architecture-overview)
2. [Core Data Schemas & Models](#2-core-data-schemas--models)
3. [The 11 CBC Question Types Specifications](#3-the-11-cbc-question-types-specifications)
4. [Pedagogical Rules & Question Limits](#4-pedagogical-rules--question-limits)
5. [Media & Audio Engine Contracts](#5-media--audio-engine-contracts)
6. [Parent Control Center & Security Gates](#6-parent-control-center--security-gates)
7. [API Endpoints Reference](#7-api-endpoints-reference)

---

## 1. System Architecture Overview

KiddoQuest is an interactive early-childhood learning application designed for **Kenyan Competency-Based Curriculum (CBC)** levels:
* **Playgroup** (Ages 3–4)
* **PP1** (Ages 4–5)
* **PP2** (Ages 5–6)

The web application serves as the production engine, content management system, parent dashboard, and PWA player. This document serves as the complete technical blueprint for native mobile developers replicating the kid learning engine, offline synchronization, audio/video playback, and parent security controls.

```
                    ┌─────────────────────────────────────────┐
                    │      KiddoQuest Native Mobile App       │
                    └───────────────────┬─────────────────────┘
                                        │
                 ┌──────────────────────┴──────────────────────┐
                 ▼                                             ▼
  ┌─────────────────────────────┐               ┌─────────────────────────────┐
  │     Local SQLite Cache      │               │     REST & Auth API         │
  │ (Offline Missions & Assets) │               │   (Laravel 12 / Postgres)   │
  └─────────────────────────────┘               └─────────────────────────────┘
```

---

## 2. Core Data Schemas & Models

### 2.1 Guardians (`guardians`)
Represents the parent/guardian account.
```sql
CREATE TABLE guardians (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    pin VARCHAR(4) DEFAULT '1234',
    enable_devotional BOOLEAN DEFAULT TRUE,
    enable_songs_hub BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2.2 Children (`children`)
Represents the child profile under a guardian.
```sql
CREATE TABLE children (
    id BIGSERIAL PRIMARY KEY,
    guardian_id BIGINT REFERENCES guardians(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    level VARCHAR(50) NOT NULL, -- 'playgroup', 'pp1', 'pp2'
    daily_time_limit_minutes INT DEFAULT 30,
    avatar_url VARCHAR(550),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2.3 Adventure Worlds (`adventure_worlds`)
Categories/Worlds on the kid's Adventure Map.
```sql
CREATE TABLE adventure_worlds (
    id BIGSERIAL PRIMARY KEY,
    subject_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    display_title VARCHAR(255),
    description TEXT,
    icon VARCHAR(100) DEFAULT '🌟',
    theme_color VARCHAR(50) DEFAULT '#3B82F6',
    sort_order INT DEFAULT 1,
    is_locked BOOLEAN DEFAULT FALSE
);
```

### 2.4 Missions (`missions`)
Individual learning missions inside an Adventure World.
```sql
CREATE TABLE missions (
    id BIGSERIAL PRIMARY KEY,
    adventure_world_id BIGINT REFERENCES adventure_worlds(id) ON DELETE CASCADE,
    question_bank_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    display_title VARCHAR(255),
    description TEXT,
    video_url VARCHAR(550), -- CapCut Intro Video URL
    questions_per_session INT DEFAULT 8,
    pass_threshold_percent INT DEFAULT 60,
    stars_reward INT DEFAULT 3,
    status VARCHAR(50) DEFAULT 'published',
    sort_order INT DEFAULT 1
);
```

### 2.5 Quiz Questions (`quiz_questions`)
Questions contained inside a Question Bank.
```sql
CREATE TABLE quiz_questions (
    id BIGSERIAL PRIMARY KEY,
    question_bank_id BIGINT NOT NULL,
    quiz_type_id BIGINT NOT NULL,
    type VARCHAR(100) NOT NULL, -- 'multiple_choice', 'matching', 'pattern', etc.
    prompt TEXT NOT NULL,
    narration_text TEXT, -- Speech synthesis voice narration
    prompt_image_url VARCHAR(550),
    prompt_audio_url VARCHAR(550),
    points INT DEFAULT 10,
    sort_order INT DEFAULT 1,
    scoring_config JSONB -- Category definitions for drag_sort, etc.
);
```

### 2.6 Question Options (`question_options`)
Answer choices for a question.
```sql
CREATE TABLE question_options (
    id BIGSERIAL PRIMARY KEY,
    question_id BIGINT REFERENCES quiz_questions(id) ON DELETE CASCADE,
    text_value VARCHAR(255),
    image_url VARCHAR(550),
    audio_url VARCHAR(550),
    is_correct BOOLEAN DEFAULT FALSE,
    match_key VARCHAR(255), -- Pairing key for matching & drag_sort
    sort_order INT DEFAULT 1
);
```

### 2.7 Child Progress (`child_progress`)
Tracks mission completion and stars earned.
```sql
CREATE TABLE child_progress (
    id BIGSERIAL PRIMARY KEY,
    child_id BIGINT REFERENCES children(id) ON DELETE CASCADE,
    mission_id BIGINT REFERENCES missions(id) ON DELETE CASCADE,
    stars_earned INT DEFAULT 0,
    score_percent INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP
);
```

---

## 3. The 11 CBC Question Types Specifications

Below are the exact frontend payload contracts and interaction mechanics for the 11 active Question Types:

### 3.1 QT-01: Multiple Choice (`multiple_choice`)
* **Interaction**: Child taps the correct option card from 2 to 4 options.
* **Payload**:
  ```json
  {
    "type": "multiple_choice",
    "prompt": "Tap the letter A! 🍎",
    "options": [
      { "id": 1, "text": "A", "image": null, "is_correct": true },
      { "id": 2, "text": "B", "image": null, "is_correct": false }
    ]
  }
  ```

### 3.2 QT-02: True / False (`true_false`)
* **Interaction**: 2-option Yes/No card.
* **Payload**:
  ```json
  {
    "type": "true_false",
    "prompt": "Is this a dog? 🐶",
    "options": [
      { "id": 1, "text": "Yes ✅", "is_correct": true },
      { "id": 2, "text": "No ❌", "is_correct": false }
    ]
  }
  ```

### 3.3 QT-03: Matching Pairs (`matching`)
* **Interaction**: Two side-by-side vertical columns. Child taps an item in Left Column (`text`), then taps its matching item in Right Column (`match_key`).
* **Payload**:
  ```json
  {
    "type": "matching",
    "prompt": "Match each animal parent to its baby! 🐶 🐱 🐮",
    "options": [
      { "id": 1, "text": "🐶", "match_key": "🐶 Puppy", "is_correct": true },
      { "id": 2, "text": "🐱", "match_key": "🐱 Kitten", "is_correct": true }
    ]
  }
  ```

### 3.4 QT-04: Drag & Drop Sort (`drag_sort`)
* **Interaction**: Shows **1 item at a time** in top tray. Child taps item, then taps destination bucket (`match_key`).
* **Payload**:
  ```json
  {
    "type": "drag_sort",
    "prompt": "Sort into Farm Animals 🐮 vs Wild Animals 🦁!",
    "scoring_config": { "categories": ["Farm Animals 🐮", "Wild Animals 🦁"] },
    "options": [
      { "id": 1, "text": "🐮", "match_key": "Farm Animals 🐮", "is_correct": true },
      { "id": 2, "text": "🦁", "match_key": "Wild Animals 🦁", "is_correct": true }
    ]
  }
  ```

### 3.5 QT-05: Drag & Drop Sequence (`drag_sequence`)
* **Interaction**: Empty target slots at top, shuffled cards at bottom. Tapping a card places it into the next slot. Child taps green **CHECK 🚀** button to submit.
* **Payload**:
  ```json
  {
    "type": "drag_sequence",
    "prompt": "Put the numbers in order from 1 to 4! 🔢",
    "options": [
      { "id": 1, "text": "1", "is_correct": true, "sort_order": 1 },
      { "id": 2, "text": "2", "is_correct": true, "sort_order": 2 },
      { "id": 3, "text": "3", "is_correct": true, "sort_order": 3 },
      { "id": 4, "text": "4", "is_correct": true, "sort_order": 4 }
    ]
  }
  ```

### 3.6 QT-06: Listen & Choose (`listen_choose`)
* **Interaction**: Audio clip plays automatically (`prompt_audio_url`). Child taps matching visual option card.

### 3.7 QT-07: Speak & Repeat (`speak_repeat`)
* **Interaction**: Voice prompt plays. Child taps microphone button to record word repetition via Web Speech / Native Mic API.

### 3.8 QT-08: Fill the Blank / Spell (`fill_blank`)
* **Interaction**: Word card displays missing slot (e.g. `C _ T 🐱`). Child taps correct letter card (`A`) below to fill the blank.

### 3.9 QT-09: Count Objects (`count_objects`)
* **Interaction**: Grid of visual objects rendered (e.g. 5 Stars ⭐). Child counts and taps number card (`5`).

### 3.10 QT-10: Complete Pattern (`pattern`)
* **Interaction**: Displays sequence with `?` box (e.g. `🔴 🔵 🔴 🔵 ❓`). Tapping correct option populates `?` with pop animation.

### 3.11 QT-12: Finger Tracing (`tracing`)
* **Interaction**: Touch canvas path tracing for letters, numbers, and lines.

---

## 4. Pedagogical Rules & Question Limits

To prevent cognitive fatigue in young learners, native apps MUST enforce these question limits per session:

* **Playgroup (Ages 3–4)**: **5 to 6 Questions per Mission MAX**.
* **PP1 (Ages 4–5) & PP2 (Ages 5–6)**: **8 Questions per Mission**.
* **Abstract Concepts Rule**: For emotions/abstract words (*happy*, *joy*), use clear voice prompts + narration rather than ambiguous image choices.

---

## 5. Media & Audio Engine Contracts

### 5.1 Audio Anti-Overlap Protocol
Before invoking any new speech synthesis or playing sound FX, native apps MUST invoke `stopAllAudio()`:
```javascript
function stopAllAudio() {
    if (window.speechSynthesis) window.speechSynthesis.cancel();
    if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; }
}
```

### 5.2 CapCut Intro Video Player
When `mission.video_url` is present:
* Video player loads 720p HD clip (15–25 seconds).
* Displays a large green **"START MISSION 🚀"** button below.
* Child can play video or tap button to launch questions immediately.

---

## 6. Parent Control Center & Security Gates

1. **4-Digit Parent PIN Gate**: Accessing `/parent` or changing settings requires PIN validation (default: `1234`).
2. **Screen-Time Countdown Bar**:
   * App tracks active child playtime.
   * Renders `⏳ Xm Left` countdown pill in header.
   * When `remaining_time_minutes <= 0`, app automatically locks quiz missions and redirects child to the **Kids Music & Video Songs Hub** (`/kids/songs`).
3. **Feature Toggles**:
   * `enable_devotional`: Toggles Daily Bible Verse & Morning Prayer modal on Adventure Map.
   * `enable_songs_hub`: Toggles access to Music & Video Songs Hub.

---

## 7. API Endpoints Reference

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/parent/login` | Parent PIN & Password login |
| `GET` | `/api/v1/children` | List guardian's child profiles |
| `GET` | `/api/v1/map/{child_id}` | Fetch unlocked Adventure Worlds & Progress |
| `GET` | `/api/v1/missions/{mission_id}` | Fetch mission payload (Questions & Options) |
| `POST` | `/api/v1/missions/{mission_id}/submit` | Submit child mission score & stars |
| `POST` | `/api/v1/parent/settings` | Update devotional & screen-time toggles |

---
**End of Specification Document**
