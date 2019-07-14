<?php
namespace OaiPmhHarvester;

return [
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'oaipmhharvester_entities' => Api\Adapter\EntityAdapter::class,
            'oaipmhharvester_harvestjob' => Api\Adapter\HarvestJobAdapter::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'OaiPmhHarvester\Controller\Index' => Service\Controller\IndexControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'oaipmhharvester' => [
                        'type' => \Zend\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/oaipmhharvester',
                            'defaults' => [
                                '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'sets' => [
                                'type' => \Zend\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/sets',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'sets',
                                         'visible' => false,
                                    ],
                                ],
                            ],
                            'harvest' => [
                                'type' => \Zend\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/harvest',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'harvest',
                                         'visible' => false,
                                    ],
                                ],
                            ],
                            'past-harvests' => [
                                'type' => \Zend\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/past-harvests',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'past-harvests',
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
                'resource' => 'OaiPmhHarvester\Controller\Index',
                'pages' => [
                    [
                        'label' => 'Harvest', // @translate
                        'route' => 'admin/oaipmhharvester',
                        'resource' => 'OaiPmhHarvester\Controller\Index',
                    ],
                    [
                        'label' => 'Sets', // @translate
                        'route' => 'admin/oaipmhharvester/sets',
                        'resource' => 'OaiPmhHarvester\Controller\Index',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Harvest', // @translate
                        'route' => 'admin/oaipmhharvester/harvest',
                        'resource' => 'OaiPmhHarvester\Controller\Index',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Past Harvests', // @translate
                        'route' => 'admin/oaipmhharvester/past-harvests',
                        'controller' => 'Index',
                        'action' => 'past-harvests',
                        'resource' => 'OaiPmhHarvester\Controller\Index',
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
];
