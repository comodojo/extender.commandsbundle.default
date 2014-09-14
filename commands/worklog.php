<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Console_Table;

class worklog extends StandardCommand implements CommandInterface {

	public function execute() {

		$wkid = $this->getOption("id");

		if ( is_null($wkid) ) throw new ShellException("Invalid worklog id");
		
		try{

			$worklog = self::getWorklog();

		}
		catch (\Exception $e) {

			throw new ShellException($e->getMessage());

		}

		if ( $worklog["length"] == 0 ) throw new ShellException("Cannot find worklog (wrong id?)");

		$wklg = $worklog["data"][0];

		
		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->addRow(array("ID",$wkid));

		$tbl->addSeparator();

		$tbl->addRow(array("Name",$wklg["name"]));

		$tbl->addRow(array("PID",$wklg["pid"]));

		$tbl->addRow(array("Status",$this->color->convert("%y".$wklg["status"]."%n")));

		$tbl->addRow(array("Task",$wklg["task"]));

		$tbl->addSeparator();

		$tbl->addRow(array("Start", date("r", (int)$wklg["start"])));

		$tbl->addRow(array("End", empty($wklg["end"]) ? "-" : date("r", (int)$wklg["end"])));

		$tbl->addSeparator();

		$tbl->addRow(array("Success",$this->color->convert( $wklg["success"] == true ? "%gV%n" : "%rX%n" )));

		$tbl->addRow(array("Result",$wklg["result"]));


		return "Requested worklog:\n------------------\n\n".$tbl->getTable();

	}

	static private function getWorklog() {
		
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
				->keys(array("pid","name","task",
					"status","success","result","start","end"))
				->where("id","=",$wkid)
				->get();

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
