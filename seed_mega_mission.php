<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdventureWorld;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;

// 1. Create a World
$world = AdventureWorld::updateOrCreate(
    ['slug' => 'mega-test-world'],
    [
        'name' => 'Mega Test World',
        'description' => 'A special world for testing all question types.',
        'theme_color' => '#8B5CF6',
        'icon' => '🧪',
        'status' => 'published',
        'sort_order' => 99
    ]
);

// 2. Create Question Bank
$bank = QuestionBank::updateOrCreate(
    ['name' => 'Mega Test Bank'],
    ['description' => 'Questions for Mega Test Mission']
);
$bankId = $bank->id;

// Clear old questions
QuizQuestion::where('question_bank_id', $bankId)->delete();

// Helpers
function q($bankId, $typeId, $prompt, $imageUrl = null) {
    return QuizQuestion::create([
        'question_bank_id' => $bankId,
        'quiz_type_id'     => $typeId,
        'prompt'           => $prompt,
        'prompt_image_url' => $imageUrl,
        'difficulty'       => 'easy',
        'points'           => 10,
        'sort_order'       => 0,
    ]);
}
function opt($q, $text, $correct, $imgUrl = null, $matchKey = null) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => $imgUrl ? 'image' : 'text',
        'text_value'   => $text,
        'image_url'    => $imgUrl,
        'is_correct'   => $correct,
        'match_key'    => $matchKey,
    ]);
}

// -- QUESTIONS --

// 1. Multiple Choice (Text Options)
$q1 = q($bankId, 1, 'Select the number 5.');
opt($q1, '3', false);
opt($q1, '5', true);
opt($q1, '8', false);

// 2. Multiple Choice (Image Options)
$q2 = q($bankId, 1, 'Select the Apple.');
opt($q2, 'Banana', false, '/images/questions/match_banana.png');
opt($q2, 'Apple', true, '/images/questions/match_apple_05.png');

// 3. True/False
$q3 = q($bankId, 2, 'Is this an Apple?', '/images/questions/match_apple_05.png');
opt($q3, 'True', true);
opt($q3, 'False', false);

// 4. Match (Text to Text)
$q4 = q($bankId, 3, 'Match the identical numbers.');
opt($q4, 'One', true, null, '1'); opt($q4, 'One', true, null, '1');
opt($q4, 'Two', true, null, '2'); opt($q4, 'Two', true, null, '2');

// 5. Match (Image to Image)
$q5 = q($bankId, 3, 'Match the identical images.');
opt($q5, 'Apple', true, '/images/questions/match_apple_05.png', 'apple'); opt($q5, 'Apple', true, '/images/questions/match_apple_05.png', 'apple');
opt($q5, 'Banana', true, '/images/questions/match_banana.png', 'banana'); opt($q5, 'Banana', true, '/images/questions/match_banana.png', 'banana');

// 6. Match (Text to Image)
$q6 = q($bankId, 3, 'Match text to image.');
opt($q6, 'Apple', true, null, 'apple'); opt($q6, 'AppleImg', true, '/images/questions/match_apple_05.png', 'apple');
opt($q6, 'Banana', true, null, 'banana'); opt($q6, 'BananaImg', true, '/images/questions/match_banana.png', 'banana');

// 7. Sort/Drag
$q7 = q($bankId, 4, 'Sort the numbers 1, 2, 3.');
opt($q7, '1', true);
opt($q7, '2', true);
opt($q7, '3', true);

// 8. Pattern
$q8 = q($bankId, 10, 'Complete the pattern.', '/images/questions/pattern_star_heart.png');
opt($q8, 'Heart', false, '/images/questions/pat_heart.png');
opt($q8, 'Star', true, '/images/questions/pat_star.png'); // Setting Star as correct!

// 3. Create Mission
Mission::updateOrCreate(
    ['slug' => 'mega-test-mission'],
    [
        'lesson_id' => 1,
        'adventure_world_id' => $world->id,
        'question_bank_id' => $bankId,
        'title' => 'Mega Test Mission',
        'display_title' => 'The Ultimate UI Test',
        'description' => 'A comprehensive test of all question UI variants.',
        'pass_threshold_percent' => 50,
        'stars_reward' => 3,
        'questions_per_session' => 8,
        'randomize_questions' => 0,
        'estimated_minutes' => 5,
        'status' => 'published',
        'sort_order' => 1
    ]
);

echo "Mega test mission seeded successfully!\n";
