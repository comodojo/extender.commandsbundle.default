<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;

class tasks extends StandardCommand implements CommandInterface {

	public function execute() {

		$return = "\nAvailable tasks:\n\n";

		foreach ($this->tasks->getTasks() as $task => $parameters) {
			
			$return .= "- ".$this->color->convert("%g".$task."%n")." (".$parameters["target"]."): ".( empty($parameters["description"]) ? "No description available" : $parameters["description"] );

		}

		return $return;

	}

}
