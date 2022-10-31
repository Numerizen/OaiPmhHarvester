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
            'o-module-oai-pmh-harvester:identifier' => $this->identifier(),
            'o:created' =>[
                '@value' => $this->getDateTime($this->created()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
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

    public function identifier(): string
    {
        return $this->resource->getIdentifier();
    }

    public function created()
    {
        return $this->resource->getCreated();
    }
}
