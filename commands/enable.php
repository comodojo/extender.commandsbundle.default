<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

class enable extends StandardCommand implements CommandInterface {

	public function execute() {

		$name = $this->getArgument("name");

		try {
			
			$result = Scheduler::enableSchedule($name);

		} catch (Exception $e) {
			
			throw $e;

		}

		if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

		return $this->color->convert("\n%gJob activated%n");

	}

}
