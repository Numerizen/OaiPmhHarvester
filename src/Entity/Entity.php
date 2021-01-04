<?php declare(strict_types=1);
namespace OaiPmhHarvester\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Job;

/**
 * @Entity
 * @Table(
 *     name="oaipmhharvester_entity"
 * )
 */
class Entity extends AbstractEntity
{
    /**
     * @var int;
     * @Id
     * @Column(
     *     type="integer"
     * )
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var Job
     * @ManyToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=false
     * )
     */
    protected $job;

    /**
     * @var int
     * @Column(
     *     type="integer"
     * )
     */
    protected $entityId;

    /**
     * API resource type (not neccesarily a Resource class)
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $resourceType;

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
     * @param int $entityId
     * @return self
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
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
}
