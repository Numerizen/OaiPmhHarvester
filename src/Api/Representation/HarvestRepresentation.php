<?php declare(strict_types=1);
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
            'o-module-oai-pmh-harvester:endpoint' => $this->endpoint(),
            'o-module-oai-pmh-harvester:resource_type' => $this->resourceType(),
            'o:item_set' => $itemSet(),
            'o-module-oai-pmh-harvester:metadata_prefix' => $this->metadataPrefix(),
            'o-module-oai-pmh-harvester:set_spec' => $this->getSetSpec(),
            'o-module-oai-pmh-harvester:set_name' => $this->getSetName(),
            'o-module-oai-pmh-harvester:set_description' => $this->getSetDescription(),
            'o-module-oai-pmh-harvester:has_err' => $this->hasErr(),
            'o-module-oai-pmh-harvester:stats' => $this->stats(),
            'o-module-oai-pmh-harvester:resumption_token' => $this->resumptionToken(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:OaipmhharvesterHarvestJob';
    }

    /**
     * @return \Omeka\Api\Representation\JobRepresentation
     */
    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    /**
     * @return \Omeka\Api\Representation\JobRepresentation|null
     */
    public function undoJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getUndoJob());
    }

    /**
     * @return string
     */
    public function comment()
    {
        return $this->resource->getComment();
    }

    /**
     * @return string
     */
    public function endpoint()
    {
        return $this->resource->getEndpoint();
    }

    /**
     * @return string
     */
    public function resourceType()
    {
        return $this->resource->getResourceType();
    }

    /**
     * @return \Omeka\Api\Representation\ItemSetRepresentation|null
     */
    public function itemSet()
    {
        return $this->getAdapter('item_sets')
            ->getRepresentation($this->resource->getItemSet());
    }

    /**
     * @return string
     */
    public function metadataPrefix()
    {
        return $this->resource->getMetadataPrefix();
    }

    /**
     * @return string
     */
    public function getSetSpec()
    {
        return $this->resource->getSetSpec();
    }

    /**
     * @return string
     */
    public function getSetName()
    {
        return $this->resource->getSetName();
    }

    /**
     * @return string
     */
    public function getSetDescription()
    {
        return $this->resource->getSetDescription();
    }

    /**
     * @return bool
     */
    public function hasErr()
    {
        return $this->resource->getHasErr();
    }

    /**
     * @return array
     */
    public function stats()
    {
        return $this->resource->getStats();
    }

    /**
     * @return string
     */
    public function resumptionToken()
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
