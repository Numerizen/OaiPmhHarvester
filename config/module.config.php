<?php

return [
    'controllers' => [
        'factories' => [
            'OaiPmhHarvester\Controller\Index' => 'OaiPmhHarvester\Service\Controller\IndexControllerFactory',
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'oaipmhharvester_entities' => 'OaiPmhHarvester\Api\Adapter\EntityAdapter',
            'oaipmhharvester_harvestjob' => 'OaiPmhHarvester\Api\Adapter\HarvestJobAdapter',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/OaiPmhHarvester/view',
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/OaiPmhHarvester/src/Entity',
        ],
    ],    
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'oaipmhharvester' => [
                        'type' => 'Literal',
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
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/sets',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'sets',
                                         'visible'    => false,                                        
                                    ],
                                ],
                            ],
                            'harvest' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/harvest',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'harvest',
                                         'visible'    => false,                                        
                                    ],
                                ],
                            ],
                            'past-harvests' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/past-harvests',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'OaiPmhHarvester\Controller',
                                        'controller' => 'Index',
                                        'action' => 'past-harvests',
                                    ],
                                ],
                            ],                        ]                    
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Oai-Pmh Harvester',
                'route' => 'admin/oaipmhharvester',
                'resource' => 'OaiPmhHarvester\Controller\Index',
                'pages' => [
                    [
                        'label'      => 'Harvest', // @translate
                        'route'      => 'admin/oaipmhharvester',
                        'resource'   => 'OaiPmhHarvester\Controller\Index',
                    ],
                    [
                        'label'      => 'Sets', // @translate
                        'route'      => 'admin/oaipmhharvester/sets',
                        'resource'   => 'OaiPmhHarvester\Controller\Index',
                        'visible' => false,                        
                    ],                
                    [
                        'label'      => 'Harvest', // @translate
                        'route'      => 'admin/oaipmhharvester/harvest',
                        'resource'   => 'OaiPmhHarvester\Controller\Index',
                        'visible' => false,                        
                    ],                  
                    [
                        'label'      => 'Past Harvests', // @translate
                        'route'      => 'admin/oaipmhharvester/past-harvests',
                        'controller' => 'Index',
                        'action' => 'past-harvests',
                        'resource' => 'OaiPmhHarvester\Controller\Index',
                    ],                    
                ],
            ],
        ],
    ],
];
