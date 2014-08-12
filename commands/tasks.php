<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;

class tasks implements CommandInterface {

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

		$return = "\nAvailable tasks:\n\n";

		foreach ($this->tasks as $task => $parameters) {
			
			$return .= "- ".$this->color->convert("%g".$task."%n")." (".$parameters["target"]."): ".( empty($parameters["description"]) ? "No description available" : $parameters["description"] );

		}

		return $return;

	}

}
