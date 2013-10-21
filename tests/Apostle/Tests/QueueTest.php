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

		$this->assertEquals([], $queue->emails);
		$queue->add("abc");
		$this->assertEquals(["abc"], $queue->emails);
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

		$invalidTemplateMail = new Mail(null, ["email" => "user@example.org"]);
		$invalidEmailMail = new Mail("slug");

		$queue->add($invalidTemplateMail);
		$queue->add($invalidEmailMail);

		$queue->deliver();
		$this->assertEquals([
			"valid" => [],
			"invalid" => [$invalidTemplateMail, $invalidEmailMail]
		], $queue->results);
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
		$mail1 = new Mail("slug-1", ["email" => "user1@example.org"]);
		$mail2 = new Mail("slug-2", ["email" => "user2@example.org", "foo" => "bar"]);
		$queue->add($mail1)->add($mail2);
		$this->setMockResponse($queue, "delivery_200.http");

		$this->assertEquals(true, $queue->deliver());
		$this->assertRequestJson(["recipients" => [
			"user1@example.org" => ["data" => [], "template_id" => "slug-1"],
			"user2@example.org" => ["data" => ["foo" => "bar"], "template_id" => "slug-2"],
		]]);

	}
}
