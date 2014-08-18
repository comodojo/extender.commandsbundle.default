<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;

class tasks implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = null;

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

		$return = "\nAvailable tasks:\n\n";

		foreach ($this->tasks->getTasks() as $task => $parameters) {
			
			$return .= "- ".$this->color->convert("%g".$task."%n")." (".$parameters["target"]."): ".( empty($parameters["description"]) ? "No description available" : $parameters["description"] );

		}

		return $return;

	}

}
