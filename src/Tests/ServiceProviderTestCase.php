<?php

namespace Pagekit\Tests;

use Pagekit\Application;
use Pagekit\Config\Config;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ServiceProviderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $app;

	public function setUp()
	{
        $this->app = Application::getInstance();
        $this->app['events'] = new EventDispatcher();
        $this->app['request'] = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->app['session'] = new Session(new MockArraySessionStorage);
        $this->app['path.cache'] = __DIR__.'/cache-ignore';
	}

    public function tearDown()
    {
    	$this->app->reset();
    }

	public function getConfig($settings)
	{
		$config = new Config();

		foreach ($settings as $key => $value) {
			$config->set($key, $value);
		}

		return $config;
	}
}
