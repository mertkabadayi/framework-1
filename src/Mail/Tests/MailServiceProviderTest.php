<?php

namespace Pagekit\Mail\Tests;

use Pagekit\Mail\MailServiceProvider;
use Pagekit\Tests\ServiceProviderTestCase;

class MailServiceProviderTest extends ServiceProviderTestCase
{
    /**
     * @var MailServiceProvider
     */
    protected $provider;

	public function setUp()
	{
		parent::setUp();
		$this->provider = new MailServiceProvider();
	}

	public function mailDriver()
	{
		return [
			['mail'],
			['smtp']
		];
	}

	/**
	* @dataProvider mailDriver
	*/
	public function testMailServiceProvider($mailDriver)
	{
		$mailConfig = ['mail.driver' => $mailDriver];
		$this->app['config'] = $this->getConfig($mailConfig);

		$this->provider->register($this->app);
		$this->provider->boot($this->app);

		$this->assertInstanceOf('Pagekit\Mail\Mailer', $this->app['mailer']);
		$this->assertTrue($this->app['mailer.initialized']);
		$this->assertInstanceOf('Swift_SpoolTransport', $this->app['swift.spooltransport']);
		$this->assertInstanceOf('Swift_MemorySpool', $this->app['swift.spool']);
		$this->assertInstanceOf('Swift_Transport_StreamBuffer', $this->app['swift.transport.buffer']);
		$this->assertInstanceOf('Swift_Transport_Esmtp_AuthHandler', $this->app['swift.transport.authhandler']);
		$this->assertInstanceOf('Swift_Events_SimpleEventDispatcher', $this->app['swift.transport.eventdispatcher']);
		switch ($mailDriver) {
			case 'smtp':
				$this->assertInstanceOf('Swift_Transport_EsmtpTransport', $this->app['swift.transport']);
				break;
			case 'mail':
				$this->assertInstanceOf('Swift_MailTransport', $this->app['swift.transport']);
				break;
		}
	}
}
