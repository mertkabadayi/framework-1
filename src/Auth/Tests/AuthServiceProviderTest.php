<?php

namespace Pagekit\Auth\Tests;

use Pagekit\Auth\Auth;
use Pagekit\Auth\AuthServiceProvider;
use Pagekit\Tests\ServiceProviderTestCase;

class AuthServiceProviderTest extends ServiceProviderTestCase
{
    protected $user;
    protected $provider;

	public function setUp()
	{
		parent::setUp();
		$this->user = $this->getMockBuilder('Pagekit\Auth\UserInterface')->disableOriginalConstructor()->getMock();
		$this->provider = new AuthServiceProvider;
	}

	public function testRegister()
	{
		$this->provider->register($this->app);

		$this->assertInstanceOf('Pagekit\Auth\Auth', $this->app['auth']);
		$this->assertInstanceOf('Pagekit\Auth\Encoder\NativePasswordEncoder', $this->app['auth.password']);
	}

	public function testLogin()
	{
		$this->app['request']->expects($this->once())
					  ->method('get')
					  ->with(Auth::REDIRECT_PARAM)
					  ->will($this->returnValue('/'));

		$this->provider->register($this->app);
		$this->provider->boot($this->app);

		$result = $this->app['auth']->login($this->user);

		$this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
	}

	public function testLogout()
	{
		$this->app['request']->expects($this->once())
					  ->method('get')
					  ->with(Auth::REDIRECT_PARAM)
					  ->will($this->returnValue('/'));

		$this->provider->register($this->app);
		$this->provider->boot($this->app);

		$result = $this->app['auth']->logout($this->user);

		$this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
	}

	public function testGetSubscribedEvents()
	{
		$expected = [
					'auth.login'  => ['onLogin', -32],
					'auth.logout' => ['onLogout', -32],
					];
		$this->assertEquals($expected, $this->provider->getSubscribedEvents());
	}
}
