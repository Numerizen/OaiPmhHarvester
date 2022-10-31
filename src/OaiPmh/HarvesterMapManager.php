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

    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);
        $this->addInitializer(function ($serviceLocator, $instance) {
            $instance->setServiceLocator($serviceLocator);
        }, false);
    }
}
