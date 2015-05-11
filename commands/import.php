<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Extender\Scheduler\Scheduler;

/**
 * Import jobs from json file
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class import extends StandardCommand implements CommandInterface {

    /**
     * Execute statement (what this command will do)
     *
     * Import jobs from json file
     *
     * Command syntax:
     *
     * - Import and merge
     *
     * ./econtrol.php import my_jobs_list.json
     *
     * - Import and clean
     *
     * ./econtrol.php import my_jobs_list.json -c
     *
     * @return  string
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $source = $this->getArgument("source");

        $clean = $this->getOption("clean");

        try {

            if ( $clean ) self::truncate();

            $jobs = file_get_contents($source);

            if ( $jobs === false ) throw new ShellException("Unable to read source file");

            $data = json_decode($jobs, true);

            if ( $data === false ) throw new ShellException("Invalid source file");

            $count = self::uploadJobs($data, $this->color);
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (ShellException $se) {

            throw $se;

        }

        return "\n--------------------\n\n".$count .  " job(s) imported in database";

    }

    static private function truncate() {
        
        echo "\nCleaning jobs table... ";

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

            echo "error!\n";

            throw $e;

        }
        
        unset($db);

        echo "done!\n";

    }

    static private function uploadJobs($jobs, $color) {
        
        $imported = 0;

        foreach ($jobs as $job) {
            
            $expression = $job["min"]." ".$job["hour"]." ".$job["dayofmonth"]." ".$job["month"]." ".$job["dayofweek"]." ".$job["year"];

            try {

                $parameters = unserialize($job["params"]);
            
                list($id, $next_calculated_run) = Scheduler::addSchedule($expression, $job["name"], $job["task"], $job["description"], $parameters);

                if ( $job["enabled"] ) Scheduler::enableSchedule($job["name"]);

            } catch (Exception $e) {
                
                echo "\n";
                echo $color->convert( "%rError importing job ".$job["name"]."(id:".$job["id"]."): ".$e->getMessage()."%n" );

            }

            $imported++;

            echo "\n";
            echo $color->convert( "%gJob ".$job["name"]." imported; next run date: ".$next_calculated_run."%n" );

        }

        return $imported;
        
    }

}
