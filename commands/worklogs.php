<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Console_Table;

class worklogs extends StandardCommand implements CommandInterface {

	public function execute() {

		$howmany = $this->getArgument("howmany");

		$from = $this->getArgument("from");

		$limit = ( is_null($howmany) || !is_int($howmany) ) ? 10 : $howmany;

		$offset = ( is_null($from) || !is_int($from) ) ? 0 : $from;

		try{

			$worklogs = self::getWorklogs($limit, $offset);

		}
		catch (\Exception $e) {

			throw new ShellException($e->getMessage());

		}

		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->setHeaders(array(
			"ID",
			"Status",
			"PID",
			"Name",
			"Task",
			"Start",
			"End",
			"S",
			"Result"
		));

		foreach ($worklogs["data"] as $worklog) {

			$result = strlen($worklog["result"]) >= 60 ? substr($worklog["result"],0,60)."..." : $worklog["result"];

			$start = date("r", (int)$worklog["start"]);

			$end = empty($worklog["end"]) ? "-" : date("r", (int)$worklog["end"]);

			$status = $this->color->convert("%y".$worklog["status"]."%n");

			$success = $this->color->convert( $worklog["success"] == true ? "%gV%n" : "%rX%n" );

			$tbl->addRow(array(
				$worklog["id"],
				$status,
				$worklog["pid"],
				$worklog["name"],
				$worklog["task"],
				$start,
				$end,
				$success,
				$result
			));

		}

		return "Found ".$worklogs['length']." worklog(s):\n--------------------\n\n".$tbl->getTable();

	}

	static private function getWorklogs($limit, $offset) {
		
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
				->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
				->keys(array("id","pid","name","task",
					"status","success","result","start","end"))
				->get($limit, $offset);

		}
		catch (DatabaseException $de) {

			throw $de;

		}
		catch (\Exception $e) {

			throw $e;

		}
		
		return $result;
		
	}

}
