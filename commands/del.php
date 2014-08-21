<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class del extends StandardCommand implements CommandInterface {

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
