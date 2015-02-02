<?php

namespace Pagekit\Database\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Pagekit\Database\DatabaseServiceProvider;
use Pagekit\Tests\ServiceProviderTestCase;

class DatabaseServiceProviderTest extends ServiceProviderTestCase
{
    /**
     * @var DatabaseServiceProvider
     */
    protected $provider;

    public function setUp()
	{
		parent::setUp();
		$this->provider = new DatabaseServiceProvider;
	}

	public function testDatabaseServiceProvider()
	{

		$conf = [
        'database' => [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver'   => 'pdo_mysql',
                    'dbname'   => '',
                    'host'     => 'localhost',
                    'user'     => 'root',
                    'password' => '',
                    'engine'   => 'InnoDB',
                    'charset'  => 'utf8',
                    'collate'  => 'utf8_unicode_ci',
                    'prefix'   => ''
                ]
            ]
        ]];

		$this->app['config'] = $this->getConfig($conf);
        $this->app['cache.phpfile'] = new ArrayCache;
		$this->provider->register($this->app);

        $this->assertInstanceOf('Pagekit\Database\Connection', $this->app['db']);
        $this->assertInstanceOf('Pagekit\Database\ORM\EntityManager', $this->app['db.em']);
		$this->assertInstanceOf('Pagekit\Database\ORM\MetadataManager', $this->app['db.metas']);
	}
}
