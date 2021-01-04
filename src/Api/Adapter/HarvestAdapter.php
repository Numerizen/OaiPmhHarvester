<?php declare(strict_types=1);
namespace OaiPmhHarvester\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class HarvestAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return \OaiPmhHarvester\Entity\Harvest::class;
    }

    public function getResourceName()
    {
        return 'oaipmhharvester_harvests';
    }

    public function getRepresentationClass()
    {
        return \OaiPmhHarvester\Api\Representation\HarvestRepresentation::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query): void
    {
        $expr = $qb->expr();

        if (isset($query['job_id'])) {
            $qb->andWhere($expr->eq(
                'omeka_root.job',
                $this->createNamedParameter($qb, $query['job_id']))
            );
        }

        if (isset($query['resource_type'])) {
            $qb->andWhere($expr->eq(
                'omeka_root.resource_type',
                $this->createNamedParameter($qb, $query['resource_type']))
            );
        }
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ): void {
        $data = $request->getContent();

        if (array_key_exists('o:job', $data)) {
            $job = isset($data['o:job']['o:id'])
                ? $this->getAdapter('jobs')->findEntity($data['o:job']['o:id'])
                : null;
            $entity->setJob($job);
        }

        if (array_key_exists('o:undo_job', $data)) {
            $job = isset($data['o:undo_job']['o:id'])
                ? $this->getAdapter('jobs')->findEntity($data['o:undo_job']['o:id'])
                : null;
            $entity->setUndoJob($job);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:comment', $data)) {
            $entity->setComment($data['o-module-oai-pmh-harvester:comment']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:resource_type', $data)) {
            $entity->setResourceType($data['o-module-oai-pmh-harvester:resource_type']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:endpoint', $data)) {
            $entity->setEndpoint($data['o-module-oai-pmh-harvester:endpoint']);
        }

        if (array_key_exists('o:item_set', $data)) {
            $itemSet = isset($data['o:item_set']['o:id'])
                ? $this->getAdapter('item_sets')->findEntity($data['o:item_set']['o:id'])
                : null;
            $entity->setItemSet($itemSet);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:metadata_prefix', $data)) {
            $entity->setMetadataPrefix($data['o-module-oai-pmh-harvester:metadata_prefix']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:set_spec', $data)) {
            $entity->setSetSpec($data['o-module-oai-pmh-harvester:set_spec']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:set_name', $data)) {
            $entity->setSetName($data['o-module-oai-pmh-harvester:set_name']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:set_description', $data)) {
            $entity->setSetDescription($data['o-module-oai-pmh-harvester:set_description']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:has_err', $data)) {
            $entity->setHasErr($data['o-module-oai-pmh-harvester:has_err']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:stats', $data)) {
            $entity->setStats($data['o-module-oai-pmh-harvester:stats']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:resumption_token', $data)) {
            $entity->setResumptionToken($data['o-module-oai-pmh-harvester:resumption_token']);
        }
    }
}
