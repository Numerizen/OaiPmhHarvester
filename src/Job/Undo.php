<?php declare(strict_types=1);

namespace OaiPmhHarvester\Job;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Job\AbstractJob;
use Omeka\Stdlib\Message;

class Undo extends AbstractJob
{
    public function perform(): void
    {
        /** @var \Omeka\Api\Manager $api */
        $services = $this->getServiceLocator();
        $logger = $services->get('Omeka\Logger');
        $api = $services->get('Omeka\ApiManager');

        $jobId = $this->getArg('jobId');
        $harvestEntityIds = $api->search('oaipmhharvester_entities', ['job_id' => $jobId], ['returnScalar' => 'entityId'])->getContent();
        if (!$harvestEntityIds) {
            return;
        }
        $harvestEntityNames = $api->search('oaipmhharvester_entities', ['job_id' => $jobId], ['returnScalar' => 'entityName'])->getContent();

        $index = 0;
        foreach (array_chunk($harvestEntityIds, 100, true) as $chunk) {
            if ($this->shouldStop()) {
                $logger->warn(new Message(
                    'The job "Undo" was stopped: %d/%d resources processed.', // @translate
                    $index, count($harvestEntityIds)
                ));
                break;
            }
            try {
                $harvestIds = array_keys($chunk);
                // For now, entity name is always "items".
                do {
                    $entityIds = [];
                    $entityName = $harvestEntityNames[key($chunk)];
                    foreach ($chunk as $harvestId => $entityId) {
                        if ($harvestEntityNames[$harvestId] === $entityName) {
                            $entityIds[] = $entityId;
                            unset($chunk[$harvestId]);
                        }
                    }
                    $api->batchDelete($entityName, $entityIds);
                } while (count($chunk));
                $api->batchDelete('oaipmhharvester_entities', $harvestIds);
            } catch (NotFoundException $e) {
            }
            $index += 100;
        }
    }
}
