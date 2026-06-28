<?php
/**
 * One-off schema fix (run on server): php scripts/fix-webinar-extra-schema.php
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

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

$pdo->exec('ALTER TABLE `webinar_extra_descriptions` MODIFY COLUMN `webinar_id` INT UNSIGNED NULL');
echo "OK: webinar_id nullable\n";

if (!columnExists($pdo, 'webinar_extra_descriptions', 'upcoming_course_id')) {
    $pdo->exec('ALTER TABLE `webinar_extra_descriptions` ADD COLUMN `upcoming_course_id` INT UNSIGNED NULL AFTER `webinar_id`');
    echo "OK: added upcoming_course_id\n";
} else {
    echo "SKIP: upcoming_course_id exists\n";
}

if (!columnExists($pdo, 'webinar_extra_descriptions', 'event_id')) {
    $pdo->exec('ALTER TABLE `webinar_extra_descriptions` ADD COLUMN `event_id` INT UNSIGNED NULL AFTER `upcoming_course_id`');
    echo "OK: added event_id\n";
} else {
    echo "SKIP: event_id exists\n";
}

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `webinar_extra_description_translations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `webinar_extra_description_id` INT UNSIGNED NOT NULL,
    `locale` VARCHAR(191) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `webinar_extra_description_translations_locale_index` (`locale`),
    KEY `webinar_extra_description_id_foreign` (`webinar_extra_description_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
echo "OK: translations table ensured\n";

echo "Done.\n";
