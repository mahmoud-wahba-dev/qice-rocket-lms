<?php
/**
 * One-off schema check (run on server): php scripts/check-webinar-extra-schema.php
 */
$root = dirname(__DIR__);
$lines = file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$cfg = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $cfg[trim($k)] = trim($v, " \t\"'");
}

$pdo = new PDO(
    "mysql:host={$cfg['DB_HOST']};dbname={$cfg['DB_DATABASE']};charset=utf8mb4",
    $cfg['DB_USERNAME'],
    $cfg['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "webinar_extra_descriptions columns:\n";
foreach ($pdo->query('SHOW COLUMNS FROM webinar_extra_descriptions') as $row) {
    echo '  - ' . $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\nwebinar_extra_description_translations: ";
try {
    $pdo->query('SELECT 1 FROM webinar_extra_description_translations LIMIT 1');
    echo "exists\n";
} catch (Throwable $e) {
    echo "MISSING\n";
}
