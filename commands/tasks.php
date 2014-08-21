<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;

class tasks extends StandardCommand implements CommandInterface {

	public function execute() {

		$extensive = $this->getOption("extensive");

		$return = "\nAvailable tasks:\n---------------\n\n";

		foreach ($this->tasks->getTasks() as $task => $parameters) {
			
			if ( $extensive ) $return .= "- ".$this->color->convert("%g".$task."%n")." (".$parameters["target"]."): ".( empty($parameters["description"]) ? "No description available" : $parameters["description"] );

			else $return .= "- ".$this->color->convert("%g".$task."%n").": ".( empty($parameters["description"]) ? "No description available" : $parameters["description"] );

		}

		return $return;

	}

}
