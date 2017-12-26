<?php
namespace OaiPmhHarvester\Job;

use Omeka\Job\AbstractJob;

class Undo extends AbstractJob
{
    public function perform()
    {
        $jobId = $this->getArg('jobId');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $response = $api->search('oaipmhharvester_entities', ['job_id' => $jobId]);
        $harvestEntities = $response->getContent();
        if ($harvestEntities) {
            foreach ($harvestEntities as $harvestEntity) {
                $harvestResponse = $api->delete('oaipmhharvester_entities', $harvestEntity->id());
                $entityResponse = $api->delete($harvestEntity->resourceType(), $harvestEntity->entityId());
            }
        }
    }
}
