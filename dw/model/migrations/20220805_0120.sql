-- fix duplicates in dw_package

CREATE TABLE `dw_package_new` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              `package_type_file_id` bigint(20) unsigned NOT NULL,
                              `name` varchar(100) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
                              `first_id` bigint(20) unsigned NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';

ALTER TABLE `dw_package_new` ADD INDEX `idx_package_type_file_id` (`package_type_file_id`);
ALTER TABLE `dw_package_new` ADD INDEX `idx_name` (`name`);

ALTER TABLE `dw_package`
    ADD `first_id` bigint(20) unsigned;

INSERT INTO dw_package_new (package_type_file_id, name, first_id)
SELECT dw_package.package_type_file_id, dw_package.name, MIN(dw_package.id)
FROM dw_package
GROUP BY dw_package.package_type_file_id, dw_package.name;

UPDATE dw_package
INNER JOIN dw_package_new ON dw_package.package_type_file_id = dw_package_new.package_type_file_id AND dw_package.name = dw_package_new.name
SET dw_package.first_id = dw_package_new.first_id;

UPDATE dw_repository_package
INNER JOIN dw_package ON dw_repository_package.package_id = dw_package.id
SET dw_repository_package.package_id = dw_package.first_id;

DROP TABLE dw_package_new;

DELETE FROM dw_package WHERE id != first_id;

ALTER TABLE dw_package
  DROP COLUMN first_id;
