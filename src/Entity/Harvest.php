<?php declare(strict_types=1);
namespace OaiPmhHarvester\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\ItemSet;
use Omeka\Entity\Job;

/**
 * @Entity
 * @Table(
 *     name="oaipmhharvester_harvest"
 * )
 */
class Harvest extends AbstractEntity
{
    /**
     * @var int
     * @Id
     * @Column(
     *     type="integer"
     * )
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var Job
     * @OneToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=false
     * )
     */
    protected $job;

    /**
     * @var Job|null
     * @OneToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=true
     * )
     */
    protected $undoJob;

    /**
     * @var string
     * @Column(
     *     name="`comment`",
     *     type="text",
     *     nullable=true
     * )
     */
    protected $comment;

    /**
     * @var string
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $endpoint;

    /**
     * @var string
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $resourceType;

    /**
     * @var ItemSet
     * @ManyToOne(
     *     targetEntity=\Omeka\Entity\ItemSet::class,
     *     inversedBy="itemSet"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $itemSet;

    /**
     * @var string
     * @Column(
     *      type="string",
     *      length=190
     * )
     */
    protected $metadataPrefix;

    /**
     * @var string
     * @Column(
     *     type="string",
     *     length=190,
     *     nullable=true
     * )
     */
    protected $setSpec;

    /**
     * @var string
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $setName;

    /**
     * @var string
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $setDescription;

    /**
     * @var bool
     * @Column(
     *     type="boolean",
     *     nullable=false
     * )
     */
    protected $hasErr = false;

    /**
     * @var array
     * @Column(
     *     type="json"
     * )
     */
    protected $stats;

    /**
     * @var string
     * @Column(
     *     type="string",
     *     length=190,
     *     nullable=true
     * )
     */
    protected $resumptionToken;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Job $job
     * @return self
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return \Omeka\Entity\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param Job $undoJob
     * @return self
     */
    public function setUndoJob(Job $undoJob = null)
    {
        $this->undoJob = $undoJob;
        return $this;
    }

    /**
     * @return \Omeka\Entity\Job|null
     */
    public function getUndoJob()
    {
        return $this->undoJob;
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $resourceType
     * @return self
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param ItemSet $itemSet
     * @return self
     */
    public function setItemSet(ItemSet $itemSet = null)
    {
        $this->itemSet = $itemSet;
        return $this;
    }

    /**
     * @return ItemSet|null
     */
    public function getItemSet()
    {
        return $this->itemSet;
    }

    /**
     * @param string $metadataPrefix
     * @return self
     */
    public function setMetadataPrefix($metadataPrefix)
    {
        $this->metadataPrefix = $metadataPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetadataPrefix()
    {
        return $this->metadataPrefix;
    }

    /**
     * @param string $setSpec
     * @return self
     */
    public function setSetSpec($setSpec)
    {
        $this->setSpec = $setSpec;
        return $this;
    }

    /**
     * @return string
     */
    public function getSetSpec()
    {
        return $this->setSpec;
    }

    /**
     * @param string $setName
     * @return self
     */
    public function setSetName($setName)
    {
        $this->setName = $setName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSetName()
    {
        return $this->setName;
    }

    /**
     * @param string $setDescription
     * @return self
     */
    public function setSetDescription($setDescription)
    {
        $this->setDescription = $setDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getSetDescription()
    {
        return $this->setDescription;
    }

    /**
     * @param bool $hasErr
     * @return self
     */
    public function setHasErr($hasErr)
    {
        $this->hasErr = (bool) $hasErr;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasErr()
    {
        return $this->hasErr;
    }

    /**
     * @param array $stats
     * @return self
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param string $resumptionToken
     * @return self
     */
    public function setResumptionToken($resumptionToken)
    {
        $this->resumptionToken = $resumptionToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getResumptionToken()
    {
        return $this->resumptionToken;
    }
}
