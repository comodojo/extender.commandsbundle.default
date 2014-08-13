<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;
use \Console_Table;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

class jobs implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = array();

	public function setOptions($options) {

		$this->options = $options;

		return $this;

	}

	public function setArguments($args) {

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

	public function getArgument($arg) {

		if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

		else return null;

	}

	public function exec() {

		try {

            $jobs = self::getJobs();

        } catch (ShellException $se) {

            throw $se;

        }

		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->setHeaders(array(
			'Expression',
			'Name',
			'Task',
			'Description',
			'Enabled',
			'Lastrun'
		));

		foreach ($jobs as $job) {

			$description = strlen($job["description"]) >= 60 ? substr($job["description"],0,60)."..." : $job["description"];

			$tbl->addRow(array(
				implode(" ",array($job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"])),
				$job["name"],
				$job["task"],
				$description,
				$this->color->convert($job["enabled"] ? "%gYES%n" : "%rNO%n"),
				$job["lastrun"]
			));

		}

		return $return = "\nAvailable jobs:\n\n".$tbl->getTable();

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

}
