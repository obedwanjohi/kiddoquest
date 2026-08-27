<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mappings = [
    'pattern_apple_banana' => 'pattern_fruit_ab',
    'pattern_1_2' => 'pattern_numbers_12',
    'pattern_dog_cat' => 'pattern_animals_dog_cat',
    'pattern_red_blue' => 'pattern_colours_red_blue'
];

$updatedQ = 0;
foreach ($mappings as $old => $new) {
    $media = App\Models\Media::where('name', $new)->first();
    if ($media) {
        $questions = App\Models\QuizQuestion::where('prompt_image_url', 'like', "%{$old}%")->get();
        foreach ($questions as $q) {
            $q->prompt_image_url = '/storage/' . $media->file_path;
            $q->save();
            $updatedQ++;
        }
    }
}

echo "Fixed {$updatedQ} broken pattern prompt images.\n";
