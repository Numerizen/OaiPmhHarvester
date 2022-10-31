<?php declare(strict_types=1);

namespace OaiPmhHarvester;

return [
    'service_manager' => [
        'factories' => [
            OaiPmh\HarvesterMapManager::class => Service\OaiPmh\HarvesterMapManagerFactory::class,
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'oaipmhharvester_entities' => Api\Adapter\EntityAdapter::class,
            'oaipmhharvester_harvests' => Api\Adapter\HarvestAdapter::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SetsForm::class => Form\SetsForm::class,
        ],
        'factories' => [
            Form\HarvestForm::class => Service\Form\HarvestFormFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'OaiPmhHarvester\Controller\Admin\Index' => Controller\Admin\IndexController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'oaiPmhRepository' => Service\ControllerPlugin\OaiPmhRepositoryFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'oaipmhharvester' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/oai-pmh-harvester',
                            'defaults' => [
                                '__NAMESPACE__' => 'OaiPmhHarvester\Controller\Admin',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/:action',
                                    'constraints' => [
                                        'action' => 'index|sets|harvest|past-harvests',
                                    ],
                                    'defaults' => [
                                        'action' => 'sets',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'OAI-PMH Harvester', // @translate
                'route' => 'admin/oaipmhharvester',
                'resource' => 'OaiPmhHarvester\Controller\Admin\Index',
                'pages' => [
                    [
                        'label' => 'Harvest', // @translate
                        'route' => 'admin/oaipmhharvester/default',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Sets', // @translate
                        'route' => 'admin/oaipmhharvester/default',
                        'action' => 'sets',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Harvest', // @translate
                        'route' => 'admin/oaipmhharvester/default',
                        'action' => 'harvest',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Past Harvests', // @translate
                        'route' => 'admin/oaipmhharvester/default',
                        'action' => 'past-harvests',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'oaipmh_harvester_maps' => [
        'invokables' => [
            // Let oai_dc first, the only required format.
            'oai_dc' => OaiPmh\HarvesterMap\OaiDc::class,
            'oai_dcterms' => OaiPmh\HarvesterMap\OaiDcTerms::class,
            'mets' => OaiPmh\HarvesterMap\Mets::class,
            // 'mock' => OaiPmh\HarvesterMap\Mock::class,
        ],
        'aliases' => [
            'dc' => 'oai_dc',
            'dcterms' => 'oai_dcterms',
            'oai_dcq' => 'oai_dcterms',
            'oai_qdc' => 'oai_dcterms',
            'dcq' => 'oai_dcterms',
            'qdc' => 'oai_dcterms',
        ],
    ],
];
