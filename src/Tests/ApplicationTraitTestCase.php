<?php

namespace Pagekit\Tests;

use Pagekit\Framework\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagekit\Framework\Controller\Controller;

class ApplicationTraitTestCase extends \PHPUnit_Framework_TestCase {

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
        $this->app = new Application();
        $this->controller = new Controller();
    }

    public function testGetApplication()
    {
        Controller::setApplication($this->app);
        $this->assertEquals($this->app, $this->controller->getApplication());
    }

    public function testArrayAccess()
    {
        $key   = "unique_foo_42";
        $value = "some_string_23";

        // offsetExists
        $this->assertFalse(isset($this->controller[$key]));

        // offsetSet
        $this->controller[$key] = $value;

        // offsetExists
        $this->assertTrue(isset($this->controller[$key]));

        // offsetGet
        $this->assertEquals($value, $this->controller[$key]);

        //offsetUnset
        unset($this->controller[$key]);

        // offsetExists
        $this->assertFalse(isset($this->controller[$key]));
    }
}