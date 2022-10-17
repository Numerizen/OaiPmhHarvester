<?php declare(strict_types=1);

namespace OaiPmhHarvester\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\JobRepresentation;

class EntityRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'o:job' => $this->job()->getReference(),
            'o-module-oai-pmh-harvester:entity_id' => $this->entityId(),
            'o-module-oai-pmh-harvester:entity_name' => $this->entityName(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:OaiPmhHarvesterHarvesterEntity';
    }

    public function job(): JobRepresentation
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function entityId(): int
    {
        return $this->resource->getEntityId();
    }

    public function entityName(): string
    {
        return $this->resource->getEntityName();
    }
}
