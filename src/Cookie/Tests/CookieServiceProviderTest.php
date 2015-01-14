<?php

namespace Pagekit\Cookie\Tests;

use Pagekit\Config\Config;
use Pagekit\Cookie\CookieServiceProvider;
use Pagekit\Application;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CookieServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	public function testCookieServiceProvider()
	{
		$config = new Config;
		$config->set('cookie.path', 'path/to/cookie');
		$config->set('cookie.domain', 'localhost');

		$app = new Application;
		$app['session'] = new Session(new MockArraySessionStorage);
		$app['request'] = $this->getMock('Symfony\Component\HttpFoundation\Request');
		$app['config'] = $config;
		$app['path.cache'] = null;

		$provider = new CookieServiceProvider;
		$provider->register($app);
		$provider->boot($app);

		$this->assertInstanceOf('Pagekit\Cookie\CookieJar', $app['cookie']);
	}
}
