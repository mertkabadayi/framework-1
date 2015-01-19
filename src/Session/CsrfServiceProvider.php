<?php

namespace Pagekit\Session;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Session\Csrf\Event\CsrfListener;
use Pagekit\Session\Csrf\Provider\SessionCsrfProvider;

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
        $app->subscribe(new CsrfListener($app['csrf']));
    }
}
