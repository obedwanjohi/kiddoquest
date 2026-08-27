<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$images = [
    473 => 'pattern_heart_1784353057469.png',
    474 => 'pattern_star_1784353067253.png',
    475 => 'pattern_moon_1784353077787.png',
    476 => 'pattern_sun_1784353089243.png'
];

$sourceDir = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\\';
$targetDir = public_path('storage/media/images/');

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

foreach ($images as $id => $filename) {
    $sourcePath = $sourceDir . $filename;
    $targetPath = $targetDir . $filename;
    
    // Copy file
    if (file_exists($sourcePath)) {
        copy($sourcePath, $targetPath);
        
        // Update DB
        DB::table('question_options')
            ->where('id', $id)
            ->update([
                'image_url' => '/storage/media/images/' . $filename
            ]);
            
        echo "Updated option $id with image $filename.\n";
    } else {
        echo "File missing: $sourcePath\n";
    }
}
