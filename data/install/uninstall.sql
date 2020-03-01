SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE oai_pmh_harvester_harvest_job DROP FOREIGN KEY FK_FC86A2F24C276F75;
ALTER TABLE oai_pmh_harvester_harvest_job DROP FOREIGN KEY FK_FC86A2F2BE04EA9;
DROP TABLE oai_pmh_harvester_entity;
DROP TABLE oai_pmh_harvester_harvest_job;
SET FOREIGN_KEY_CHECKS=1;
