<?php

namespace Pagekit\Auth;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Auth\Event\AuthenticateEvent;
use Pagekit\Auth\Event\LoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RememberMeServiceProvider implements ServiceProviderInterface, EventSubscriberInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['auth.remember'] = function($app) {

            $name = $app['config']['cookie.remember_me'] ?: 'remember_'.md5($app['request']->getUriForPath(''));

            return new RememberMe($app['config']['app.key'], $name, $app['cookie']);
        };
    }

    public function boot(Application $app)
    {
        $app->subscribe($this);
    }

    public function onKernelRequest($event, $name, $dispatcher)
    {
        try {

            if (null !== $this->app['auth']->getUser()) {
                return;
            }

            $user = $this->app['auth.remember']->autoLogin($this->app['auth']->getUserProvider());

            $this->app['auth']->setUser($user);

            $dispatcher->dispatch(AuthEvents::LOGIN, new LoginEvent($user));

        } catch (\Exception $e) {}
    }

    public function onAuthSuccess(AuthenticateEvent $event)
    {
        $this->app['auth.remember']->set($this->app['request'], $event->getUser());
    }

    public function onFailureAndLogout()
    {
        $this->app['auth.remember']->remove();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
            AuthEvents::SUCCESS   => 'onAuthSuccess',
            AuthEvents::FAILURE   => 'onFailureAndLogout',
            AuthEvents::LOGOUT    => 'onFailureAndLogout'
        ];
    }
}
