<?php
namespace OaiPmhHarvester\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class HarvestJobAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return 'OaiPmhHarvester\Entity\OaiPmhHarvesterHarvestJob';
    }

    public function getResourceName()
    {
        return 'oaipmhharvester_harvestjob';
    }

    public function getRepresentationClass()
    {
        return 'OaiPmhHarvester\Api\Representation\HarvestJobRepresentation';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['o:job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:job']['o:id']);
            $entity->setJob($job);
        }
        if (isset($data['o:undo_job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:undo_job']['o:id']);
            $entity->setUndoJob($job);
        }

        if (isset($data['comment'])) {
            $entity->setComment($data['comment']);
        }

        if (isset($data['has_err'])) {
            $entity->setHasErr($data['has_err']);
        }

        if (isset($data['resource_type'])) {
            $entity->setResourceType($data['resource_type']);
        }

        if (isset($data['base_url'])) {
            $entity->setBaseUrl($data['base_url']);
        }

        if (isset($data['metadata_prefix'])) {
            $entity->setMetadataPrefix($data['metadata_prefix']);
        }

        if (isset($data['collection_id'])) {
            $entity->setCollectionId($data['collection_id']);
        }

        if (isset($data['set_name'])) {
            $entity->setSetName($data['set_name']);
        }

        if (isset($data['set_description'])) {
            $entity->setSetDescription($data['set_description']);
        }

        if (isset($data['initiated'])) {
            $entity->setInitiated($data['initiated']);
        }

        if (isset($data['completed'])) {
            $entity->setCompleted($data['completed']);
        }

        if (isset($data['start_from'])) {
            $entity->setStartFrom($data['start_from']);
        }

        if (isset($data['resumption_token'])) {
            $entity->setResumptionToken($data['resumption_token']);
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['job_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.job',
                $this->createNamedParameter($qb, $query['job_id']))
            );
        }

        if (isset($query['resource_type'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.resource_type',
                $this->createNamedParameter($qb, $query['resource_type']))
            );
        }
    }
}
