<?php

namespace Pagekit\Option;

use Pagekit\Application;
use Pagekit\ServiceProviderInterface;

class OptionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['option'] = function ($app) {
            return new Option($app['db'], $app['cache'], $app['config']['option.table']);
        };
    }

    public function boot(Application $app)
    {
    }
}
