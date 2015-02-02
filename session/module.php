<?php

use Pagekit\Session\Handler\DatabaseSessionHandler;
use Pagekit\Session\Message;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Pagekit\Session\Csrf\Event\CsrfListener;
use Pagekit\Session\Csrf\Provider\SessionCsrfProvider;

return [

    'name' => 'framework/session',

    'main' => function ($app, $config) {

        $app['session'] = function($app) {
            $session = new Session($app['session.storage']);
            $session->registerBag($app['message']);
            return $session;
        };

        $app['message'] = function() {
            return new Message;
        };

        $app['session.storage'] = function($app) {

            switch ($app['config']['session.storage']) {

                case 'database':

                    $handler = new DatabaseSessionHandler($app['db'], $app['config']['session.table']);
                    $storage = new NativeSessionStorage($app['session.options'], $handler);

                    break;

                case 'array':

                    $storage = new MockArraySessionStorage;
                    $app['session.test'] = true;

                    break;

                default:

                    $handler = new NativeFileSessionHandler($app['config']['session.files']);
                    $storage = new NativeSessionStorage($app['session.options'], $handler);

                    break;
            }

            return $storage;
        };

        $app['session.options'] = function($app) {

            $options = $app['config']['session'];

            if (isset($options['cookie'])) {

                foreach ($options['cookie'] as $name => $value) {
                    $options[$name == 'name' ? 'name' : 'cookie_'.$name] = $value;
                }

                unset($options['cookie']);
            }

            if (isset($options['lifetime']) && !isset($options['gc_maxlifetime'])) {
                $options['gc_maxlifetime'] = $options['lifetime'];
            }

            return $options;
        };

        $app['session.test'] = false;

        $app['csrf'] = function($app) {
            return new SessionCsrfProvider($app['session']);
        };

        $app->on('kernel.boot', function() use ($app) {

            if ($app['session.test']) {

                $app['events']->addListener(KernelEvents::REQUEST, function (GetResponseEvent $event)
                {
                    if (!$event->isMasterRequest() || !isset($this->app['session'])) {
                        return;
                    }

                    $session = $this->app['session'];
                    $cookies = $event->getRequest()->cookies;

                    if ($cookies->has($session->getName())) {
                        $session->setId($cookies->get($session->getName()));
                    } else {
                        $session->migrate(false);
                    }
                }, 100);

                $app['events']->addListener(KernelEvents::RESPONSE, function (FilterResponseEvent $event)
                {
                    if (!$event->isMasterRequest()) {
                        return;
                    }

                    $session = $event->getRequest()->getSession();

                    if ($session && $session->isStarted()) {

                        $session->save();

                        $params = session_get_cookie_params();
                        $cookie = new Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);

                        $event->getResponse()->headers->setCookie($cookie);
                    }
                }, -100);
            }

            $app['events']->addListener(KernelEvents::REQUEST, function (GetResponseEvent $event) {
                if (!$this->app['session.test'] && !isset($this->app['session.options']['cookie_path'])) {
                    $this->app['session.storage']->setOptions(['cookie_path' => $event->getRequest()->getBasePath() ?: '/']);
                }

                $event->getRequest()->setSession($this->app['session']);
            }, 100);

            $app->subscribe(new CsrfListener($app['csrf']));

        });
    }

];
