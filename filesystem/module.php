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

        $app['locator'] = function ($app) {
            return new Locator($app['path']);
        };

        $app->on('kernel.boot', function () use ($app) {
            StreamWrapper::setFilesystem($app['file']);
        });

    }

];
