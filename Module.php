<?php declare(strict_types=1);

namespace OaiPmhHarvester;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $services): void
    {
        $this->setServiceLocator($services);
        $this->execSqlFromFile(__DIR__ . '/data/install/schema.sql');
    }

    public function uninstall(ServiceLocatorInterface $services): void
    {
        $this->setServiceLocator($services);
        $this->execSqlFromFile(__DIR__ . '/data/install/uninstall.sql');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services): void
    {
        $serviceLocator = $services;
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
        $sqls = array_filter(array_map('trim', explode(";\n", $sql)));
        foreach ($sqls as $sql) {
            $result = $connection->executeStatement($sql);
        }
        return $result;
    }
}
