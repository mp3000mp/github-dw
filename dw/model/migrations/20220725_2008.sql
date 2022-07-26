CREATE TABLE `dw_package` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              `package_type_file_id` bigint(20) unsigned NOT NULL,
                              `name` varchar(100) COLLATE 'utf8mb4_unicode_ci' NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';

INSERT INTO dw_package (package_type_file_id, name)
SELECT dw_repository_package_type_file.package_type_file_id, name
FROM dw_repository_package
INNER JOIN dw_repository_package_type_file ON dw_repository_package.repository_package_type_file_id = dw_repository_package_type_file.id;

ALTER TABLE `dw_package`
    ADD FOREIGN KEY `fk_dw_package_type_file_id` (`package_type_file_id`) REFERENCES `dw_package_type_file` (`id`) ON DELETE CASCADE;

ALTER TABLE `dw_repository_package`
    ADD `package_id` bigint(20) unsigned NOT NULL AFTER `repository_package_type_file_id`;

UPDATE dw_repository_package
INNER JOIN dw_package ON dw_package.name = dw_repository_package.name
SET dw_repository_package.package_id = dw_package.id;

ALTER TABLE `dw_repository_package`
    ADD FOREIGN KEY `fk_dw_package_id` (`package_id`) REFERENCES `dw_package` (`id`) ON DELETE CASCADE;

ALTER TABLE `dw_repository_package`
DROP `name`;

ALTER TABLE `dw_repository_package`
    ADD `repository_id` bigint(20) unsigned NOT NULL AFTER `package_id`;

UPDATE dw_repository_package
    INNER JOIN dw_repository_package_type_file ON dw_repository_package_type_file.id = dw_repository_package.repository_package_type_file_id
    SET dw_repository_package.repository_id = dw_repository_package_type_file.repository_id;

ALTER TABLE `dw_repository_package`
    ADD FOREIGN KEY `fk_dw_repository_id` (`repository_id`) REFERENCES `dw_repository` (`id`) ON DELETE CASCADE;

ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_min_major` (`version_min_major`);
ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_min_minor` (`version_min_minor`);
ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_min_patch` (`version_min_patch`);
ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_max_major` (`version_max_major`);
ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_max_minor` (`version_max_minor`);
ALTER TABLE `dw_repository_package` ADD INDEX `idx_version_max_patch` (`version_max_patch`);
ALTER TABLE `dw_repository` ADD FULLTEXT `ft_description` (`description`);
ALTER TABLE `dw_repository` ADD INDEX `idx_name` (`name`);
ALTER TABLE `dw_package` ADD INDEX `idx_name` (`name`);
