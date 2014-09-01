<?php

namespace Pagekit\Component\Mail\Tests;

use Pagekit\Component\Mail\Mailer;
use Pagekit\Component\Mail\Message;
use Swift_ByteStream_FileByteStream;
use Swift_Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    protected $swift;
    protected $queue;
    protected $mailer;
    protected $message;

    public function setUp()
    {
    	$this->swift = $this->getMockBuilder('Swift_Transport')->disableOriginalConstructor()->getMock();
        $this->queue = $this->getMockBuilder('Swift_SpoolTransport')->disableOriginalConstructor()->getMock();
        $this->mailer = new Mailer($this->swift, $this->queue);
    	$this->message = new Message();
        $this->message->setMailer($this->mailer);
    }

    public function testSubject()
    {
    	$this->assertEquals('', $this->message->getSubject());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setSubject('some subject'));
    	$this->assertEquals('some subject', $this->message->getSubject());
    }

    public function testDate()
    {
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setDate(20131212));
    	$this->assertEquals(20131212, $this->message->getDate());
    }

    public function testBody()
    {
    	$this->assertEquals('', $this->message->getBody());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setBody('some subject'));
    	$this->assertEquals('some subject', $this->message->getBody());
    }

    public function testReturnPath()
    {
    	$this->assertEquals('', $this->message->getReturnPath());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setReturnPath('test@mail.com'));
    	$this->assertEquals('test@mail.com', $this->message->getReturnPath());
    }

    public function testSender()
    {
    	$this->assertEquals('', $this->message->getSender());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setSender('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getSender());
    }

    public function testFrom()
    {
    	$this->assertEquals([], $this->message->getFrom());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setFrom('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getFrom());
    }

    public function testReplyTo()
    {
    	$this->assertEquals('', $this->message->getReplyTo());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setReplyTo('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getReplyTo());
    }

    public function testTo()
    {
    	$this->assertEquals('', $this->message->getTo());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setTo('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getTo());
    }

     public function testCc()
    {
    	$this->assertEquals('', $this->message->getCc());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setCc('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getCc());
    }

    public function testBcc()
    {
    	$this->assertEquals('', $this->message->getBcc());
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->setBcc('test@mail.com', 'test'));
    	$this->assertEquals(['test@mail.com' => 'test'], $this->message->getBcc());
    }

    public function testAttach()
    {
    	$this->assertEquals(0, count($this->message->getChildren()));
    	$this->assertInstanceOf('Pagekit\Component\Mail\Message', $this->message->attachFile('./Fixtures/foo.txt', 'Foo', 'text/plain'));
    	$this->assertEquals(1, count($this->message->getChildren()));

    	$this->message->attachData('some plain text', 'Bar', 'text/plain');
    	$this->assertEquals(2, count($this->message->getChildren()));
    }

    public function testEmbed()
    {
    	$this->assertRegExp('/cid:[0-9a-z]*@swift.generated/', $this->message->embedFile('./Fixtures/image.gif'));
    	$this->assertEquals(1, count($this->message->getChildren()));

    	$this->message->embedData(new Swift_ByteStream_FileByteStream('./Fixtures/image.gif'), 'Image', 'image/gif');
    	$this->assertEquals(2, count($this->message->getChildren()));
    }
}
