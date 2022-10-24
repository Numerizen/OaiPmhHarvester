<?php declare(strict_types=1);

namespace OaiPmhHarvester\Service\OaiPmh;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OaiPmhHarvester\OaiPmh\HarvesterMapManager;
use Omeka\Service\Exception\ConfigException;

class HarvesterMapManagerFactory implements FactoryInterface
{
    /**
     * Create the oai metadata format manager service.
     *
     * @return HarvesterMapManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        if (empty($config['oaipmh_harvester_maps'])) {
            throw new ConfigException('Missing harvest configuration'); // @translate
        }
        return new HarvesterMapManager($container, $config['oaipmh_harvester_maps']);
    }
}
