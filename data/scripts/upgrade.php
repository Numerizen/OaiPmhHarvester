<?php declare(strict_types=1);

namespace OaiPmhHarvester;

use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (version_compare($oldVersion, '3.0.3', '<')) {
    $sql = <<<'SQL'
ALTER TABLE oai_pmh_harvester_harvest_job
    CHANGE undo_job_id undo_job_id INT DEFAULT NULL,
    CHANGE comment comment VARCHAR(255) DEFAULT NULL,
    CHANGE has_err has_err TINYINT(1) NOT NULL,
    CHANGE resource_type resource_type VARCHAR(255) NOT NULL AFTER has_err,
    CHANGE collection_id collection_id INT NOT NULL,
    CHANGE base_url base_url VARCHAR(255) NOT NULL,
    CHANGE metadata_prefix metadata_prefix VARCHAR(255) NOT NULL,
    CHANGE set_spec set_spec VARCHAR(255) DEFAULT NULL,
    CHANGE set_name set_name VARCHAR(255) NOT NULL,
    CHANGE set_description set_description VARCHAR(255) DEFAULT NULL,
    CHANGE initiated initiated INT DEFAULT NULL,
    CHANGE completed completed INT DEFAULT NULL,
    CHANGE start_from start_from VARCHAR(255) DEFAULT NULL,
    CHANGE resumption_token resumption_token VARCHAR(255) DEFAULT NULL;

ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_FC86A2F2BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_FC86A2F24C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);

DROP INDEX uniq_17b50881be04ea9 ON oai_pmh_harvester_harvest_job;
CREATE UNIQUE INDEX UNIQ_FC86A2F2BE04EA9 ON oai_pmh_harvester_harvest_job (job_id);

DROP INDEX uniq_17b508814c276f75 ON oai_pmh_harvester_harvest_job;
CREATE UNIQUE INDEX UNIQ_FC86A2F24C276F75 ON oai_pmh_harvester_harvest_job (undo_job_id);

ALTER TABLE oai_pmh_harvester_entity
    CHANGE resource_type resource_type VARCHAR(255) NOT NULL;

ALTER TABLE oai_pmh_harvester_entity ADD CONSTRAINT FK_EEA09D7FBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);

DROP INDEX idx_84d382f4be04ea9 ON oai_pmh_harvester_entity;
CREATE INDEX IDX_EEA09D7FBE04EA9 ON oai_pmh_harvester_entity (job_id);
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.0.6', '<')) {
    // It's simpler to fill the new table than to manage complex drop/alter/change…

    $sql = <<<SQL
