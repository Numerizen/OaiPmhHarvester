<?php
namespace OaiPmhHarvester;

use Omeka\Module\AbstractModule;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->execSqlFromFile(__DIR__ . '/data/install/schema.sql');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->execSqlFromFile(__DIR__ . '/data/install/uninstall.sql');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        require_once __DIR__ . '/data/scripts/upgrade.php';
    }

    /**
     * Execute a sql from a file.
     *
     * @param string $filepath
     * @return mixed
     */
    protected function execSqlFromFile($filepath)
    {
        $services = $this->getServiceLocator();
        $connection = $services->get('Omeka\Connection');
        $sql = file_get_contents($filepath);
        return $connection->exec($sql);
    }
}
