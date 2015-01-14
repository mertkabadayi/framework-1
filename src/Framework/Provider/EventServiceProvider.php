<?php

namespace Pagekit\Framework\Provider;

use Pagekit\Application;
use Pagekit\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['events'] = function() {
            return new EventDispatcher;
        };
    }

    public function boot(Application $app)
    {
    }
}
