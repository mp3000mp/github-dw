ALTER TABLE `dw_repository`
    CHANGE `description` `description` varchar(4000) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `routine_error`;
