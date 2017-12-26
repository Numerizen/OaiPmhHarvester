<?php
namespace OaiPmhHarvester\Service\Controller;

use OaiPmhHarvester\Controller\IndexController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $config = $serviceLocator->get('Config');
        $indexController = new IndexController($config);
        return $indexController;
    }
}
