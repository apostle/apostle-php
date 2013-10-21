<?php

namespace Apostle;

use Guzzle\Service\Client;

class Queue extends Client
{

	public $emails = [];
	public $results;

	public function __construct()
	{
		parent::__construct(\Apostle::instance()->deliveryHost, [
			'request.options' => [
				'exceptions' => false
			]
		]);
	}

	public function add($email)
	{
		$this->emails[] = $email;
		return $this;
	}

	public function size()
	{
		return count($this->emails);
	}

	public function deliver(&$failures=null)
	{
		$recipients = [];
		$failures = [];
		$this->results = ["valid" => [], "invalid" => []];

		foreach($this->emails as $email)
		{
			if(!isset($email->email) || $email->email == '')
			{
				$failures[] = $email;
				$this->results["invalid"][] = $email;
				$email->setDeliveryError("No email provided");
				continue;
			}
			if(!isset($email->template) || $email->template == '')
			{
				$failures[] = $email;
				$this->results["invalid"][] = $email;
				$email->setDeliveryError("No template provided");
				continue;
			}

			$recipients[$email->email] = $email->toArray();

			$this->results["valid"][] = $email;
		}

		if(count($failures) > 0)
		{
			return false;
		}

		if(!\Apostle::instance()->deliver)
		{
			return true;
		}

		$body = ["recipients" => $recipients];

		$response = $this->post('/',
			[
				'ContentType' => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . \Apostle::instance()->domainKey
			],
			json_encode($body)
		)->send();

		if($response->getStatusCode() == 401)
		{
			$data = $response->json();
			if($data && isset($data["message"]))
			{
				$failures = $data["message"];
			}
			else
			{
				$failures = "401 Unauthorized";
			}
		}
		return $response->isSuccessful();
	}
}
