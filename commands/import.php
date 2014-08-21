<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

class import extends StandardCommand implements CommandInterface {

	public function execute() {

		$source = $this->getArgument("source");

		$clean = $this->getOption("clean");

		try {

			if ( $clean ) self::truncate();

            $jobs = file_get_contents($source);

            if ( $jobs === false ) throw new ShellException("Unable to read source file");

            $data = json_decode($jobs, true);

            if ( $data === false ) throw new ShellException("Invalid source file");

            $count = self::uploadJobs($data);
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (ShellException $se) {

            throw $se;

        }

		return $count .  " jobs imported in database";

	}

	static private function truncate() {
		
		try{

			$db = new EnhancedDatabase(
				EXTENDER_DATABASE_MODEL,
				EXTENDER_DATABASE_HOST,
				EXTENDER_DATABASE_PORT,
				EXTENDER_DATABASE_NAME,
				EXTENDER_DATABASE_USER,
				EXTENDER_DATABASE_PASS
			);

			$db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->truncate();

		}
		catch (DatabaseException $e) {

			unset($db);

			throw $e;

		}
		
		unset($db);
		
	}

	static private function uploadJobs($jobs) {
		
		try{

			$db = new EnhancedDatabase(
				EXTENDER_DATABASE_MODEL,
				EXTENDER_DATABASE_HOST,
				EXTENDER_DATABASE_PORT,
				EXTENDER_DATABASE_NAME,
				EXTENDER_DATABASE_USER,
				EXTENDER_DATABASE_PASS
			);

			$db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys(array("id","name","task","description","enabled",
					"min","hour","dayofmonth","month","dayofweek","year",
					"params","lastrun"));

            foreach ($jobs as $job) {

            	$db->values(array($job["id"],$job["name"],$job["task"],$job["description"],$job["enabled"],
					$job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"],
					$job["params"],NULL));

            }
                
            $result = $db->store();

		}
		catch (DatabaseException $e) {

			unset($db);

			throw $e;

		}
		
		unset($db);

		return $result['affected_rows'];
		
	}

}
