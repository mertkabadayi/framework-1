<?php

namespace Pagekit\Component\Migration\Tests;

use Pagekit\Component\Migration\Migration;
use Pagekit\Component\Migration\Migrator;

/**
 * Test class for Migrations.
 */
class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    public function setUp()
    {
        $this->migrator = new Migrator();
    }

    public function testUp()
    {
        $migration = $this->migrator->create(__DIR__.'/Fixtures');
        $this->assertEquals(['0000_00_00_000000'], $migration->run());
        $this->assertEquals(['0000_00_00_000000', '0000_00_00_000001', '0000_00_00_000002', '0000_00_00_000003'], $migration->run('0000_00_00_000003'));

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000000');
        $this->assertEquals(['0000_00_00_000001'], $migration->run());
        $this->assertEquals(['0000_00_00_000001', '0000_00_00_000002', '0000_00_00_000003'], $migration->run('0000_00_00_000003'));

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000003');
        $this->assertCount(0, $migration->run('0000_00_00_000003'));
    }

    public function testDown()
    {
        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000003');
        $this->assertEquals(['0000_00_00_000003'], $migration->get(null, 'down'));
        $this->assertEquals(['0000_00_00_000003', '0000_00_00_000002'], $migration->get('0000_00_00_000001', 'down'));

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000003');
        $this->assertCount(0, $migration->get('0000_00_00_000003', 'down'));
    }

    public function testVersion()
    {
        $migration = $this->migrator->create(__DIR__.'/Fixtures');
        $this->assertEquals(['0000_00_00_000000', '0000_00_00_000001', '0000_00_00_000002'], $migration->run('0000_00_00_000002'));
        $this->assertEquals(['0000_00_00_000000'], $migration->run());

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000002');
        $this->assertEquals(['0000_00_00_000002', '0000_00_00_000001'], $migration->run('0000_00_00_000000'));
        $this->assertEquals(['0000_00_00_000003', '0000_00_00_000006', '0000_00_00_000007'], $migration->run('0000_00_00_000007'));
    }

    public function testLatest()
    {
        $migration = $this->migrator->create(__DIR__.'/Fixtures');
        $this->assertEquals(['0000_00_00_000000', '0000_00_00_000001', '0000_00_00_000002', '0000_00_00_000003', '0000_00_00_000006', '0000_00_00_000007'], $migration->run());

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000002');
        $this->assertEquals(['0000_00_00_000003', '0000_00_00_000006', '0000_00_00_000007'], $migration->run());

        $migration = $this->migrator->create(__DIR__.'/Fixtures', '0000_00_00_000007');
        $this->assertCount(0, $migration->run());
    }
}
