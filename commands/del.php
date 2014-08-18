<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class del implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = array();

	public function setOptions($options) {

		$this->options = $options;

		return $this;

	}

	public function setArguments($args) {

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

	public function getArgument($arg) {

		if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

		else return null;

	}

	public function execute() {

		$name = $this->getArgument("name");

		try {
			
			$result = Scheduler::removeSchedule($name);

		} catch (Exception $e) {
			
			throw $e;

		}

		if ( !$result ) return $this->color->convert("\n%rJob not found%n");

		return $this->color->convert("\n%yJob deleted%n");

	}

}
