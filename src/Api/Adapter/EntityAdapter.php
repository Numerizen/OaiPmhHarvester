<?php
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
        return \OaiPmhHarvester\Entity\OaiPmhHarvesterEntity::class;
    }

    public function getResourceName()
    {
        return 'oaipmhharvester_entities';
    }

    public function getRepresentationClass()
    {
        return \OaiPmhHarvester\Api\Representation\EntityRepresentation::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? $this->getEntityClass() : 'omeka_root';
        $expr = $qb->expr();

        if (isset($query['job_id'])) {
            $qb->andWhere($expr->eq(
                $alias . '.job',
                $this->createNamedParameter($qb, $query['job_id']))
            );
        }
        if (isset($query['entity_id'])) {
            $qb->andWhere($expr->eq(
                $alias . '.entity_id',
                $this->createNamedParameter($qb, $query['entity_id']))
            );
        }

        if (isset($query['resource_type'])) {
            $qb->andWhere($expr->eq(
                $alias . '.resource_type',
                $this->createNamedParameter($qb, $query['resource_type']))
            );
        }
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['o:job']['o:id'])) {
            $job = isset($data['o:job']['o:id'])
                ? $this->getAdapter('jobs')->findEntity($data['o:job']['o:id'])
                : null;
            $entity->setJob($job);
        }

        if (array_key_exists('entity_id', $data)) {
            $entity->setEntityId($data['entity_id']);
        }

        if (array_key_exists('resource_type', $data)) {
            $entity->setResourceType($data['resource_type']);
        }
    }
}