CREATE TABLE oaipmhharvester_harvest (
    id INT AUTO_INCREMENT NOT NULL,
    job_id INT NOT NULL,
    undo_job_id INT DEFAULT NULL,
    item_set_id INT DEFAULT NULL,
    `comment` LONGTEXT DEFAULT NULL,
    endpoint VARCHAR(190) NOT NULL,
    resource_type VARCHAR(190) NOT NULL,
    metadata_prefix VARCHAR(190) NOT NULL,
    set_spec VARCHAR(190) DEFAULT NULL,
    set_name LONGTEXT DEFAULT NULL,
    set_description LONGTEXT DEFAULT NULL,
    has_err TINYINT(1) NOT NULL,
    resumption_token VARCHAR(190) DEFAULT NULL,
    UNIQUE INDEX UNIQ_929CA732BE04EA9 (job_id),
    UNIQUE INDEX UNIQ_929CA7324C276F75 (undo_job_id),
    INDEX IDX_929CA732960278D7 (item_set_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE oaipmhharvester_entity (
    id INT AUTO_INCREMENT NOT NULL,
    job_id INT NOT NULL,
    entity_id INT NOT NULL,
    resource_type VARCHAR(190) NOT NULL,
    INDEX IDX_FE902C0EBE04EA9 (job_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE oaipmhharvester_harvest ADD CONSTRAINT FK_929CA732BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
ALTER TABLE oaipmhharvester_harvest ADD CONSTRAINT FK_929CA7324C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);
ALTER TABLE oaipmhharvester_harvest ADD CONSTRAINT FK_929CA732960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;
ALTER TABLE oaipmhharvester_entity ADD CONSTRAINT FK_FE902C0EBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
SQL;

    $connection->executeStatement($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
INSERT INTO oaipmhharvester_harvest
(id, job_id, undo_job_id, item_set_id, `comment`, endpoint, resource_type, metadata_prefix, set_spec, set_name, set_description, has_err, resumption_token)
SELECT id, job_id, undo_job_id, collection_id, `comment`, base_url, resource_type, metadata_prefix, set_spec, set_name, set_description, has_err, resumption_token
FROM oai_pmh_harvester_harvest_job;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->executeStatement($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
INSERT INTO oaipmhharvester_entity
(id, job_id, entity_id, resource_type)
SELECT id, job_id, entity_id, resource_type
FROM oai_pmh_harvester_entity;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->executeStatement($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS oai_pmh_harvester_harvest_job;
DROP TABLE IF EXISTS oai_pmh_harvester_entity;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->executeStatement($sql);

    $sql = <<<'SQL'
UPDATE job
SET class="OaiPmhHarvester\\Job\\Harvest"
WHERE class="OaiPmhHarvester\\Job\\HarvestJob";
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.0.6.1', '<')) {
    $sql = <<<'SQL'
ALTER TABLE oaipmhharvester_harvest
    ADD stats LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)' AFTER has_err ;
SQL;
    $connection->executeStatement($sql);

    $sql = <<<'SQL'
UPDATE oaipmhharvester_harvest
SET stats = "{}";
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.3.0.7', '<')) {
    $sql = <<<'SQL'
ALTER TABLE `oaipmhharvester_harvest`
CHANGE `stats` `stats` LONGTEXT NOT NULL COMMENT '(DC2Type:json)';
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.3.0.10', '<')) {
    $sql = <<<'SQL'
ALTER TABLE `oaipmhharvester_harvest` CHANGE `comment` `message` LONGTEXT DEFAULT NULL;
ALTER TABLE `oaipmhharvester_harvest` CHANGE `resource_type` `entity_name` VARCHAR(190) NOT NULL;
ALTER TABLE `oaipmhharvester_entity` CHANGE `resource_type` `entity_name` VARCHAR(190) NOT NULL;
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.3.0.12', '<')) {
    // Fix keys on some database.
    $sql = <<<'SQL'
ALTER TABLE `oaipmhharvester_harvest` DROP FOREIGN KEY FK_929CA7324C276F75;
ALTER TABLE `oaipmhharvester_harvest` DROP FOREIGN KEY FK_929CA732BE04EA9;
ALTER TABLE `oaipmhharvester_harvest` ADD CONSTRAINT FK_929CA7324C276F75 FOREIGN KEY (`undo_job_id`) REFERENCES `job` (`id`) ON DELETE SET NULL;
ALTER TABLE `oaipmhharvester_harvest` ADD CONSTRAINT FK_929CA732BE04EA9 FOREIGN KEY (`job_id`) REFERENCES `job` (`id`) ON DELETE CASCADE;
ALTER TABLE `oaipmhharvester_entity` DROP FOREIGN KEY FK_FE902C0EBE04EA9;
ALTER TABLE `oaipmhharvester_entity` ADD CONSTRAINT FK_FE902C0EBE04EA9 FOREIGN KEY (`job_id`) REFERENCES `job` (`id`) ON DELETE CASCADE;
SQL;
    try {
        $connection->executeStatement($sql);
    } catch (\Exception $e) {
        // Nothing.
    }

    $sql = <<<'SQL'
ALTER TABLE `oaipmhharvester_entity`
    ADD `identifier` LONGTEXT NOT NULL AFTER `entity_name`,
    ADD `created` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `identifier`;
ALTER TABLE `oaipmhharvester_entity` CHANGE `created` `created` DATETIME NOT NULL AFTER `identifier`;
CREATE INDEX identifier_idx ON `oaipmhharvester_entity` (`identifier`(767));
SQL;
    $connection->executeStatement($sql);
}
