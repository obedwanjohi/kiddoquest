<?php

$dirs = [
    'c:/xampp/htdocs/kid/database/csv_imports/play_group_english',
    'c:/xampp/htdocs/kid/database/csv_imports/play_group_cre',
    'c:/xampp/htdocs/kid/database/csv_imports/play_group_math',
];

$totalFixedFiles = 0;
$totalFixedRows = 0;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '/*.csv');
    foreach ($files as $file) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if (!$lines) continue;
        
        $header = str_getcsv($lines[0]);
        $modified = false;
        $newRows = [$lines[0]];
        
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (trim($line) === '') continue;
            
            $row = str_getcsv($line);
            if (empty($row)) continue;
            
            if (trim($row[0]) === 'matching') {
                $rowStr = implode(',', $row);
                if (str_contains($rowStr, 'secret') || str_contains($rowStr, 'ops')) {
                    $modified = true;
                    $totalFixedRows++;
                    
                    // Extract non-empty non-placeholder items after col 3
                    $pairs = [];
                    for ($j = 3; $j < count($row); $j++) {
                        $item = trim($row[$j]);
                        if ($item !== '' && !in_array($item, ['secret', '1 secret', 'ops'])) {
                            $pairs[] = $item;
                        }
                    }
                    
                    // Remove leading '1' if it was part of correct_answer
                    if (count($pairs) > 0 && $pairs[0] === '1') {
                        array_shift($pairs);
                    }
                    
                    $cleanRow = [
                        trim($row[0]), // matching
                        trim($row[1]), // lesson_title
                        trim($row[2]), // prompt
                        '', '', '1', '', '', '', '', '', '', ''
                    ];
                    
                    foreach ($pairs as $p) {
                        $cleanRow[] = $p;
                    }
                    
                    while (count($cleanRow) < 19) {
                        $cleanRow[] = '';
                    }
                    $cleanRow = array_slice($cleanRow, 0, 19);
                    
                    // Re-encode CSV line
                    $fp = fopen('php://temp', 'r+');
                    fputcsv($fp, $cleanRow);
                    rewind($fp);
                    $newCsvLine = rtrim(stream_get_contents($fp), "\r\n");
                    fclose($fp);
                    
                    $newRows[] = $newCsvLine;
                } else {
                    $newRows[] = $line;
                }
            } else {
                $newRows[] = $line;
            }
        }
        
        if ($modified) {
            file_put_contents($file, implode("\n", $newRows) . "\n");
            $totalFixedFiles++;
            echo "Cleaned: " . basename($file) . "\n";
        }
    }
}

echo "FINISHED! Cleaned $totalFixedRows matching rows across $totalFixedFiles CSV files.\n";
