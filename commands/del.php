<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

/**
 * Delete job from scheduler database
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

class del extends AbstractCommand {

    /**
     * Execute statement (what this command will do)
     *
     * del will use argument "name" to remove relative job from scheduler database
     *
     * Command syntax:
     *
     * ./econtrol.php del my_midnight_job
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        $name = $this->getArgument("name");

        try {
            
            $result = Scheduler::removeSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        if ( !$result ) return $this->color->convert("\n%rJob not found%n");

        return $this->color->convert("\n%yJob deleted%n");

    }

}
