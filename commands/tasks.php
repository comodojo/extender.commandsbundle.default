<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;

/**
 * List registered tasks
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

class tasks extends AbstractCommand {

    /**
     * Execute statement (what this command will do)
     *
     * List registered tasks
     *
     * Command syntax:
     *
     * - Brief version
     *
     * ./econtrol.php tasks
     *
     * - Extensive version
     *
     * ./econtrol.php tasks -e
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $extensive = $this->getOption("extensive");

        $header = "\nAvailable tasks:\n---------------\n\n";

        $content = $extensive ? self::extensive($this->color, $this->tasks) : self::brief($this->color, $this->tasks);

        return $header.$content;

    }

    static private function brief($color, $tasks) {

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Name',
            'Description'
        ));

        foreach ($tasks->getTasks(true) as $task => $parameters) {

            if ( empty($parameters["description"]) ) $description = "No description available";

            else $description = strlen($parameters["description"]) >= 60 ? substr($parameters["description"],0,80)."..." : $parameters["description"];

            $tbl->addRow(array(
                $color->convert("%g".$task."%n"),
                $description
            ));

        }

        return $tbl->getTable();

    }

    static private function extensive($color, $tasks) {

        $return = '';

        foreach ($tasks->getTasks() as $task => $parameters) {

            $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

            $tbl->addRow(array("Name",$color->convert("%g".$task."%n")));

            $tbl->addSeparator();

            $tbl->addRow(array("Description", empty($parameters["description"]) ? "No description available" : $parameters["description"] ));

            $tbl->addSeparator();

            $tbl->addRow(array("Target",$parameters["target"]));

            $tbl->addRow(array("Class",$parameters["class"]));

            $return .= $tbl->getTable()."\n\n";

        }

        return $return;

    }

}
