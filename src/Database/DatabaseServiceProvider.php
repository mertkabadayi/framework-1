<?php

namespace Pagekit\Database;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Database\Logging\DebugStack;
use Pagekit\Database\ORM\EntityManager;
use Pagekit\Database\ORM\Loader\AnnotationLoader;
use Pagekit\Database\ORM\MetadataManager;
use Pagekit\Database\ORM\ModelTrait;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $default = [
            'wrapperClass'        => 'Pagekit\Database\ConnectionWrapper',
            'defaultTableOptions' => []
        ];

        $app['dbs'] = function($app) use ($default) {

            $dbs = [];

            foreach ($app['config']['database.connections'] as $name => $params) {

                $params = array_replace($default, $params);

                foreach (['engine', 'charset', 'collate'] as $option) {
                    if (isset($params[$option])) {
                        $params['defaultTableOptions'][$option] = $params[$option];
                    }
                }

                $events = $app['config']['database.default'] === $name ? $app['events'] : null;

                $dbs[$name] = new Connection($params, $events);
            }

            return $dbs;
        };

        $app['db'] = function ($app) {
            return $app['dbs'][$app['config']['database.default']];
        };

        $app['db.em'] = function($app) {
            return new EntityManager($app['db'], $app['db.metas']);
        };

        $app['db.metas'] = function($app) {

            $manager = new MetadataManager($app['db']);
            $manager->setLoader(new AnnotationLoader);
            $manager->setCache($app['cache.phpfile']);

            return $manager;
        };

        $app['db.debug_stack'] = function($app) {
            return new DebugStack($app['profiler.stopwatch']);
        };
    }

    public function boot(Application $app)
    {
        ModelTrait::setConnection($app['db']);
        ModelTrait::setManager($app['db.em']);
    }
}
