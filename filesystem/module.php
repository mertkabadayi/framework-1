<?php

use Pagekit\Filesystem\Filesystem;
use Pagekit\Filesystem\Locator;
use Pagekit\Filesystem\StreamWrapper;

return [

    'name' => 'framework/filesystem',

    'main' => function ($app, $config) {

        $app['file'] = function () {
            return new Filesystem;
        };

        $app['locator'] = function () use ($config) {
            return new Locator($config['config.path']);
        };

        $app->on('kernel.boot', function () use ($app) {
            StreamWrapper::setFilesystem($app['file']);
        });

    },

    'config.path' => getcwd()

];
