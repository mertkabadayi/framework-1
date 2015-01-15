<?php

namespace Pagekit\Tests;

use Pagekit\Application;
use Pagekit\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ServiceProviderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

	public function setUp()
	{
		$this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
		$this->app = $this->createApplication();
	}

	public function createApplication()
	{
		$app = Application::getInstance();
        $app['path.cache'] = __DIR__.'/cache-ignore';
		$app['session'] = new Session(new MockArraySessionStorage);
		$app['request'] = $this->request;

		return $app;
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
