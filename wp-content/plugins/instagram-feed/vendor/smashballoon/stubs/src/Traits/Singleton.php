<?php

namespace Smashballoon\Stubs\Traits;

trait Singleton
{
	protected static $instance;
	final public static function getInstance()
	{
		return isset(static::$instance)
			? static::$instance
			: static::$instance = new static;
	}
	final private function __construct() {
		$this->init();
	}
	protected function init() {}
	final public function __wakeup() {}
	final private function __clone() {}
}