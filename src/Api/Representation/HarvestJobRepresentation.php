<?php
namespace OaiPmhHarvester\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class HarvestJobRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        $undo_job = null;
        if ($this->undoJob()) {
            $undo_job = $this->undoJob()->getReference();
        }

        return [
            'comment' => $this->comment(),
            'base_url' => $this->getbaseUrl(),
            'collection_id' => $this->getCollectionId(),
            'metadata_prefix' => $this->getMetadataPrefix(),
            'set_spec' => $this->getSetSpec(),
            'set_name' => $this->getSetName(),
            'set_description' => $this->getSetDescription(),
            'initiated' => $this->getInitiated(),
            'completed' => $this->getCompleted(),
            'start_from' => $this->getStartFrom(),
            'start_from' => $this->getResumptionToken(),
            'resumption_token' => $this->resourceType(),
            'o:job' => $this->job()->getReference(),
            'o:undo_job' => $undo_job,
        ];
    }

    public function getJsonLdType()
    {
        return 'o:OaipmhharvesterHarvestJob';
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function undoJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getUndoJob());
    }

    public function comment()
    {
        return $this->resource->getComment();
    }

    public function hasErr()
    {
        return $this->resource->getHasErr();
    }

    public function getHasErr()
    {
        return $this->has_err;
    }

    public function getCollectionId()
    {
        return $this->resource->collection_id;
    }

    public function getBaseUrl()
    {
        return $this->resource->base_url;
    }

    public function getMetadataPrefix()
    {
        return $this->resource->metadata_prefix;
    }

    public function getSetSpec()
    {
        return $this->resource->set_spec;
    }

    public function getSetName()
    {
        return $this->resource->set_name;
    }

    public function getSetDescription()
    {
        return $this->resource->set_description;
    }

    public function getInitiated()
    {
        return $this->resource->initiated;
    }

    public function getCompleted()
    {
        return $this->resource->completed;
    }

    public function getStartFrom()
    {
        return $this->resource->start_from;
    }

    public function getResumptionToken()
    {
        return $this->resource->resumption_token;
    }
}
