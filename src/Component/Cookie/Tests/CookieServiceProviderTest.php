<?php

namespace Pagekit\Component\Cookie\Tests;

use Pagekit\Component\Config\Config;
use Pagekit\Component\Cookie\CookieServiceProvider;
use Pagekit\Framework\Application;
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

		$this->assertInstanceOf('Pagekit\Component\Cookie\CookieJar', $app['cookie']);
	}
}
