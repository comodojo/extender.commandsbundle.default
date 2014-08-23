<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;

class tasks extends StandardCommand implements CommandInterface {

	public function execute() {

		$extensive = $this->getOption("extensive");

		$header = "\nAvailable tasks:\n---------------\n\n";

		$content = $extensive ? self::extensive($this->color, $this->tasks) : self::brief($this->color, $this->tasks);

		return $header.$content;

	}

	static private function brief($color, $tasks) {

		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->setHeaders(array(
			'Name',
			'Description'
		));

		foreach ($tasks->getTasks() as $task => $parameters) {

			if ( empty($parameters["description"]) ) $description = "No description available";

			else $description = strlen($parameters["description"]) >= 60 ? substr($parameters["description"],0,80)."..." : $parameters["description"];

			$tbl->addRow(array(
				$color->convert("%g".$task."%n"),
				$description
			));

		}

		return $tbl->getTable();

	}

	static private function extensive($color, $tasks) {

		$return = '';

		foreach ($tasks->getTasks() as $task => $parameters) {

			$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

			$tbl->addRow(array("Name",$color->convert("%g".$task."%n")));

			$tbl->addSeparator();

			$tbl->addRow(array("Description", empty($parameters["description"]) ? "No description available" : $parameters["description"] ));

			$tbl->addSeparator();

			$tbl->addRow(array("Target",$parameters["target"]));

			$tbl->addRow(array("Class",$parameters["class"]));

			$return .= $tbl->getTable()."\n\n";

		}

		return $return;

	}

}
