<?php declare(strict_types=1);
namespace OaiPmhHarvester\Job;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Job\AbstractJob;
use Omeka\Stdlib\Message;

class Undo extends AbstractJob
{
    public function perform(): void
    {
        // TODO Improve memory management for deletion of previous harvest and allow to stop.

        /** @var \Omeka\Api\Manager $api */
        $services = $this->getServiceLocator();
        $logger = $services->get('Omeka\Logger');
        $api = $services->get('Omeka\ApiManager');

        $jobId = $this->getArg('jobId');
        $harvestEntities = $api->search('oaipmhharvester_entities', ['job_id' => $jobId])->getContent();
        if (!$harvestEntities) {
            return;
        }

        foreach ($harvestEntities as $key => $harvestEntity) {
            if ($this->shouldStop()) {
                $logger->warn(new Message(
                    'The job "Undo" was stopped: %d/%d resources processed.', // @translate
                    $key, count($harvestEntities)
                ));
                break;
            }
            try {
                $api->delete('oaipmhharvester_entities', $harvestEntity->id());
                $api->delete($harvestEntity->resourceType(), $harvestEntity->entityId());
            } catch (NotFoundException $e) {
            }
        }
    }
}
