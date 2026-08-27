<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = <<<JS
            const opts = this.currentQuestion.options.map((o, i) => ({
                text: o.text,
                correctIndex: i,
            }));
JS;

$replace = <<<JS
            const opts = this.currentQuestion.options.map((o, i) => ({
                text: o.text,
                image: o.image,
                correctIndex: i,
            }));
JS;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added image to seqCards initialization.\n";
