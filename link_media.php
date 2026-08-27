<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$options = App\Models\QuestionOption::whereNotNull('image_url')->get();
$updatedO = 0;
foreach ($options as $o) {
    $baseName = pathinfo($o->image_url, PATHINFO_FILENAME);
    
    // Some are 'match_apple_01', 'sort_apples_03', etc. Let's try matching with and without prefixes
    $media = App\Models\Media::where('name', $baseName)->first();
    
    if (!$media && strpos($baseName, 'match_') === 0) {
        $media = App\Models\Media::where('name', substr($baseName, 6))->first();
    }
    if (!$media && strpos($baseName, 'sort_') === 0) {
        $media = App\Models\Media::where('name', substr($baseName, 5))->first();
    }

    if ($media) {
        $o->image_url = '/storage/' . $media->file_path;
        $o->save();
        $updatedO++;
    }
}

echo "Updated {$updatedO} options.\n";
