<?php

namespace Pagekit\Component\Routing;

use Pagekit\Component\Filter\FilterManager;
use Pagekit\Component\Routing\Controller\AliasCollection;
use Pagekit\Component\Routing\Controller\ControllerCollection;
use Pagekit\Component\Routing\Controller\ControllerReader;
use Pagekit\Component\Routing\Controller\CallbackCollection;
use Pagekit\Component\Routing\Controller\ControllerResolver;
use Pagekit\Component\Routing\Event\ConfigureRouteListener;
use Pagekit\Component\Routing\Event\JsonResponseListener;
use Pagekit\Component\Routing\Event\StringResponseListener;
use Pagekit\Component\Routing\Request\Event\ParamFetcherListener;
use Pagekit\Component\Routing\Request\ParamFetcher;
use Pagekit\Framework\Application;
use Pagekit\Framework\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;

class RoutingServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['router'] = function($app) {
            return new Router($app['events'], $app['kernel'], ['cache' => $app['path.cache']]);
        };

        $app['kernel'] = function($app) {
            return new HttpKernel($app['events'], $app['resolver'], $app['request_stack']);
        };

        $app['request_stack'] = function () {
            return new RequestStack;
        };

        $app['response'] = function($app) {
            return new Response($app['url']);
        };

        $app['resolver'] = function($app) {
            return new ControllerResolver($app['events']);
        };

        $app['aliases'] = function() {
            return new AliasCollection;
        };

        $app['callbacks'] = function() {
            return new CallbackCollection;
        };

        $app['controllers'] = function($app) {
            return new ControllerCollection(new ControllerReader($app['events']), $app['autoloader']);
        };

        $app['url'] = function($app) {
            return new UrlProvider($app['router'], $app['locator']);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app['events']->addSubscriber(new ConfigureRouteListener);
        $app['events']->addSubscriber(new ParamFetcherListener(new ParamFetcher(new FilterManager)));
        $app['events']->addSubscriber(new RouterListener($app['router'], null, null, $app['request_stack']));
        $app['events']->addSubscriber(new ResponseListener('UTF-8'));
        $app['events']->addSubscriber(new JsonResponseListener);
        $app['events']->addSubscriber(new StringResponseListener);
        $app['events']->addSubscriber($app['aliases']);
        $app['events']->addSubscriber($app['callbacks']);
        $app['events']->addSubscriber($app['controllers']);
    }
}
