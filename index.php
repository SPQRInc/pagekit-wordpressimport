<?php

use Pagekit\Application;

return [
    'name' => 'spqr/wordpressimport',
    'type' => 'extension',
    'main' => function (Application $app) {
    },
    
    'autoload' => [
        'Spqr\\Wordpressimport\\' => 'src',
    ],
    
    'routes' => [
        '/wordpressimport' => [
            'name'       => '@wordpressimport',
            'controller' => [
                'Spqr\\Wordpressimport\\Controller\\WordpressimportController',
            ],
        ],
    ],
    
    'widgets' => [],
    
    'menu'        => [
        'wordpressimport'           => [
            'label'  => 'WordPress Import',
            'url'    => '@wordpressimport/settings',
            'active' => '@wordpressimport/settings*',
            'icon'   => 'spqr/wordpressimport:icon.svg',
        ],
        'wordpressimport: settings' => [
            'parent' => 'wordpressimport',
            'label'  => 'Settings',
            'url'    => '@wordpressimport/settings',
            'access' => 'wordpressimport: manage settings',
        ],
    ],
    'permissions' => [
        'wordpressimport: manage settings' => [
            'title' => 'Manage settings',
        ],
    ],
    
    'settings' => '@wordpressimport/settings',
    
    'resources' => [
        'spqr/wordpressimport:' => '',
    ],
    
    'config' => [
        'extensions' => ['xml', 'json'],
    ],
    
    'events' => [
        'boot'         => function ($event, $app) {
        },
        'site'         => function ($event, $app) {
        },
        'view.scripts' => function ($event, $scripts) use ($app) {
        },
    ],
];