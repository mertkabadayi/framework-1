<?php

use Pagekit\Cookie\CookieJar;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

return [

    'name' => 'framework/cookie',

    'main' => function ($app, $config) {

        $app['cookie'] = function ($app) use ($config) {

            extract($config['config'], EXTR_SKIP);

            $app['cookie.init'] = true;

            if (!$path) {
                $path = $app['request']->getBasePath() ?: '/';
            }

            return new CookieJar($app['request'], $path, $domain);
        };

        $app->on('kernel.response', function (FilterResponseEvent $event) use ($app) {
            if (isset($app['cookie.init'])) {
                foreach ($app['cookie']->getQueuedCookies() as $cookie) {
                    $event->getResponse()->headers->setCookie($cookie);
                }
            }
        });

    },

    'config' => [

        'path'   => null,
        'domain' => null,

    ]

];
