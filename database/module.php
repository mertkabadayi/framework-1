<?php

use Doctrine\DBAL\DriverManager;
use Pagekit\Database\Logging\DebugStack;
use Pagekit\Database\ORM\EntityManager;
use Pagekit\Database\ORM\Loader\AnnotationLoader;
use Pagekit\Database\ORM\MetadataManager;

return [

    'name' => 'framework/database',

    'main' => function ($app, $config) {

        $default = [
            'wrapperClass' => 'Pagekit\Database\Connection'
        ];

        $app['dbs'] = function ($app) use ($config, $default) {

            $dbs = [];

            foreach ($config['connections'] as $name => $params) {

                $params = array_replace($default, $params);

                if ($config['default'] === $name) {
                    $params['events'] = $app['events'];
                }

                $dbs[$name] = DriverManager::getConnection($params);
            }

            return $dbs;
        };

        $app['db'] = function ($app) use ($config) {
            return $app['dbs'][$config['default']];
        };

        $app['db.em'] = function ($app) {
            return new EntityManager($app['db'], $app['db.metas']);
        };

        $app['db.metas'] = function ($app) {

            $manager = new MetadataManager($app['db']);
            $manager->setLoader(new AnnotationLoader);
            $manager->setCache($app['cache.phpfile']);

            return $manager;
        };

        $app['db.debug_stack'] = function ($app) {
            return new DebugStack($app['profiler.stopwatch']);
        };

    },

    'default' => 'mysql',

    'priority' => 16,

    'connections' => [

        'mysql' => [

            'driver'   => 'pdo_mysql',
            'dbname'   => '',
            'host'     => 'localhost',
            'user'     => 'root',
            'password' => '',
            'engine'   => 'InnoDB',
            'charset'  => 'utf8',
            'collate'  => 'utf8_unicode_ci',
            'prefix'   => ''

        ]

    ]

];
