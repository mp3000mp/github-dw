ALTER TABLE `dw_repository_package_type_file`
ADD UNIQUE `idx_uniq` (`repository_id`, `package_type_file_id`, `path`);

ALTER TABLE `dw_package`
ADD UNIQUE `idx_uniq` (`package_type_file_id`, `name`);
