<?php
namespace OaiPmhHarvester\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class HarvestRepresentation extends AbstractEntityRepresentation
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
            'o-module-oai-pmh-harvester:endpoint' => $this->getEndpoint(),
            'o-module-oai-pmh-harvester:resource_type' => $this->resourceType(),
            'o:item_set' => $itemSet(),
            'o-module-oai-pmh-harvester:metadata_prefix' => $this->getMetadataPrefix(),
            'o-module-oai-pmh-harvester:set_spec' => $this->getSetSpec(),
            'o-module-oai-pmh-harvester:set_name' => $this->getSetName(),
            'o-module-oai-pmh-harvester:set_description' => $this->getSetDescription(),
            'o-module-oai-pmh-harvester:has_err' => $this->hasErr(),
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

    public function getEndpoint()
    {
        return $this->resource->getEndpoint();
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

    public function getMetadataPrefix()
    {
        return $this->resource->getMetadataPrefix();
    }

    public function getSetSpec()
    {
        return $this->resource->getSetSpec();
    }

    public function getSetName()
    {
        return $this->resource->getSetName();
    }

    public function getSetDescription()
    {
        return $this->resource->getSetDescription();
    }

    public function hasErr()
    {
        return $this->resource->getHasErr();
    }

    public function getResumptionToken()
    {
        return $this->resource->getResumptionToken();
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
