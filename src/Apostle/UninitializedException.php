<?php

namespace Apostle;

/**
 * Thrown when Apostle::instance() is called without
 * Apostle being set up
 */
class UninitializedException extends \Exception {
	protected $message = "Apostle has not been set up.
		Initialize Apostle by calling Apostle::setup('Your Domain Key')";
};
