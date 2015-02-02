<?php

use Pagekit\Filesystem\Filesystem;
use Pagekit\Filesystem\StreamWrapper;

return [

    'name' => 'framework/filesystem',

    'main' => function ($app, $config) {

        $app['file'] = function () {
            return new Filesystem();
        };

        $app->on('kernel.boot', function () use ($app) {
            StreamWrapper::setFilesystem($app['file']);
        });

    }

];
