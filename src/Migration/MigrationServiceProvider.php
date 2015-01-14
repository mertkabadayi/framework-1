<?php

namespace Pagekit\Migration;

use Pagekit\Application;
use Pagekit\Framework\ServiceProviderInterface;

class MigrationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['migrator'] = function($app) {

            $migrator = new Migrator;
            $migrator->addGlobal('app', $app);

            return $migrator;
        };
    }

    public function boot(Application $app)
    {
    }
}
