ALTER TABLE `dw_package`
    ADD `nb` int unsigned NOT NULL DEFAULT 0 AFTER `name`;

ALTER TABLE `dw_package`
DROP INDEX `ft_name`;
