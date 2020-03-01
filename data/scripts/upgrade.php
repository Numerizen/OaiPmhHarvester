<?php
namespace OaiPmhHarvester;

/**
 * @var Module $this
 * @var \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Api\Manager $api
 */
$services = $serviceLocator;
$settings = $services->get('Omeka\Settings');
$config = require dirname(dirname(__DIR__)) . '/config/module.config.php';
$connection = $services->get('Omeka\Connection');
$entityManager = $services->get('Omeka\EntityManager');
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$space = strtolower(__NAMESPACE__);

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
    $connection->exec($sql);
}

if (version_compare($oldVersion, '3.0.6', '<')) {
    // It's simpler to fill the new table than to manage complex drop/alter/change…
    $filepath = dirname(__DIR__) . '/install/schema.sql';
    $sql = file_get_contents($filepath);
    $connection->exec($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
INSERT INTO oaipmhharvester_harvest
(id, job_id, undo_job_id, item_set_id, `comment`, base_url, resource_type, metadata_prefix, set_spec, set_name, set_description, has_err, resumption_token)
SELECT id, job_id, undo_job_id, collection_id, `comment`, base_url, resource_type, metadata_prefix, set_spec, set_name, set_description, has_err, resumption_token
FROM oai_pmh_harvester_harvest_job;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->exec($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
INSERT INTO oaipmhharvester_entity
(id, job_id, entity_id, resource_type)
SELECT id, job_id, entity_id, resource_type
FROM oai_pmh_harvester_entity;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->exec($sql);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS oai_pmh_harvester_harvest_job;
DROP TABLE IF EXISTS oai_pmh_harvester_entity;
SET FOREIGN_KEY_CHECKS=1;
SQL;
    $connection->exec($sql);

    $sql = <<<'SQL'
UPDATE job
SET class="OaiPmhHarvester\\Job\\Harvest"
WHERE class="OaiPmhHarvester\\Job\\HarvestJob";
SQL;
    $connection->exec($sql);
}
