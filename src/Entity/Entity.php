<?php declare(strict_types=1);

namespace OaiPmhHarvester\Entity;

use DateTime;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Job;

/**
 * @Entity
 * @Table(
 *     name="oaipmhharvester_entity",
 *     indexes={
 *         @Index(name="identifier_idx", columns={"identifier"}, options={"lengths": {767}})
 *     }
 * )
 */
class Entity extends AbstractEntity
{
    /**
     * @var int
     *
     * @Id
     * @Column(
     *     type="integer"
     * )
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var Job
     *
     * @ManyToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $job;

    /**
     * API resource id (not necessarily an Omeka main Resource).
     *
     * @var int
     *
     * @Column(
     *     type="integer"
     * )
     */
    protected $entityId;

    /**
     * API resource name (not necessarily an Omeka main Resource).
     *
     * @var string
     *
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $entityName;

    /**
     * @var string
     *
     * @Column(
     *     type="text",
     *     nullable=false
     * )
     */
    protected $identifier;

    /**
     * @var DateTime
     *
     * @Column(
     *     type="datetime"
     * )
     */
    protected $created;

    public function getId()
    {
        return $this->id;
    }

    public function setJob(Job $job): self
    {
        $this->job = $job;
        return $this;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;
        return $this;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;
        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }
}
