<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Console_Table;

/**
 * An extender command (default bundle)
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

class worklogs extends StandardCommand implements CommandInterface {

    public function execute() {

        $howmany = $this->getArgument("howmany");

        $from = $this->getArgument("from");

        $extensive = $this->getOption("extensive");

        $limit = is_null($howmany) ? 10 : intval($howmany);

        $offset = is_null($from) ? 0 : intval($from);

        try{

            $worklogs = self::getWorklogs($limit, $offset);

        }
        catch (\Exception $e) {

            throw new ShellException($e->getMessage());

        }

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        if ( is_null($extensive) ) {

            $tbl->setHeaders(array(
                "ID",
                "Status",
                "Name",
                "Start",
                "End",
                "S"
            ));

        } else {

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

        }

        foreach ($worklogs["data"] as $worklog) {

            $result = strlen($worklog["result"]) >= 60 ? substr($worklog["result"],0,60)."..." : $worklog["result"];

            $start = date("r", (int)$worklog["start"]);

            $end = empty($worklog["end"]) ? "-" : date("r", (int)$worklog["end"]);

            $status = $this->color->convert("%y".$worklog["status"]."%n");

            $success = $this->color->convert( $worklog["success"] == true ? "%gV%n" : "%rX%n" );

            if ( is_null($extensive) ) {

                $tbl->addRow(array(
                    $worklog["id"],
                    $status,
                    $worklog["name"],
                    $start,
                    $end,
                    $success
                ));

            } else {

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

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                            ->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
                            ->keys(array("id","pid","name","task",
                                    "status","success","result","start","end"));

                        if ( $offset == 0 ) $db->orderBy("id","DESC");

                        $result = $db->get($limit, $offset);

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
