<?php

namespace Pagekit\Framework\Provider;

use Pagekit\Application;
use Pagekit\Framework\Routing\Response;
use Pagekit\Framework\Routing\UrlProvider;
use Pagekit\Routing\RoutingServiceProvider as BaseRoutingServiceProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RoutingServiceProvider extends BaseRoutingServiceProvider
{
	public function register(Application $app)
	{
		parent::register($app);

		$app['url'] = function($app) {
			return new UrlProvider($app['router'], $app['file']);
		};

		$app['response'] = function($app) {
			return new Response($app['url']);
		};
	}

    public function boot(Application $app)
    {
        parent::boot($app);

        // redirect the request if it has a trailing slash
		if (!$app->runningInConsole()) {

	        $app->on('kernel.request', function(GetResponseEvent $event) {

				$path = $event->getRequest()->getPathInfo();

				if ('/' != $path && '/' == substr($path, -1) && '//' != substr($path, -2)) {
                    $event->setResponse(new RedirectResponse(rtrim($event->getRequest()->getUriForPath($path), '/'), 301));
				}

	        }, 200);

		}
    }
}
