<?php declare(strict_types=1);

namespace OaiPmhHarvester;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
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

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // Manage the deletion of an item.
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ItemAdapter::class,
            'api.delete.pre',
            [$this, 'handleBeforeDelete'],
        );
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

    public function handleBeforeDelete(Event $event): void
    {
        /**
         * @var \Omeka\Api\Manager $api
         * @var \Omeka\Api\Request $request
         */
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $request = $event->getParam('request');
        $resourceId = $request->getId();
        $resourceName = $request->getResource();
        try {
            $api
                ->delete(
                    'oaipmhharvester_entities',
                    [
                        'entityId' => $resourceId,
                        'entityName' => $resourceName,
                    ],
                    [],
                    [
                        // The flush is automatically done on main resource
                        // execution, or skipped when failing.
                        'flushEntityManager' => false,
                    ]
                )
                ->getContent();
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
        }
    }
}
