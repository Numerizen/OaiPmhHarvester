<?php declare(strict_types=1);

namespace OaiPmhHarvester\Job;

use Omeka\Api\Representation\AbstractRepresentation;
use Omeka\Job\AbstractJob;
use Omeka\Stdlib\Message;

class Harvest extends AbstractJob
{
    /**
     * Date format for OAI-PMH requests.
     * Only use day-level granularity for maximum compatibility with
     * repositories.
     */
    const OAI_DATE_FORMAT = 'Y-m-d';

    const BATCH_CREATE_SIZE = 20;

    /**
     * @var \Omeka\Api\Manager
     */
    protected $api;

    /**
     * @var \Laminas\Log\Logger
     */
    protected $logger;

    protected $hasErr = false;

    public function perform()
    {
        $services = $this->getServiceLocator();
        $this->logger = $services->get('Omeka\Logger');
        $this->api = $services->get('Omeka\ApiManager');

        $harvesterMapManager = $services->get(\OaiPmhHarvester\OaiPmh\HarvesterMapManager::class);

        $args = $this->job->getArgs();
        $itemSetId = empty($args['item_set_id']) ? null : (int) $args['item_set_id'];
        $whitelist = $args['filters']['whitelist'] ?? [];
        $blacklist = $args['filters']['blacklist'] ?? [];

        $message = null;
        $stats = [
            'records' => null, // @translate
            'harvested' => 0, // @translate
            'whitelisted' => 0, // @translate
            'blacklisted' => 0, // @translate
            'imported' => 0, // @translate
        ];

        $harvestData = [
            'o:job' => ['o:id' => $this->job->getId()],
            'o:undo_job' => null,
            'o-module-oai-pmh-harvester:message' => 'Harvesting started', // @translate
            'o-module-oai-pmh-harvester:entity_name' => $this->getArg('entity_name', 'items'),
            'o-module-oai-pmh-harvester:endpoint' => $args['endpoint'],
            'o:item_set' => ['o:id' => $args['item_set_id']],
            'o-module-oai-pmh-harvester:metadata_prefix' => $args['metadata_prefix'],
            'o-module-oai-pmh-harvester:set_spec' => $args['set_spec'],
            'o-module-oai-pmh-harvester:set_name' => $args['set_name'],
            'o-module-oai-pmh-harvester:set_description' => $args['set_description'] ?? null,
            'o-module-oai-pmh-harvester:has_err' => false,
            'o-module-oai-pmh-harvester:stats' => $stats,
        ];

        /** @var \OaiPmhHarvester\Api\Representation\HarvestRepresentation $harvest */
        $harvest = $this->api->create('oaipmhharvester_harvests', $harvestData)->getContent();
        $harvestId = $harvest->id();

        $metadataPrefix = $args['metadata_prefix'] ?? null;
        if (!$metadataPrefix || !$harvesterMapManager->has($metadataPrefix)) {
            $this->logger->err(sprintf(
                'The format "%s" is not managed by the module currently.', // @translate
                $metadataPrefix
            ));
            $this->api->update('oaipmhharvester_harvests', $harvestId, ['o-module-oai-pmh-harvester:has_err' => true]);
            return false;
        }

        /** @var \OaiPmhHarvester\OaiPmh\HarvesterMap\HarvesterMapInterface $harvesterMap */
        $harvesterMap = $harvesterMapManager->get($metadataPrefix);
        $harvesterMap->setOptions([
            'o:is_public' => !$services->get('Omeka\Settings')->get('default_to_private', false),
            'o:item_set' => $itemSetId ? [['o:id' => $itemSetId]] : [],
        ]);

        $resumptionToken = false;
        do {
            if ($this->shouldStop()) {
                $this->logger->notice(new Message(
                    'Results: total records = %1$s, harvested = %2$d, whitelisted = %3$d, blacklisted = %4$d, imported = %5$d.', // @translate
                    $stats['records'], $stats['harvested'], $stats['whitelisted'], $stats['blacklisted'], $stats['imported']
                ));
                $this->logger->warn(new Message(
                    'The job was stopped.' // @translate
                ));
                return false;
            }

            if ($resumptionToken) {
                $url = $args['endpoint'] . "?verb=ListRecords&resumptionToken=$resumptionToken";
            } else {
                $url = $args['endpoint'] . '?verb=ListRecords'
                    . (isset($args['set_spec']) && strlen((string) $args['set_spec']) ? '&set=' . $args['set_spec'] : '')
                    . "&metadataPrefix=$metadataPrefix";
            }

            /** @var \SimpleXMLElement $response */
            $response = simplexml_load_file($url);
            if (!$response) {
                $this->hasErr = true;
                $message = 'Error.'; // @translate
                $this->logger->err(new Message(
                    'Error: the harvester does not list records with url %s.', // @translate
                    $url
                ));
                break;
            }

            if (!$response->ListRecords) {
                $this->hasErr = true;
                $message = 'Error.'; // @translate
                $this->logger->err(new Message(
                    'Error: the harvester does not list records with url %s.', // @translate
                    $url
                ));
                break;
            }

            $records = $response->ListRecords;

            if (is_null($stats['records'])) {
                $stats['records'] = isset($response->ListRecords->resumptionToken)
                    ? (int) $records->resumptionToken['completeListSize']
                    : count($response->ListRecords->record);
            }

            $toInsert = [];
            /** @var \SimpleXMLElement $record */
            foreach ($records->record as $record) {
                ++$stats['harvested'];
                if ($whitelist || $blacklist) {
                    // Use xml instead of string because some formats may use
                    // attributes for data.
                    $recordString = $record->asXML();
                    foreach ($whitelist as $string) {
                        if (mb_stripos($recordString, $string) === false) {
                            ++$stats['whitelisted'];
                            continue 2;
                        }
                    }
                    foreach ($blacklist as $string) {
                        if (mb_stripos($recordString, $string) !== false) {
                            ++$stats['blacklisted'];
                            continue 2;
                        }
                    }
                }
                // A record can be mapped to multiple resources, cf. ead.
                $resources = $harvesterMap->mapRecord($record);
                foreach ($resources as $resource) {
                    $toInsert[] = $resource;
                    ++$stats['imported'];
                }
            }

            $this->createItems($toInsert);

            $resumptionToken = isset($response->ListRecords->resumptionToken) && $response->ListRecords->resumptionToken <> ''
                ? $response->ListRecords->resumptionToken
                : false;

            // Update job.
            $harvestData = [
                'o-module-oai-pmh-harvester:message' => 'Processing', // @translate
                'o-module-oai-pmh-harvester:has_err' => $this->hasErr,
                'o-module-oai-pmh-harvester:stats' => $stats,
            ];
            $this->api->update('oaipmhharvester_harvests', $harvestId, $harvestData);
        } while ($resumptionToken);

        // Update job.
        if (empty($message)) {
            $message = 'Harvest ended.'; // @translate
        }

        $harvestData = [
            'o-module-oai-pmh-harvester:message' => $message,
            'o-module-oai-pmh-harvester:has_err' => $this->hasErr,
            'o-module-oai-pmh-harvester:stats' => $stats,
        ];

        $this->api->update('oaipmhharvester_harvests', $harvestId, $harvestData);

        $this->logger->notice(new Message(
            'Results: total records = %1$s, harvested = %2$d, whitelisted = %3$d, blacklisted = %4$d, imported = %5$d.', // @translate
            $stats['records'], $stats['harvested'], $stats['whitelisted'], $stats['blacklisted'], $stats['imported']
        ));
    }

    protected function createItems(array $toCreate): void
    {
        // TODO The length should be related to the size of the repository output?
        foreach (array_chunk($toCreate, self::BATCH_CREATE_SIZE, true) as $chunk) {
            $response = $this->api->batchCreate('items', $chunk, [], ['continueOnError' => true]);
            $this->createRollback($response->getContent());
        }
    }

    protected function createRollback($resources)
    {
        if (empty($resources)) {
            return null;
        }

        $importEntities = [];
        foreach ($resources as $resource) {
            $importEntities[] = $this->buildImportEntity($resource, '');
        }
        $this->api->batchCreate('oaipmhharvester_entities', $importEntities, [], ['continueOnError' => true]);
    }

    protected function buildImportEntity(AbstractRepresentation $resource, string $identifier): array
    {
        return [
            'o:job' => ['o:id' => $this->job->getId()],
            'o-module-oai-pmh-harvester:entity_id' => $resource->id(),
            'o-module-oai-pmh-harvester:entity_name' => $this->getArg('entity_name', 'items'),
            'o-module-oai-pmh-harvester:identifier' => $identifier,
        ];
    }
}
