<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Fix broken image URLs in options
$brokenOptions = App\Models\QuestionOption::where('image_url', 'like', '%/images/questions/pat_%')->get();
foreach ($brokenOptions as $opt) {
    // If the file does not exist, set to null
    $path = public_path($opt->image_url);
    if (!file_exists($path)) {
        $opt->image_url = null;
        $opt->save();
        echo "Set image_url to null for option ID: {$opt->id}\n";
    }
}

// 2. Fix engine.blade.php pattern gap
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = <<<EOT
                          <div class="pattern-strip">
                              <template x-for="(item, i) in (currentQuestion.metadata?.sequence || [])" :key="'seq-' + i">
EOT;

$replace = <<<EOT
                          <div class="pattern-strip" x-show="(currentQuestion.metadata?.sequence || []).length > 0">
                              <template x-for="(item, i) in (currentQuestion.metadata?.sequence || [])" :key="'seq-' + i">
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);

echo "Updated engine.blade.php pattern gap\n";
