<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class disable implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = array();

	public function setOptions($options) {

		$this->options = $options;

		return $this;

	}

	public function setArgs($args) {

		$this->args = $args;

		return $this;

	}

	public function setColor($color) {

		$this->color = $color;

		return $this;

	}

	public function setTasks($tasks) {

		$this->tasks = $tasks;

		return $this;

	}

	public function getOption($option) {

		if ( array_key_exists($option, $this->options) ) return $this->options[$option];

		else return null;

	}

	public function getArg($arg) {

		if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

		else return null;

	}

	public function exec() {

		$name = $this->getArg("name");

		try {
			
			$result = Scheduler::disableSchedule($name);

		} catch (Exception $e) {
			
			throw $e;

		}

		return $this->color->convert("\n%yJob deactivated%n");

	}

}
