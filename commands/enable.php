<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

/**
 * Enable a registered job
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

class enable extends StandardCommand implements CommandInterface {

    /**
     * Execute statement (what this command will do)
     *
     * enable command will activate job referenced from "name" argument
     *
     * Command syntax:
     *
     * ./econtrol.php enable my_midnight_job
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $name = $this->getArgument("name");

        try {
            
            $result = Scheduler::enableSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

        return $this->color->convert("\n%gJob activated%n");

    }

}
