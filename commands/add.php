<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Scheduler\Scheduler;

/**
 * Add a job into scheduler database
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

class add extends AbstractCommand {

    /**
     * Execute statement (what this command will do)
     *
     * add command will put a job into scheduler database using:
     *
     * - arguments
     *      * expression: the cron expression to match
     *      * name: job name
     *      * task: the task the job will execute
     *      * (optional) description: job description
     *      * (optional) parameters: a comma separated, not spaced list of 'parameter=value'
     *
     * - options
     *      * enable (-e): enable job
     *
     * Command syntax:
     *
     * ./econtrol.php add "59 23 * * *" my_midnight_job MyTask "a job that should run around midnight" test=false -e
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
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

    /**
     * Convert provided parameters (if any) into array
     *
     * @param   object  $color      ConsoleColor instance
     * @param   string  $parameters Provided parameters
     *
     * @return  array
     */
    static private function processParameters($color, $parameters) {

        $params = array();

        if ( !is_null($parameters) ) {

            $p = explode(",", trim($parameters));

            foreach ($p as $parameter) {
                
                $ps = explode("=", $parameter);

                if ( sizeof($ps) == 2 ) {

                    $value = $ps[1];

                    $params[$ps[0]] = $value;

                }

                else echo $color->convert("\n%ySkipping invalid parameter: ".$parameter."%n");

            }


        }

        return $params;

    }

}
