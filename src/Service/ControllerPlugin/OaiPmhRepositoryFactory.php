<?php declare(strict_types=1);

namespace OaiPmhHarvester\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OaiPmhHarvester\Mvc\Controller\Plugin\OaiPmhRepository;

class OaiPmhRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new OaiPmhRepository(
            $services->get(\OaiPmhHarvester\OaiPmh\HarvesterMapManager::class),
            $services->get('MvcTranslator')
        );
    }
}
