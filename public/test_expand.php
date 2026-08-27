<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting CSV Expansion Test...\n";

$baseDir = __DIR__ . '/../database/csv_imports/';

$subjects = [
    'play_group_math' => ['type' => 'math'],
    'play_group_english' => ['type' => 'english'],
    'play_group_cre' => ['type' => 'cre'],
    'pp1_math' => ['type' => 'math'],
    'pp1_english' => ['type' => 'english'],
    'pp1_cre' => ['type' => 'cre'],
    'pp2_math' => ['type' => 'math'],
    'pp2_english' => ['type' => 'english'],
    'pp2_cre' => ['type' => 'cre'],
];

$headers = [
    'question_type', 'lesson_title', 'prompt', 'prompt_image', 'prompt_audio', 
    'correct_answer', 'option_1', 'option_2', 'option_3', 'option_4', 
    'count_emoji_or_image', 'target_count', 'target_word', 
    'pair_1_left', 'pair_1_right', 'pair_2_left', 'pair_2_right', 'pair_3_left', 'pair_3_right'
];

$totalFixed = 0;
$totalChecked = 0;

foreach ($subjects as $folder => $info) {
    $dirPath = $baseDir . $folder . '/';
    if (!is_dir($dirPath)) {
        echo "Dir not found: {$dirPath}\n";
        continue;
    }

    $files = glob($dirPath . '*.csv');
    echo "Folder {$folder}: " . count($files) . " CSV files found.\n";

    foreach ($files as $file) {
        $totalChecked++;
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) continue;

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $r = str_getcsv($lines[$i]);
            if (!empty($r) && !empty($r[0])) {
                $rows[] = $r;
            }
        }

        if (count($rows) >= 20) {
            continue;
        }

        $firstRow = $rows[0] ?? [];
        $lessonTitle = !empty($firstRow[1]) ? $firstRow[1] : str_replace(['_', '-'], ' ', basename($file, '.csv'));
        $cleanTitle = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($lessonTitle));
        $subjectType = $info['type'];

        while (count($rows) < 20) {
            $q = count($rows) + 1;
            
            if ($subjectType === 'math') {
                if ($q <= 8) {
                    $cnt = (($q - 1) % 5) + 1;
                    $row = ['count_objects', $lessonTitle, "Count the {$lessonTitle} items for Question {$q}!", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', (string)$cnt, (string)($cnt + 1), (string)($cnt + 2), '', '⭐', (string)$cnt, '', '', '', '', '', '', ''];
                } elseif ($q <= 14) {
                    $cnt = (($q - 1) % 5) + 1;
                    $row = ['multiple_choice', $lessonTitle, "Which card shows {$cnt} items for {$lessonTitle}?", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', "{$cnt} Items", ($cnt + 1) . " Items", ($cnt + 2) . " Items", '', '', '', '', '', '', '', '', '', ''];
                } elseif ($q <= 17) {
                    $words = ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten'];
                    $word = $words[($q - 15) % 10];
                    $row = ['speak_repeat', $lessonTitle, "Say out loud: {$word}!", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', $word, '', '', '', '', '', $word, '', '', '', '', '', ''];
                } else {
                    $pairNum = $q - 17;
                    $row = ['matching', $lessonTitle, "Match items to number symbols!", '', '', '1', '', '', '', '', '', '', '', "Group {$pairNum}", (string)$pairNum, "Group " . ($pairNum + 1), (string)($pairNum + 1), "Group " . ($pairNum + 2), (string)($pairNum + 2)];
                }
            } elseif ($subjectType === 'english') {
                if ($q <= 8) {
                    $row = ['multiple_choice', $lessonTitle, "Question {$q}: Which word or letter belongs to {$lessonTitle}?", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', "Option A (Correct)", "Option B", "Option C", '', '', '', '', '', '', '', '', '', ''];
                } elseif ($q <= 14) {
                    $row = ['multiple_choice', $lessonTitle, "Question {$q}: Identify the correct picture for {$lessonTitle}!", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', "Correct Picture", "Incorrect Picture 1", "Incorrect Picture 2", '', '', '', '', '', '', '', '', '', ''];
                } elseif ($q <= 17) {
                    $phrase = "Speak {$lessonTitle} Question {$q}";
                    $row = ['speak_repeat', $lessonTitle, "Say out loud: {$phrase}!", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', $phrase, '', '', '', '', '', $phrase, '', '', '', '', '', ''];
                } else {
                    $row = ['matching', $lessonTitle, "Match words to pictures for {$lessonTitle}!", '', '', '1', '', '', '', '', '', '', '', "Word 1", "Picture 1", "Word 2", "Picture 2", "Word 3", "Picture 3"];
                }
            } else { // CRE
                if ($q <= 8) {
                    $row = ['multiple_choice', $lessonTitle, "Question {$q}: What does God's word teach us about {$lessonTitle}?", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', "God's Truth 🙏", "Choice B", "Choice C", '', '', '', '', '', '', '', '', '', ''];
                } elseif ($q <= 14) {
                    $row = ['multiple_choice', $lessonTitle, "Question {$q}: Which story action shows love for {$lessonTitle}?", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', "Loving Action ❤️", "Unkind Action", "Selfish Action", '', '', '', '', '', '', '', '', '', ''];
                } elseif ($q <= 17) {
                    $phrase = "Thank You God for {$lessonTitle}";
                    $row = ['speak_repeat', $lessonTitle, "Say out loud: {$phrase}!", "{$cleanTitle}_q{$q}.webp", "audio_q{$q}.mp3", '1', $phrase, '', '', '', '', '', $phrase, '', '', '', '', '', ''];
                } else {
                    $row = ['matching', $lessonTitle, "Match Christian value cards for {$lessonTitle}!", '', '', '1', '', '', '', '', '', '', '', "Action 1", "Value 1", "Action 2", "Value 2", "Action 3", "Value 3"];
                }
            }

            $rows[] = $row;
        }

        $fp = fopen($file, 'w');
        fputcsv($fp, $headers);
        foreach ($rows as $r) {
            while (count($r) < count($headers)) $r[] = '';
            fputcsv($fp, array_slice($r, 0, count($headers)));
        }
        fclose($fp);
        $totalFixed++;
    }
}

echo "FINISHED EXPANSION! Total Checked: {$totalChecked} | Total Expanded to 20 Questions: {$totalFixed}\n";
