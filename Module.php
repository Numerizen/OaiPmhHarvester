<?php
namespace OaiPmhHarvester;

use Omeka\Module\AbstractModule;
use Omeka\Entity\Job;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        /* Harvested records/items.
          id: primary key
          harvest_id: the corresponding set id in `oaipmh_harvester_harvests`
          item_id: the corresponding item id in `items`
          identifier: the OAI-PMH record identifier (unique identifier)
          datestamp: the OAI-PMH record datestamp
        */
/*
        $sql = "
        CREATE TABLE IF NOT EXISTS `oaipmh_harvester_records` (
          `id` int unsigned NOT NULL auto_increment,
          `harvest_id` int unsigned NOT NULL,
          `item_id` int unsigned default NULL,
          `identifier` text NOT NULL,
          `datestamp` tinytext NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `identifier_idx` (identifier(255)),
          UNIQUE KEY `item_id_idx` (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $connection->exec($sql);
*/
        
        $sql = "
        CREATE TABLE oai_pmh_harvester_harvest_job (
          id INT AUTO_INCREMENT NOT NULL, 
          job_id INT NOT NULL, 
          undo_job_id INT DEFAULT NULL, 
          comment VARCHAR(255) DEFAULT NULL, 
          has_err TINYINT(1) NOT NULL DEFAULT 0, 
          collection_id int unsigned default NULL,
          base_url text NOT NULL,
          metadata_prefix tinytext NOT NULL,
          set_spec text,
          set_name text,
          set_description text,
          initiated datetime default NULL,
          completed datetime default NULL,
          start_from datetime default NULL,          
          resumption_token text, 
          resource_type text,                    
          UNIQUE INDEX UNIQ_17B50881BE04EA9 (job_id), 
          UNIQUE INDEX UNIQ_17B508814C276F75 (undo_job_id), 
          PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
        CREATE TABLE oai_pmh_harvester_entity (
          id INT AUTO_INCREMENT NOT NULL, 
          job_id INT NOT NULL, 
          entity_id INT NOT NULL, 
          resource_type text,            
          INDEX IDX_84D382F4BE04EA9 (job_id), 
          PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
        ";   
        $connection->exec($sql);             
/*
            ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_17B50881BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
            ALTER TABLE oai_pmh_harvester_harvest_job ADD CONSTRAINT FK_17B508814C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);
            ALTER TABLE oai_pmh_harvester_harvest_entity ADD CONSTRAINT FK_84D382F4BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);
*/
        
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        // drop the tables        
/*
        $sql = "DROP TABLE IF EXISTS `oaipmh_harvester_harvests`;";
        $connection->exec($sql);
        $sql = "DROP TABLE IF EXISTS `oaipmh_harvester_records`;";

        $connection->exec($sql);
*/        
        $sql = "DROP TABLE IF EXISTS `oai_pmh_harvester_harvest_job`;";
        $connection->exec($sql);
        $sql = "DROP TABLE IF EXISTS `oai_pmh_harvester_entity`;";
        $connection->exec($sql);
    }
}
