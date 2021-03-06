<?php

namespace Apostle;

use Guzzle\Service\Client;

class Queue extends Client
{

	public $emails = array();
	public $results;

	public function __construct()
	{
		parent::__construct(\Apostle::instance()->deliveryHost, array(
			'request.options' => array(
				'exceptions' => false
			)
		));
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
		$recipients = array();
		$failures = array();
		$this->results = array("valid" => array(), "invalid" => array());

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

		$body = array("recipients" => $recipients);

		$response = $this->post('/',
			array(
				'ContentType' => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . \Apostle::instance()->domainKey,
				'Apostle-Client' => 'PHP/' . \Apostle::VERSION
			),
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
