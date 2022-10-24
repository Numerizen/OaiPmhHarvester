<?php declare(strict_types=1);

namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use Laminas\ServiceManager\ServiceLocatorInterface;

interface HarvesterMapInterface
{
    /**
     * Set the services.
     */
    public function setServiceLocator(ServiceLocatorInterface $services): HarvesterMapInterface;
}
