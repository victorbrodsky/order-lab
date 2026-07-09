<?php
$dir = __DIR__ . '/src/App/UserdirectoryBundle/Entity';
$files = glob($dir . '/*.php');
$changed = 0;
$skipped = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'extends ListAbstract') === false) {
        continue;
    }

    $newContent = preg_replace_callback(
        "/(#[\\s]*ORM[\\s]*Table[\\s]*\\([^)]*name[\\s]*:[\\s]*['\"])([^'\"]+)(['\"][^)]*\\))/i",
        function ($matches) {
            return $matches[1] . strtolower($matches[2]) . $matches[3];
        },
        $content
    );

    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Lowercased table name in: " . basename($file) . "\n";
        $changed++;
    } else {
        echo "Skipped (no Table attribute or already lowercase): " . basename($file) . "\n";
        $skipped++;
    }
}

echo "\nDone. Changed: $changed, Skipped: $skipped\n";
