<?php

namespace Apostle\Tests;

use Apostle\Mail;
use Apostle\TestCase;

class MailTest extends TestCase
{
	public function testConstructorSetsData()
	{
		$mail = new Mail("slug", [
			"email" => "user@example.org",
			"name" => "Example User",
			"headers" => ["headers"],
			"from" => "from@example.org",
			"layoutId" => "layout-slug",
			"replyTo" => "reply@example.org",
			"other" => "data",
			"more" => "data"
		]);

		$this->assertEquals("user@example.org", $mail->email);
		$this->assertEquals("Example User", $mail->name);
		$this->assertEquals(["headers"], $mail->headers);
		$this->assertEquals("from@example.org", $mail->from);
		$this->assertEquals("layout-slug", $mail->layoutId);
		$this->assertEquals("reply@example.org", $mail->replyTo);
		$this->assertEquals(["other" => "data", "more" => "data"], $mail->data);
	}

	public function testSetter()
	{
		$mail = new Mail("slug");

		$mail->potato = "hardy";

		$this->assertEquals(["potato" => "hardy"], $mail->data);
	}

	public function testToArray()
	{
		$mail = new Mail("slug", [
			"email" => "user@example.org",
			"from" => "from@example.org",
			"random" => "data"
		]);

		$this->assertEquals([
			"template_id" => "slug",
			"from" => "from@example.org",
			"data" => ["random" => "data"],
		], $mail->toArray());
	}

	public function testDeliverSetsFailure()
	{
		$mail = new Mail("slug");

		$this->assertFalse($mail->deliver($failure));
		$this->assertEquals("No email provided", $failure);
	}

	public function testDeliverSuccess()
	{
		$mail = new Mail("slug", ["email" => "user@example.org"]);

		$this->assertTrue($mail->deliver($failure));
		$this->assertNull($failure);
	}
}
