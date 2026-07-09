<?php
$dir = __DIR__ . '/src';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$found = [];

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $i => $line) {
        // skip commented lines (rough check for // at start of non-whitespace)
        $trimmed = ltrim($line);
        if (strpos($trimmed, '//') === 0) {
            continue;
        }
        if (preg_match('/roles\s+(NOT\s+)?LIKE/i', $line)) {
            $found[] = [
                'file' => str_replace(__DIR__ . '/', '', $path),
                'line' => $i + 1,
                'content' => trim($line),
            ];
        }
    }
}

foreach ($found as $item) {
    echo $item['file'] . ':' . $item['line'] . '  ' . $item['content'] . "\n";
}

echo "\nTotal: " . count($found) . "\n";
