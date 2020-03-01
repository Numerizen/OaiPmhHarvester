<?php
namespace OaiPmhHarvester\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class HarvestJobRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        $undoJob = $this->undoJob();
        if ($undoJob) {
            $undoJob = $undoJob->getReference();
        }

        $itemSet = $this->itemSet();
        if ($itemSet) {
            $itemSet = $itemSet->getReference();
        }

        return [
            'o:job' => $this->job()->getReference(),
            'o:undo_job' => $undoJob,
            'o-module-oai-pmh-harvester:comment' => $this->comment(),
            'o-module-oai-pmh-harvester:resource_type' => $this->resourceType(),
            'o-module-oai-pmh-harvester:base_url' => $this->getbaseUrl(),
            'o:item_set' => $itemSet(),
            'o-module-oai-pmh-harvester:metadata_prefix' => $this->getMetadataPrefix(),
            'o-module-oai-pmh-harvester:set_spec' => $this->getSetSpec(),
            'o-module-oai-pmh-harvester:set_name' => $this->getSetName(),
            'o-module-oai-pmh-harvester:set_description' => $this->getSetDescription(),
            'o-module-oai-pmh-harvester:initiated' => $this->getInitiated(),
            'o-module-oai-pmh-harvester:completed' => $this->getCompleted(),
            'o-module-oai-pmh-harvester:has_err' => $this->hasErr(),
            'o-module-oai-pmh-harvester:start_from' => $this->getStartFrom(),
            'o-module-oai-pmh-harvester:resumption_token' => $this->getResumptionToken(),
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

    public function resourceType()
    {
        return $this->resource->getResourceType();
    }

    public function getItemSet()
    {
        return $this->getAdapter('item_sets')
            ->getRepresentation($this->resource->getItemSet());
    }

    public function getBaseUrl()
    {
        return $this->resource->baseUrl();
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

    public function hasErr()
    {
        return $this->resource->getHasErr();
    }

    public function getStartFrom()
    {
        return $this->resource->start_from;
    }

    public function getResumptionToken()
    {
        return $this->resource->resumption_token;
    }

    /**
     * Get the count of the currently imported resources.
     *
     * @return int
     */
    public function totalImported()
    {
        $response = $this->getServiceLocator()->get('Omeka\ApiManager')
            ->search('oaipmhharvester_entities', [
                'job_id' => $this->job()->id(),
                'limit' => 0,
            ]);
        return $response->getTotalResults();
    }
}
