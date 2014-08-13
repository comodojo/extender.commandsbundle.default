<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class add implements CommandInterface {

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

		$enable = $this->getOption("enable");

		$expression = $this->getArgument("expression");

		$name = $this->getArgument("name");

		$task = $this->getArgument("task");

		$description = $this->getArgument("description");

		$description = is_null($description) ? '' : $description;

		$parameters = $this->getArgument("parameters");

		$parameters = is_null($parameters) ? array() : $parameters;

		try {
			
			list($id, $next_calculated_run) = Scheduler::addSchedule($expression, $name, $task, $description, $parameters);

			if ( $enable ) Scheduler::enableSchedule($name);

		} catch (Exception $e) {
			
			throw $e;

		}

		if ( $enable ) return $this->color->convert("\n%gJob added and activated; next calculated runtime: ".$next_calculated_run."%n");

		else return $this->color->convert("\n%gJob added but not activated.%n");

	}

}
