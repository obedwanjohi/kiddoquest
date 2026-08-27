<?php
$file = __DIR__ . '/seed_mega_mission.php';
$content = file_get_contents($file);

$content = str_replace(
    "'adventure_world_id' => \$world->id,",
    "'lesson_id' => 1,\n        'adventure_world_id' => \$world->id,",
    $content
);

file_put_contents($file, $content);
