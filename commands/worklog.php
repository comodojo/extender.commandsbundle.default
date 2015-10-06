<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Console_Table;

/**
 * Show worklog's detail
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class worklog extends AbstractCommand {

    /**
     * Execute statement (what this command will do)
     *
     * Show worklog's detail
     *
     * Command syntax:
     *
     * ./econtrol.php worklog 42
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $wkid = $this->getArgument("id");

        if ( is_null($wkid) ) throw new ShellException("Invalid worklog id");
        
        try{

            $worklog = self::getWorklog($wkid);

        }
        catch (\Exception $e) {

            throw new ShellException($e->getMessage());

        }

        if ( $worklog->getLength() == 0 ) throw new ShellException("Cannot find worklog (wrong id?)");

        $wklg = $worklog->getData();

        $wklg = $wklg[0];
        
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

    static private function getWorklog($wkid) {
        
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
