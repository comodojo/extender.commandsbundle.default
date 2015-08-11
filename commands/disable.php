<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

/**
 * Disable a registered job
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

class disable extends AbstractCommand {

    /**
     * Execute statement (what this command will do)
     *
     * disable command will disable job referenced from "name" argument
     *
     * Command syntax:
     *
     * ./econtrol.php disable my_midnight_job
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $name = $this->getArgument("name");

        try {
            
            $result = Scheduler::disableSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

        return $this->color->convert("\n%yJob deactivated%n");

    }

}
