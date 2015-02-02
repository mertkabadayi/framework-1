<?php

namespace Pagekit\Cookie\Tests;

use Pagekit\Config\Config;
use Pagekit\Cookie\CookieServiceProvider;
use Pagekit\Tests\ServiceProviderTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CookieServiceProviderTest extends ServiceProviderTestCase
{
    public function testCookieServiceProvider()
    {
        $config = new Config;
        $config->set('cookie.path', 'path/to/cookie');
        $config->set('cookie.domain', 'localhost');

        $this->app['session']    = new Session(new MockArraySessionStorage);
        $this->app['request']    = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->app['config']     = $config;
        $this->app['path.cache'] = null;

        $provider = new CookieServiceProvider;
        $provider->register($this->app);
        $provider->boot($this->app);

        $this->assertInstanceOf('Pagekit\Cookie\CookieJar', $this->app['cookie']);
    }
}
