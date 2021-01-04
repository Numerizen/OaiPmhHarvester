<?php declare(strict_types=1);
namespace OaiPmhHarvester\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class EntityRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'o:job' => $this->job()->getReference(),
            'o-module-oai-pmh-harvester:entity_id' => $this->entityId(),
            'o-module-oai-pmh-harvester:resource_type' => $this->resourceType(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:OaiPmhHarvesterHarvesterEntity';
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function entityId()
    {
        return $this->resource->getEntityId();
    }

    public function resourceType()
    {
        return $this->resource->getResourceType();
    }
}
