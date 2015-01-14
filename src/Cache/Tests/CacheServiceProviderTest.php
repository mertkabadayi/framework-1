<?php

namespace Pagekit\Cache\Tests;

use Pagekit\Cache\CacheServiceProvider;
use Pagekit\Tests\ServiceProviderTestCase;

class CacheServiceProviderTest extends ServiceProviderTestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	/**
	* @dataProvider configProvider
	*/
	public function testRegister($storage)
	{
        $identifier = 'cache_test';
		$provider = new CacheServiceProvider();
		$config = [
            'cache.'.$identifier => [
                'storage' => $storage,
                'path' => './',
                'prefix' => 'prefix_',
            ],
        ];

		$this->app['config'] = $this->getConfig($config);

		if ($storage == 'apc') {
			if (!extension_loaded('apc') || false === @apc_cache_info()) {
				$this->markTestSkipped('The ' . __CLASS__ .' requires the use of APC');
			} else {
				$provider->register($this->app);
				$this->assertInstanceOf('Pagekit\Cache\Cache', $this->app[$identifier]);
			}
		}

		if ($storage == 'array') {
			$provider->register($this->app);
			$this->assertInstanceOf('Pagekit\Cache\Cache', $this->app[$identifier]);
		}

		if ($storage == 'file' || $storage == 'auto') {
			$provider->register($this->app);
			$this->assertInstanceOf('Pagekit\Cache\Cache', $this->app[$identifier]);
		}
	}

    public function configProvider()
    {
        return [
            ['array'],
            ['apc'],
            ['file'],
            ['auto'],
        ];
    }
}
