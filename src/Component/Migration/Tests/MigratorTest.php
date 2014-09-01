<?php

namespace Pagekit\Component\Migration\Tests;

use Pagekit\Component\Migration\Migrator;

/**
 * Test class for Migrations.
 * @group now
 */
class MigratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    public function setUp()
    {
        $this->migrator = new Migrator();
        $this->migrator->addGlobal('app', new \StdClass());
    }

	public function testRun() {
        $migration = $this->migrator->create(__DIR__.'/Fixtures');
		$this->assertContains('0000_00_00_000005_test5', $migration->run());
	}

	public function testRunException() {
		$this->setExpectedException('InvalidArgumentException');
        $migration = $this->migrator->create(__DIR__.'/invalidPath');
		$migration->run();
	}

	public function testGet() {
        $migration = $this->migrator->create(__DIR__.'/Fixtures');

        $refObject = new \ReflectionObject($migration);
        $refFiles = $refObject->getProperty('files');
        $refFiles->setAccessible(true);
        $files = $refFiles->getValue($migration);

		$this->assertCount(8, $files);

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000003_test3');

        $refObject = new \ReflectionObject($migration);
        $refFiles = $refObject->getProperty('files');
        $refFiles->setAccessible(true);
        $files = $refFiles->getValue($migration);
		$this->assertCount(2, $files);
	}

	public function testGetException () {
		$this->setExpectedException('InvalidArgumentException');
		$migrator = new Migrator;
		$migrator->create(__DIR__.'/invalidPath');
	}
}