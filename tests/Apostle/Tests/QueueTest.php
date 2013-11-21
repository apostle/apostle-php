<?php

namespace Apostle\Tests;

use Apostle\Queue;
use Apostle\Mail;
use Apostle\TestCase;

class QueueTest extends TestCase
{
	public function testAddingEmail()
	{
		$queue = new Queue();

		$this->assertEquals(array(), $queue->emails);
		$queue->add("abc");
		$this->assertEquals(array("abc"), $queue->emails);
	}

	public function testSize()
	{
		$queue = new Queue();

		$this->assertEquals(0, $queue->size());
		$queue->add("abc")->add("123");
		$this->assertEquals(2, $queue->size());
	}

	public function testDeliverValidates()
	{
		$queue = new Queue();

		$invalidTemplateMail = new Mail(null, array("email" => "user@example.org"));
		$invalidEmailMail = new Mail("slug");

		$queue->add($invalidTemplateMail);
		$queue->add($invalidEmailMail);

		$queue->deliver();
		$this->assertEquals(array(
			"valid" => array(),
			"invalid" => array($invalidTemplateMail, $invalidEmailMail)
		), $queue->results);
	}

	public function testDeliverSetFailures()
	{
		\Apostle::instance()->deliver = true;

		$queue = $this->getServiceBuilder()->get('queue');
		$this->setMockResponse($queue, "delivery_401.http");

		$this->assertEquals(false, $queue->deliver($failures));
		$this->assertEquals("Invalid domain key: Bearer abc", $failures);
	}

	public function testDeliverSendsAuthorizationHeader()
	{
		\Apostle::setup("authtoken123");

		$queue = $this->getServiceBuilder()->get('queue');
		$this->setMockResponse($queue, "delivery_200.http");

		$this->assertEquals(true, $queue->deliver());
		$this->assertRequest("POST", "/");
		$this->assertBearerToken("authtoken123");
	}

	public function testDeliverSendsJsonRecipients()
	{
		\Apostle::instance()->deliver = true;

		$queue = $this->getServiceBuilder()->get('queue');
		$mail1 = new Mail("slug-1", array("email" => "user1@example.org"));
		$mail2 = new Mail("slug-2", array("email" => "user2@example.org", "foo" => "bar"));
		$queue->add($mail1)->add($mail2);
		$this->setMockResponse($queue, "delivery_200.http");

		$this->assertEquals(true, $queue->deliver());
		$this->assertRequestJson(array("recipients" => array(
			"user1@example.org" => array("data" => array(), "template_id" => "slug-1"),
			"user2@example.org" => array("data" => array("foo" => "bar"), "template_id" => "slug-2"),
		)));

	}
}
