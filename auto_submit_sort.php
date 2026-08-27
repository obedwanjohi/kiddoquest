<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = <<<JS
        selectSortBucket(bucketIndex) {
            this.resetIdleTimer();
            if (this.sortAnswered) return;
            if (this.sortSelectedChip === null) return;

            const categoryName = this.sortCategories[bucketIndex];

            // Place the selected chip into this bucket
            this.sortChips[this.sortSelectedChip].bucket = categoryName;
            this.sortSelectedChip = null;
        },
JS;

$replace = <<<JS
        selectSortBucket(bucketIndex) {
            this.resetIdleTimer();
            if (this.sortAnswered) return;
            if (this.sortSelectedChip === null) return;

            const categoryName = this.sortCategories[bucketIndex];

            // Place the selected chip into this bucket
            this.sortChips[this.sortSelectedChip].bucket = categoryName;
            this.sortSelectedChip = null;

            // Auto-submit when all chips are sorted
            if (!this.sortChips.some(c => c.bucket === null)) {
                setTimeout(() => { this.checkSorting(); }, 200);
            }
        },
JS;

// Try str_replace first
$newContent = str_replace($search, $replace, $content);

// If str_replace failed due to whitespace, we'll fallback to a regex
if ($newContent === $content) {
    // Escape and handle whitespace flexibly
    $pattern = '/selectSortBucket\(bucketIndex\)\s*\{[^\}]*this\.sortSelectedChip = null;\s*\}/s';
    $newContent = preg_replace($pattern, $replace, $content);
}

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "SUCCESS: Added auto-submit to selectSortBucket.\n";
} else {
    echo "FAILED: Could not find target pattern in engine.blade.php.\n";
}
