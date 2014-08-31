<?php

namespace Pagekit\Component\Migration\Tests;

use Pagekit\Component\Migration\Migrator;

/**
 * Test class for Migrations.
 */
class MigratorTest extends \PHPUnit_Framework_TestCase
{
	public function testRun() {
		$migrator = new Migrator;
        $migration = $migrator->create(__DIR__.'/Fixtures');
		$this->assertContains('0000_00_00_000007', $migration->run());
	}

	public function testRunException() {
		$this->setExpectedException('InvalidArgumentException');
		$migrator = new Migrator;
        $migration = $migrator->create(__DIR__.'/invalidPath');
		$migration->run();
	}

	public function testGet() {
		$migrator = new Migrator;
        $migration = $migrator->create(__DIR__.'/Fixtures');
        $refObject = new \ReflectionObject($migration);
        $refFiles = $refObject->getProperty('files');
        $refFiles->setAccessible(true);
        $files = $refFiles->getValue($migration);

		$this->assertCount(8, $files);

		$migrator = new Migrator;
        $migration = $migrator->create(__DIR__.'/Fixtures', '0000_00_00_000003_test3');
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