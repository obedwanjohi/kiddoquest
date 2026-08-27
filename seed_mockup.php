<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Mega Test Bank not found.\n");

$mcType = QuizType::where('slug', 'multiple-choice')->first();

$q = QuizQuestion::create([
    'quiz_type_id' => $mcType->id,
    'prompt' => 'Choose the correct sentence',
    'image' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/52.png', // Meowth as sleeping cat fallback, or maybe just a generic cat URL. Let's use a standard cute cat image url.
    'hint' => 'Look at what the cat is doing.',
    'points' => 10,
    'difficulty_level' => 1,
]);

// Actually let's use a nice sleeping cat image from wikimedia or placekitten.
$q->update(['image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b6/Felis_catus-cat_on_snow.jpg/640px-Felis_catus-cat_on_snow.jpg']);

// Link to bank
DB::table('question_bank_questions')->insert([
    'question_bank_id' => $bank->id,
    'question_id' => $q->id,
    'sort_order' => 10,
]);

QuestionOption::create([
    'question_id' => $q->id,
    'text' => 'The cat is sleeping',
    'is_correct' => true,
]);

QuestionOption::create([
    'question_id' => $q->id,
    'text' => 'The dog is running',
    'is_correct' => false,
]);

QuestionOption::create([
    'question_id' => $q->id,
    'text' => 'The bird is flying',
    'is_correct' => false,
]);

echo "Seeded 'Choose the correct sentence' into Mega Test Bank!";
