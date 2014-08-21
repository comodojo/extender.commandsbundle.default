<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

class export extends StandardCommand implements CommandInterface {

	public function execute() {

		$destination = $this->getArgument("destination");

		try {

            $jobs = self::getJobs();

            $data = json_encode($jobs);

            $export = file_put_contents($destination, $data);

            if ( $export === false ) throw new ShellException("Unable to write destination file");
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (ShellException $se) {

            throw $se;

        }

		return count($jobs) .  " jobs exported to " . $destination;

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
					"params","enabled"))
				->get();

		}
		catch (DatabaseException $e) {

			unset($db);

			throw $e;

		}
		
		unset($db);

		return $result['data'];
		
	}

}
