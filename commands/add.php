<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

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

class add extends StandardCommand implements CommandInterface {

    public function execute() {

        $enable = $this->getOption("enable");

        $expression = $this->getArgument("expression");

        $name = $this->getArgument("name");

        $task = $this->getArgument("task");

        $description = $this->getArgument("description");

        $description = is_null($description) ? '' : $description;

        $parameters = self::processParameters($this->color, $this->getArgument("parameters"));

        try {
            
            list($id, $next_calculated_run) = Scheduler::addSchedule($expression, $name, $task, $description, $parameters);

            if ( $enable ) Scheduler::enableSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        if ( $enable ) return $this->color->convert("\n%gJob added and activated; next calculated runtime: ".$next_calculated_run."%n");

        else return $this->color->convert("\n%gJob added but not activated.%n");

    }

    static private function processParameters($color, $parameters) {

        $params = array();

        if ( !is_null($parameters) ) {

            $p = explode(",", trim($parameters));

            foreach ($p as $parameter) {
                
                $ps = explode("=", $parameter);

                if ( sizeof($ps) == 2 ) {

                    // if ( is_numeric($ps[1]) ) $value = (int)$ps[1];

                    // if ( is_bool($ps[1]) ) $value = filter_var($ps[1], FILTER_VALIDATE_BOOLEAN);

                    // if ( is_null($ps[1]) ) $value = null;

                    //else $value = $ps[1];

                    $value = $ps[1];

                    $params[$ps[0]] = $value;

                }

                else echo $color->convert("\n%ySkipping invalid parameter: ".$parameter."%n");

            }


        }

        return $params;

    }

}
