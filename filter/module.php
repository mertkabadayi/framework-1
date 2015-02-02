<?php

use Pagekit\Filter\FilterManager;

return [

    'name' => 'framework/filter',

    'main' => function ($app, $config) {

        $app['filter'] = function($app) use ($config) {
            return new FilterManager($config['defaults']);
        };

    },

    'defaults' => null

];
