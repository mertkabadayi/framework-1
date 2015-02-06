<?php

use Pagekit\Filter\FilterManager;

return [

    'name' => 'framework/filter',

    'main' => function ($app) {

        $app['filter'] = function() {
            return new FilterManager($this->config['defaults']);
        };

    },

    'config' => [

        'defaults' => null

    ]
];
