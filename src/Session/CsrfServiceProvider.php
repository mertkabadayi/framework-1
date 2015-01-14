<?php

namespace Pagekit\Session;

use Pagekit\Session\Csrf\Event\CsrfListener;
use Pagekit\Session\Csrf\Provider\SessionCsrfProvider;
use Pagekit\Application;
use Pagekit\Framework\ServiceProviderInterface;

class CsrfServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['csrf'] = function($app) {
            return new SessionCsrfProvider($app['session']);
        };
    }

    public function boot(Application $app)
    {
        $app['events']->addSubscriber(new CsrfListener($app['csrf']));
    }
}
