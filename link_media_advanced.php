<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mappings = [
    'speak_num_' => 'speak_number_',
    'speak_123' => 'count_with_me_123',
    'co_' => 'count_scene_',
    'numcard_' => 'number_card_',
    'seq_duck_' => 'ducks_',
    'listen_cat_' => 'cats_',
    'listen_balloons_' => 'balloons_',
    'seq_apple_' => 'apples_',
    'listen_apple_' => 'apples_',
    'sort_orange_' => 'oranges_',
    'sort_banana_' => 'bananas_',
    'sort_mango_' => 'mangoes_',
    'match_stars_' => 'stars_',
    'match_apple_' => 'apples_',
];

function findMedia($baseName) {
    global $mappings;
    
    // Exact match
    $media = App\Models\Media::where('name', $baseName)->first();
    if ($media) return $media;
    
    // Try stripping prefixes
    foreach (['match_', 'sort_', 'seq_', 'listen_', 'pat_'] as $prefix) {
        if (strpos($baseName, $prefix) === 0) {
            $stripped = substr($baseName, strlen($prefix));
            $media = App\Models\Media::where('name', $stripped)->first();
            if ($media) return $media;
        }
    }
    
    // Try custom mappings
    foreach ($mappings as $from => $to) {
        if (strpos($baseName, $from) === 0) {
            $mapped = str_replace($from, $to, $baseName);
            $media = App\Models\Media::where('name', $mapped)->first();
            if ($media) return $media;
        }
    }

    // Try finding any media that contains the base name or vice versa
    $allMedia = App\Models\Media::all();
    foreach ($allMedia as $m) {
        if (str_contains($m->name, $baseName) || str_contains($baseName, $m->name)) {
            return $m;
        }
    }

    // Final fallback for edge cases like 'apples_04' instead of 'listen_apple_04'
    $parts = explode('_', $baseName);
    $lastPart = end($parts);
    if (is_numeric($lastPart)) {
        foreach ($allMedia as $m) {
            if (str_ends_with($m->name, '_' . $lastPart)) {
                // simple heuristic, might be slightly off but good enough
                if (str_contains($m->name, $parts[1] ?? '')) {
                    return $m;
                }
            }
        }
    }

    return null;
}

$questions = App\Models\QuizQuestion::where('prompt_image_url', 'like', '%/images/%')->get();
$updatedQ = 0;
foreach ($questions as $q) {
    $baseName = pathinfo($q->prompt_image_url, PATHINFO_FILENAME);
    $media = findMedia($baseName);
    if ($media) {
        $q->prompt_image_url = '/storage/' . $media->file_path;
        $q->save();
        $updatedQ++;
    }
}

$options = App\Models\QuestionOption::where('image_url', 'like', '%/images/%')->get();
$updatedO = 0;
foreach ($options as $o) {
    $baseName = pathinfo($o->image_url, PATHINFO_FILENAME);
    $media = findMedia($baseName);
    if ($media) {
        $o->image_url = '/storage/' . $media->file_path;
        $o->save();
        $updatedO++;
    }
}

echo "Advanced mapping updated {$updatedQ} questions and {$updatedO} options.\n";
