<?php declare(strict_types=1);

namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use Laminas\ServiceManager\ServiceLocatorInterface;
use SimpleXMLElement;

interface HarvesterMapInterface
{
    /**
     * Set the services.
     */
    public function setServiceLocator(ServiceLocatorInterface $services): HarvesterMapInterface;

    /**
     * Pass options for mapping process.
     */
    public function setOptions(array $options): HarvesterMapInterface;

    /**
     * Map a xml record to a list of resources for Omeka Api.
     */
    public function mapRecord(SimpleXMLElement $record): array;
}
