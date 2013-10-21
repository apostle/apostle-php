<?php
/**
 * Apostle\Mail represents an intent to send a payload of
 * data to a single template for a single recipient.
 */

namespace Apostle;

class Mail
{
	public $data;
	public $email;
	public $from;
	public $headers;
	public $layoutId;
	public $name;
	public $replyTo;
	public $template;

	protected $_error;

	protected static $_attributes = [
		"email" => "email",
		"from" => "from",
		"headers" => "headers",
		"layoutId" => "layout_id",
		"name" => "name",
		"replyTo" => "reply_to"
	];

	public function __construct($template, $data = [])
	{
		$this->template = $template;
		$this->headers = [];

		// Remove special values from the data array
		foreach(self::$_attributes as $local => $remote)
		{
			if (!isset($data[$local]))
			{
				continue;
			}
			$this->$local = $data[$local];
		   	unset($data[$local]);
		}

		$this->data = $data;
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function toArray()
	{
		$array = [];
		foreach(self::$_attributes as $local => $remote)
		{
			if(!isset($this->$local) || $local == 'email' || $this->$local == [])
			{
				continue;
			}
			$array[$remote] = $this->$local;
		}
		$array["data"] = $this->data;
		$array["template_id"] = $this->template;
		return $array;
	}

	public function toJson()
	{
		return json_encode($this->toArray());
	}

	public function deliver(&$failure = null)
	{
		$queue = new Queue;
		$queue->add($this);
		$queue->deliver();

		$failure = $this->deliveryError();

		return in_array($this, $queue->results["valid"], true);
	}

	/**
	 * Sets the delivery error message
	 *
	 * @param string @error
	 */
	public function setDeliveryError($error)
	{
		$this->_error = $error;
	}

	/**
	 * Gets a previously set delivery error message
	 *
	 * @return string
	 */
	public function deliveryError()
	{
		return $this->_error;
	}
}
