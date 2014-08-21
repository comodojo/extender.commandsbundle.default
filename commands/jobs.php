<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

class jobs extends StandardCommand implements CommandInterface {

	public function execute() {

		$extensive = $this->getOption("extensive");

		try {

            $jobs = self::getJobs();

        } catch (ShellException $se) {

            throw $se;

        }

        if ( $extensive ) return self::extensive($this->color, $jobs);

        else return self::brief($this->color, $jobs);

	}

	static private function getJobs() {
		
		try{

			$db = new EnhancedDatabase(
				EXTENDER_DATABASE_MODEL,
				EXTENDER_DATABASE_HOST,
				EXTENDER_DATABASE_PORT,
				EXTENDER_DATABASE_NAME,
				EXTENDER_DATABASE_USER,
				EXTENDER_DATABASE_PASS
			);

			$result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
				->table(EXTENDER_DATABASE_TABLE_JOBS)
				->keys(array("id","name","task","description",
					"min","hour","dayofmonth","month","dayofweek","year",
					"params","lastrun","enabled"))
				->get();

		}
		catch (Exception $e) {

			unset($db);

			throw $e;

		}
		
		unset($db);

		return $result['data'];
		
	}

	static private function brief($color, $jobs) {

		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->setHeaders(array(
			'Expression',
			'Name',
			'Task',
			'Description',
			'Enabled'
		));

		foreach ($jobs as $job) {

			$description = strlen($job["description"]) >= 60 ? substr($job["description"],0,60)."..." : $job["description"];

			$tbl->addRow(array(
				implode(" ",array($job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"])),
				$job["name"],
				$job["task"],
				$description,
				$color->convert($job["enabled"] ? "%gYES%n" : "%rNO%n"),
			));

		}

		return $return = "\nAvailable jobs:\n---------------\n\n".$tbl->getTable();

	}

	static private function extensive($color, $jobs) {

		$return = "\nAvailable jobs:\n---------------\n\n";

		foreach ($jobs as $job) {

			$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

			$tbl->addRow(array("Name",$job["name"]));

			$tbl->addSeparator();

			$tbl->addRow(array("Expression",implode(" ",array($job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"]))));

			$tbl->addRow(array("Task",$job["task"]));

			$tbl->addRow(array("Description",$job["description"]));

			$tbl->addRow(array("Enabled",$color->convert($job["enabled"] ? "%gYES%n" : "%rNO%n")));

			$tbl->addRow(array("Lastrun",empty($job["lastrun"]) ? $color->convert("%rNEVER%n") : date("r", (int)$job["lastrun"])));

			$tbl->addSeparator();

			$tbl->addRow(array("Parameters",var_export(unserialize($job["params"]), true)));

			$return .= $tbl->getTable()."\n\n";

		}

		return $return;

	}

}
