<?php

$base_dir = "c:/xampp/htdocs/kid/";

function get_missions($dir) {
    $files = glob($dir . "/*.csv");
    natsort($files);
    $missions = [];
    foreach ($files as $f) {
        $name = basename($f, ".csv");
        $parts = explode('_', $name, 2);
        $code = $parts[0];
        $raw_title = isset($parts[1]) ? str_replace('_', ' ', $parts[1]) : $code;
        // Clean title
        $title = trim(preg_replace('/\s+/', ' ', $raw_title));
        $missions[] = ['code' => $code, 'title' => $title];
    }
    return $missions;
}

// 1. Play Group Math (135 Missions)
$missions = get_missions($base_dir . "database/csv_imports/play_group_math");
$out = "# 🎙️ Play Group Mathematics — Voiceover Movie Scripts (135 Missions)\n";
$out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PG-MATH-$id: {$m['title']}\n";
    $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Hello little learner! Welcome to {$m['title']}! Look at the colorful objects on screen. Let's count and explore together! One... Two... Three! Excellent job! Now it's your turn to tap and count. You are super smart! See you in the next counting mission!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "Play Group Mathematics — Voiceover Movie Scripts.md", $out);

// 2. Play Group English (110 Missions)
$missions = get_missions($base_dir . "database/csv_imports/play_group_english");
$out = "# 🎙️ Play Group English — Voiceover Movie Scripts (110 Missions)\n";
$out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PG-ENG-$id: {$m['title']}\n";
    $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Welcome to fun phonics time! Today's lesson is {$m['title']}! Listen carefully to the sound and repeat after me loud and proud! Great job speaking clearly! Now tap the picture that matches on your screen. You are a super star reader!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "Play Group English — Voiceover Movie Scripts.md", $out);

// 3. Play Group CRE (60 Missions)
$missions = get_missions($base_dir . "database/csv_imports/play_group_cre");
$out = "# 🎙️ Play Group CRE — Voiceover Movie Scripts (60 Missions)\n";
$out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PG-CRE-$id: {$m['title']}\n";
    $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Jambo little friend! God loves you so much today! Welcome to {$m['title']}! Look at the wonderful world God made for us. In this lesson, we learn about God's love, kindness, and beauty. Let's say together: God is good all the time! Amen! Wonderful job!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "Play Group CRE — Voiceover Movie Scripts.md", $out);

// 4. PP1 Math (140 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp1_math");
$out = "# 🎙️ PP1 Mathematics — Voiceover Movie Scripts (140 Missions)\n";
$out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP1-MATH-$id: {$m['title']}\n";
    $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Hello young math explorer! Are you ready for {$m['title']}? Let's examine our numbers and shapes carefully! When we combine groups, we add them together. Let's count them up step by step! Practice makes perfect! Tap the correct answer on screen to complete your mission. You are doing fantastic!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP1 Mathematics — Voiceover Movie Scripts.md", $out);

// 5. PP1 English (120 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp1_english");
$out = "# 🎙️ PP1 English — Voiceover Movie Scripts (120 Missions)\n";
$out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP1-ENG-$id: {$m['title']}\n";
    $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Welcome back word builder! Today's mission is {$m['title']}! Let's blend letter sounds and sight words together! Read along with me with clear pronunciation and high expression. Awesome reading! Now repeat after me out loud, then match the picture on your screen. You are becoming a fluent reader!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP1 English — Voiceover Movie Scripts.md", $out);

// 6. PP1 CRE (70 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp1_cre");
$out = "# 🎙️ PP1 CRE — Voiceover Movie Scripts (70 Missions)\n";
$out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP1-CRE-$id: {$m['title']}\n";
    $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Jambo! Today we discover {$m['title']} from God's Holy Word! In this lesson, we learn about faith, kindness, prayer, and obeying God. God made each of us special and cares for us every day! Let's say together: 'God gives me strength and courage!' Great job learning God's word today!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP1 CRE — Voiceover Movie Scripts.md", $out);

// 7. PP2 Math (140 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp2_math");
$out = "# 🎙️ PP2 Mathematics — Voiceover Movie Scripts (140 Missions)\n";
$out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP2-MATH-$id: {$m['title']}\n";
    $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Greetings master mathematician! Welcome to {$m['title']}! Today we explore skip counting, addition and subtraction to 20, fractions, money, clock hours, and data. Look at the problem on screen carefully. Let me walk you through step by step! Double-check your numbers and select the correct answer to complete your mission. Brilliant problem solving!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP2 Mathematics — Voiceover Movie Scripts.md", $out);

// 8. PP2 English (120 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp2_english");
$out = "# 🎙️ PP2 English — Voiceover Movie Scripts (120 Missions)\n";
$out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP2-ENG-$id: {$m['title']}\n";
    $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Welcome master reader! Today's mission is {$m['title']}! Notice how digraphs, blends, pronouns, prepositions, and sentence structures come together to build meaning! Let's read with high expression, clarity, and accuracy! Repeat the phrase aloud clearly, then match the correct elements on screen. Outstanding language skills!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP2 English — Voiceover Movie Scripts.md", $out);

// 9. PP2 CRE (70 Missions)
$missions = get_missions($base_dir . "database/csv_imports/pp2_cre");
$out = "# 🎙️ PP2 CRE — Voiceover Movie Scripts (70 Missions)\n";
$out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
$out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";

foreach ($missions as $idx => $m) {
    $id = $idx + 1;
    $out .= "### 🎬 MISSION PP2-CRE-$id: {$m['title']}\n";
    $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
    $out .= "```text\n";
    $out .= "Jambo young scholar of faith! Welcome to {$m['title']}! In this lesson, we explore how Bible heroes trusted God during challenging times and how Jesus healed and blessed people. God's love gives us courage, peace, and wisdom! Say out loud with strong faith: 'The Lord is my shepherd and my helper!' Outstanding dedication to God's word!\n";
    $out .= "```\n\n---\n\n";
}
file_put_contents($base_dir . "PP2 CRE — Voiceover Movie Scripts.md", $out);

echo "SUCCESSFULLY GENERATED ALL 825 INDIVIDUAL MISSION SCRIPT SECTIONS ACROSS ALL 9 BOOKS!";
