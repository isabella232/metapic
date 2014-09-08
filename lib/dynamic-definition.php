<?php
namespace MetaPic;

trait Dynamic_Definition {
	public function __call($name, $args) {
		if (is_callable($this->$name)) {
			return call_user_func($this->$name, $args);
		}
		else {
			throw new \RuntimeException("Method {$name} does not exist");
		}
	}

	public function __set($name, $value) {
		$this->$name = is_callable($value)?
			$value->bindTo($this, $this):
			$value;
	}
}