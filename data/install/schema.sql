 CREATE TABLE oai_pmh_harvester_harvest_job (
    id INT AUTO_INCREMENT NOT NULL,
    job_id INT NOT NULL,
    undo_job_id INT DEFAULT NULL,
    item_set_id INT DEFAULT NULL,
    `comment` LONGTEXT DEFAULT NULL,
    resource_type VARCHAR(190) NOT NULL,
    base_url VARCHAR(190) NOT NULL,
    metadata_prefix VARCHAR(190) NOT NULL,
    set_spec VARCHAR(190),
    set_name LONGTEXT,
    set_description LONGTEXT,
    initiated TINYINT(1) DEFAULT NULL,
    completed TINYINT(1) DEFAULT NULL,
    has_err TINYINT(1) NOT NULL,
    start_from DATETIME DEFAULT NULL,
    resumption_token VARCHAR(190) DEFAULT NULL,
    UNIQUE INDEX UNIQ_FC86A2F2BE04EA9 (job_id),
    UNIQUE INDEX UNIQ_FC86A2F24C276F75 (undo_job_id),
    INDEX IDX_FC86A2F2960278D7 (item_set_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE oai_pmh_harvester_entity (
    id INT AUTO_INCREMENT NOT NULL,
    job_id INT NOT NULL,
    entity_id INT NOT NULL,
    resource_type VARCHAR(190) NOT NULL,
    INDEX IDX_EEA09D7FBE04EA9 (job_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_FC86A2F2BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_FC86A2F24C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);
ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_FC86A2F2960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;
ALTER TABLE oai_pmh_harvester_entity ADD CONSTRAINT FK_EEA09D7FBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
