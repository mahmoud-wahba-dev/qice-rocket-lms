# Fix: admin مواد التعلم returns HTTP 500

If `/admin/webinar-extra-description/store` still fails after the controller fix, run this in **phpMyAdmin** on the production database.

```sql
ALTER TABLE `webinar_extra_descriptions`
    MODIFY COLUMN `webinar_id` INT UNSIGNED NULL;

-- Add upcoming_course_id if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'webinar_extra_descriptions'
      AND COLUMN_NAME = 'upcoming_course_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `webinar_extra_descriptions` ADD COLUMN `upcoming_course_id` INT UNSIGNED NULL AFTER `webinar_id`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add event_id if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'webinar_extra_descriptions'
      AND COLUMN_NAME = 'event_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `webinar_extra_descriptions` ADD COLUMN `event_id` INT UNSIGNED NULL AFTER `upcoming_course_id`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `webinar_extra_description_translations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `webinar_extra_description_id` INT UNSIGNED NOT NULL,
    `locale` VARCHAR(191) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `webinar_extra_description_translations_locale_index` (`locale`),
    KEY `webinar_extra_description_id_foreign` (`webinar_extra_description_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Also check

1. Save the course first — `webinar_id` must exist (edit page, not brand-new unsaved course).
2. Hard refresh admin after deploy (`Ctrl+Shift+R`).
3. If it still fails, check `storage/logs/laravel.log` for `webinar-extra-description store failed`.
