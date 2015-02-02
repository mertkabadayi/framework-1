<?php

use Pagekit\Auth\Auth;
use Pagekit\Auth\Encoder\NativePasswordEncoder;
use Pagekit\Auth\Event\LoginEvent;
use Pagekit\Auth\Event\LogoutEvent;
use RandomLib\Factory;
use Symfony\Component\HttpFoundation\RedirectResponse;

return [

    'name' => 'framework/auth',

    'main' => function ($app, $config) {

        $app['auth'] = function ($app) {
            return new Auth($app['events'], $app['session']);
        };

        $app['auth.password'] = function () {
            return new NativePasswordEncoder;
        };

        $app['auth.random'] = function () {
            return (new Factory())->getMediumStrengthGenerator();
        };

        $app->on('auth.login', function (LoginEvent $event) use ($app) {
            $event->setResponse(new RedirectResponse($app['request']->get(Auth::REDIRECT_PARAM)));
        }, -32);

        $app->on('auth.logout', function (LogoutEvent $event) use ($app) {
            $event->setResponse(new RedirectResponse($app['request']->get(Auth::REDIRECT_PARAM)));
        }, -32);

    }

];
