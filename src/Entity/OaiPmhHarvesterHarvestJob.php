<?php
namespace OaiPmhHarvester\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Job;

/**
 * @Entity
 */
class OaiPmhHarvesterHarvestJob extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Job")
     * @JoinColumn(nullable=false)
     */
    protected $job;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Job")
     * @JoinColumn(nullable=true)
     */
    protected $undoJob;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $comment;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $has_err = false;

    /**
     * @Column(type="string")
     */
    protected $resource_type;

    /**
     * @Column(type="integer")
     */
    protected $collection_id;

    /**
     * @Column(type="string")
     */
    protected $base_url;

    /**
     * @Column(type="string")
     */
    protected $metadata_prefix;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $set_spec;

    /**
     * @Column(type="string")
     */
    protected $set_name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $set_description;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $initiated;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $completed;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $start_from;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $resumption_token;

    public function getId()
    {
        return $this->id;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setUndoJob(Job $job)
    {
        $this->undoJob = $job;
    }

    public function getUndoJob()
    {
        return $this->undoJob;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setHasErr($hasErr)
    {
        $this->has_err = $hasErr;
    }

    public function setResourceType($resourceType)
    {
        $this->resource_type = $resourceType;
    }

    public function getResourceType()
    {
        return $this->resource_type;
    }

    public function getHasErr()
    {
        return $this->has_err;
    }
    public function getCollectionId()
    {
        return $this->collection_id;
    }

    public function getBaseUrl()
    {
        return $this->base_url;
    }

    public function getMetadataPrefix()
    {
        return $this->metadata_prefix;
    }

    public function getSetSpec()
    {
        return $this->set_spec;
    }

    public function getSetName()
    {
        return $this->set_name;
    }

    public function getSetDescription()
    {
        return $this->set_description;
    }

    public function getInitiated()
    {
        return $this->initiated;
    }

    public function getCompleted()
    {
        return $this->completed;
    }

    public function getStartFrom()
    {
        return $this->start_from;
    }

    public function getResumptionToken()
    {
        return $this->resumption_token;
    }

    public function setCollectionId($collection_id)
    {
        $this->collection_id = $collection_id;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->base_url = $baseUrl;
    }

    public function setMetadataPrefix($metadata_prefix)
    {
        $this->metadata_prefix = $metadata_prefix;
    }

    public function setSetSpec($set_spec)
    {
        $this->set_spec = $set_spec;
    }

    public function setSetName($set_name)
    {
        $this->set_name = $set_name;
    }

    public function setSetDescription($set_description)
    {
        $this->set_description = $set_description;
    }

    public function setInitiated($initiated)
    {
        $this->initiated = $initiated;
    }

    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    public function setStartFrom($start_from)
    {
        $this->start_from = $start_from;
    }

    public function setResumptionToken($resumption_token)
    {
        $this->resumption_token = $resumption_token;
    }
}
