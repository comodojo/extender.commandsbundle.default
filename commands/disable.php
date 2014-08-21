<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class disable extends StandardCommand implements CommandInterface {

	public function execute() {

		$name = $this->getArgument("name");

		try {
			
			$result = Scheduler::disableSchedule($name);

		} catch (Exception $e) {
			
			throw $e;

		}

		if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

		return $this->color->convert("\n%yJob deactivated%n");

	}

}
