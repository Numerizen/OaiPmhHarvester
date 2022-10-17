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
     * @OneToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $job;

    /**
     * @var Job|null
     *
     * @OneToOne(
     *     targetEntity=\Omeka\Entity\Job::class
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $undoJob;

    /**
     * @var string
     *
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $message;

    /**
     * @var string
     *
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $endpoint;

    /**
     * @var string
     *
     * @Column(
     *     type="string",
     *     length=190
     * )
     */
    protected $entityName;

    /**
     * @var ItemSet
     *
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
     *
     * @Column(
     *      type="string",
     *      length=190
     * )
     */
    protected $metadataPrefix;

    /**
     * @var string
     *
     * @Column(
     *     type="string",
     *     length=190,
     *     nullable=true
     * )
     */
    protected $setSpec;

    /**
     * @var string
     *
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $setName;

    /**
     * @var string
     *
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $setDescription;

    /**
     * @var bool
     *
     * @Column(
     *     type="boolean",
     *     nullable=false
     * )
     */
    protected $hasErr = false;

    /**
     * @var array
     *
     * @Column(
     *     type="json"
     * )
     */
    protected $stats;

    /**
     * @var string
     *
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

    public function setJob(Job $job): self
    {
        $this->job = $job;
        return $this;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setUndoJob(?Job $undoJob): self
    {
        $this->undoJob = $undoJob;
        return $this;
    }

    public function getUndoJob(): ?Job
    {
        return $this->undoJob;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
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

    public function setItemSet(?ItemSet $itemSet): self
    {
        $this->itemSet = $itemSet;
        return $this;
    }

    public function getItemSet(): ?ItemSet
    {
        return $this->itemSet;
    }

    public function setMetadataPrefix($metadataPrefix): self
    {
        $this->metadataPrefix = $metadataPrefix;
        return $this;
    }

    public function getMetadataPrefix(): string
    {
        return $this->metadataPrefix;
    }

    public function setSetSpec(?string $setSpec): self
    {
        $this->setSpec = $setSpec;
        return $this;
    }

    public function getSetSpec(): ?string
    {
        return $this->setSpec;
    }

    public function setSetName(?string $setName): self
    {
        $this->setName = $setName;
        return $this;
    }

    public function getSetName(): ?string
    {
        return $this->setName;
    }

    public function setSetDescription(?string $setDescription): self
    {
        $this->setDescription = $setDescription;
        return $this;
    }

    public function getSetDescription(): ?string
    {
        return $this->setDescription;
    }

    public function setHasErr($hasErr): self
    {
        $this->hasErr = (bool) $hasErr;
        return $this;
    }

    public function getHasErr(): bool
    {
        return $this->hasErr;
    }

    public function setStats(?array $stats): self
    {
        $this->stats = $stats;
        return $this;
    }

    public function getStats(): ?array
    {
        return $this->stats;
    }

    public function setResumptionToken(?string $resumptionToken): self
    {
        $this->resumptionToken = $resumptionToken;
        return $this;
    }

    public function getResumptionToken(): ?string
    {
        return $this->resumptionToken;
    }
}
