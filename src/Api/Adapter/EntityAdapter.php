<?php declare(strict_types=1);
namespace OaiPmhHarvester\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class EntityAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return \OaiPmhHarvester\Entity\Entity::class;
    }

    public function getResourceName()
    {
        return 'oaipmhharvester_entities';
    }

    public function getRepresentationClass()
    {
        return \OaiPmhHarvester\Api\Representation\EntityRepresentation::class;
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
        if (isset($query['entity_id'])) {
            $qb->andWhere($expr->eq(
                'omeka_root.entity_id',
                $this->createNamedParameter($qb, $query['entity_id']))
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

        if (array_key_exists('o-module-oai-pmh-harvester:entity_id', $data)) {
            $entity->setEntityId($data['o-module-oai-pmh-harvester:entity_id']);
        }

        if (array_key_exists('o-module-oai-pmh-harvester:resource_type', $data)) {
            $entity->setResourceType($data['o-module-oai-pmh-harvester:resource_type']);
        }
    }
}
