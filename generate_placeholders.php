<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$imagesToCreate = [];

// Get from question_options
$options = DB::table('question_options')->whereNotNull('image_url')->get();
foreach($options as $opt) {
    $imagesToCreate[$opt->image_url] = $opt->text_value ?: basename($opt->image_url);
}

// Get from quiz_questions (prompt_image_url)
$questions = DB::table('quiz_questions')->whereNotNull('prompt_image_url')->get();
foreach($questions as $q) {
    $imagesToCreate[$q->prompt_image_url] = 'Prompt: ' . $q->id;
}

$createdCount = 0;

foreach ($imagesToCreate as $url => $label) {
    // Only process /images/questions/
    if (strpos($url, '/images/questions/') === 0) {
        $path = public_path($url);
        
        if (!file_exists($path)) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Create a simple placeholder image
            $width = 400;
            $height = 300;
            $image = imagecreatetruecolor($width, $height);
            
            // Background color (light pastel)
            $bg = imagecolorallocate($image, rand(200, 255), rand(200, 255), rand(200, 255));
            imagefill($image, 0, 0, $bg);
            
            // Text color (dark)
            $textColor = imagecolorallocate($image, 30, 30, 30);
            
            // Border
            imagerectangle($image, 0, 0, $width-1, $height-1, $textColor);
            
            // Add label text
            $fontSize = 5; // Built-in font size (1-5)
            $textWidth = imagefontwidth($fontSize) * strlen($label);
            $textHeight = imagefontheight($fontSize);
            
            $x = ($width - $textWidth) / 2;
            $y = ($height - $textHeight) / 2;
            
            imagestring($image, $fontSize, $x, $y, $label, $textColor);
            
            imagepng($image, $path);
            imagedestroy($image);
            $createdCount++;
        }
    }
}

echo "Generated $createdCount missing placeholder images.\n";
