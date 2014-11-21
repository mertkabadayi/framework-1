<?php

namespace Pagekit\Component\Feed;

use Pagekit\Framework\Application;
use Pagekit\Framework\ServiceProviderInterface;

class FeedServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['feed'] = function () {
            return new FeedFactory;
        };
    }

    public function boot(Application $app)
    {
    }
}
