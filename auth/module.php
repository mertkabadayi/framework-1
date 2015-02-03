<?php

use Pagekit\Auth\Auth;
use Pagekit\Auth\AuthEvents;
use Pagekit\Auth\Encoder\NativePasswordEncoder;
use Pagekit\Auth\Event\AuthenticateEvent;
use Pagekit\Auth\Event\LoginEvent;
use Pagekit\Auth\Event\LogoutEvent;
use Pagekit\Auth\RememberMe;
use RandomLib\Factory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

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
            return (new Factory)->getMediumStrengthGenerator();
        };

        $app->on('auth.login', function (LoginEvent $event) use ($app) {
            $event->setResponse(new RedirectResponse($app['request']->get(Auth::REDIRECT_PARAM)));
        }, -32);

        $app->on('auth.logout', function (LogoutEvent $event) use ($app) {
            $event->setResponse(new RedirectResponse($app['request']->get(Auth::REDIRECT_PARAM)));
        }, -32);

        $app['auth.remember'] = function($app) {

            $name = $app['config']['cookie.remember_me'] ?: 'remember_'.md5($app['request']->getUriForPath(''));

            return new RememberMe($app['config']['app.key'], $name, $app['cookie']);
        };

        $app->on('kernel.boot', function() use ($app, $config) {

            if (!$config['rememberme']['enabled']) {
                return;
            }

            $app['events']->addListener(KernelEvents::REQUEST, function () use ($app) {
                try {

                    if (null !== $app['auth']->getUser()) {
                        return;
                    }

                    $user = $app['auth.remember']->autoLogin($app['auth']->getUserProvider());

                    $app['auth']->setUser($user);

                    $app['events']->dispatch(AuthEvents::LOGIN, new LoginEvent($user));

                } catch (\Exception $e) {}
            }, 20);

            $app['events']->addListener(AuthEvents::SUCCESS, function (AuthenticateEvent $event) use ($app) {
                $app['auth.remember']->set($app['request'], $event->getUser());
            });

            $app['events']->addListener(AuthEvents::FAILURE, function () use ($app) {
                $app['auth.remember']->remove();
            });

            $app['events']->addListener(AuthEvents::LOGOUT, function () use ($app) {
                $app['auth.remember']->remove();
            });

        });

    },

    'rememberme' => [

        'enabled' => true

    ]
];
