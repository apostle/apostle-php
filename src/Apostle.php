<?php

class Apostle
{
	private static $_instance;

	public $deliver = true;
	public $deliveryHost = 'http://deliver.apostle.io';
	public $domainKey;

	/**
	 * @param string $domainKey The key to authorise mail on this domain
	 */
	public function __construct($domainKey, $config = array())
	{
		foreach(array('deliver', 'deliveryUrl') as $attr)
		{
			if(!isset($config[$attr]))
			{
				continue;
			}
			$this->$attr = $config[$attr];
			unset($config[$attr]);
		}

		$this->domainKey = $domainKey;
	}

    // ----------------------------------------
    // static accessors

	/**
     * Shortcut for initializing the static Apostle instance
     * @return Apostle
     */
	public static function setup($domainKey, $config = array())
	{
		self::reset(new Apostle($domainKey, $config));
	}

	/**
	 * Returns the static Apostle instance
	 * @return Apostle
	 */
	public static function instance()
	{
		if(isset(self::$_instance))
		{
			return self::$_instance;
		}

		throw new Apostle\UninitializedException();
	}

	/**
	 * Resets the static Apostle instance
	 */
	public static function reset($instance)
	{
		return self::$_instance = $instance;
	}
}
