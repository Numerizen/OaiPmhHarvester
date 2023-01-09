<?php declare(strict_types=1);

namespace OaiPmhHarvester\Service\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OaiPmhHarvester\Form\HarvestForm;

class HarvestFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $harvestForm = new HarvestForm(null, $options ?? []);
        return $harvestForm
            ->setOaiPmhRepository($services->get('ControllerPluginManager')->get('oaiPmhRepository'));
    }
}
