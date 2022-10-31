CREATE TABLE `oaipmhharvester_harvest` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `job_id` INT NOT NULL,
    `undo_job_id` INT DEFAULT NULL,
    `item_set_id` INT DEFAULT NULL,
    `message` LONGTEXT DEFAULT NULL,
    `endpoint` VARCHAR(190) NOT NULL,
    `entity_name` VARCHAR(190) NOT NULL,
    `metadata_prefix` VARCHAR(190) NOT NULL,
    `set_spec` VARCHAR(190) DEFAULT NULL,
    `set_name` LONGTEXT DEFAULT NULL,
    `set_description` LONGTEXT DEFAULT NULL,
    `has_err` TINYINT(1) NOT NULL,
    `stats` LONGTEXT NOT NULL COMMENT '(DC2Type:json)',
    `resumption_token` VARCHAR(190) DEFAULT NULL,
    UNIQUE INDEX UNIQ_929CA732BE04EA9 (`job_id`),
    UNIQUE INDEX UNIQ_929CA7324C276F75 (`undo_job_id`),
    INDEX IDX_929CA732960278D7 (`item_set_id`),
    PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE `oaipmhharvester_entity` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `job_id` INT NOT NULL,
    `entity_id` INT NOT NULL,
    `entity_name` VARCHAR(190) NOT NULL,
    `identifier` LONGTEXT NOT NULL,
    `created` DATETIME NOT NULL,
    INDEX IDX_FE902C0EBE04EA9 (`job_id`),
    INDEX identifier_idx (`identifier`(767)),
    PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE `oaipmhharvester_harvest` ADD CONSTRAINT FK_929CA732BE04EA9 FOREIGN KEY (`job_id`) REFERENCES `job` (`id`) ON DELETE CASCADE;
ALTER TABLE `oaipmhharvester_harvest` ADD CONSTRAINT FK_929CA7324C276F75 FOREIGN KEY (`undo_job_id`) REFERENCES `job` (`id`) ON DELETE SET NULL;
ALTER TABLE `oaipmhharvester_harvest` ADD CONSTRAINT FK_929CA732960278D7 FOREIGN KEY (`item_set_id`) REFERENCES `item_set` (`id`) ON DELETE SET NULL;
ALTER TABLE `oaipmhharvester_entity` ADD CONSTRAINT FK_FE902C0EBE04EA9 FOREIGN KEY (`job_id`) REFERENCES `job` (`id`) ON DELETE CASCADE;
