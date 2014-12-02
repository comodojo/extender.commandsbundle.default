<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

/**
 * Export jobs into json file
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

class export extends StandardCommand implements CommandInterface {

    /**
     * Execute statement (define what this command will do)
     *
     * export command will dump the whole job database into provided "destination" json file
     *
     * Command syntax:
     *
     * ./econtrol.php export /backup/jobs.json
     *
     * @return  string
     * @throws  DatabaseException
     * @throws  ShellException
     */
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

    /**
     * Get whole jobs' table from scheduler database
     *
     * @return  array
     */
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
