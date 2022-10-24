<?php declare(strict_types=1);

namespace OaiPmhHarvester\OaiPmh;

use OaiPmhHarvester\OaiPmh\HarvesterMap\HarvesterMapInterface;
use Omeka\ServiceManager\AbstractPluginManager;

class HarvesterMapManager extends AbstractPluginManager
{
    /**
     * Keep oai dc first.
     *
     * @var array
     */
    protected $sortedNames = [
        'oai_dc',
    ];

    protected $autoAddInvokableClass = false;

    protected $instanceOf = HarvesterMapInterface::class;
}
