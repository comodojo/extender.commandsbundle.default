<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\Checks;

/**
 * Check for common configurations parameters and environment settings
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

class check extends StandardCommand implements CommandInterface {

    /**
     * Execute statement (define what this command will do)
     *
     * check command will test framework installation and environment parameters
     *
     * Command syntax:
     *
     * ./econtrol.php check
     *
     * @return  string
     */
    public function execute() {

        $constants = Checks::constants();

        $multithread = Checks::multithread();

        $signals = Checks::signals();

        $database = Checks::database();

        $return = "Extender checks:\n----------------\n\n";

        $return .= "Extender minimum parameters configured: " . $this->color->convert( $constants === true ? "%gPASSED%n" : "%r".$constants."%n" );

        $return .= "\nMultiprocess support available: " . $this->color->convert( $multithread === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\nDaemon support (signaling): " . $this->color->convert( $signals === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\nExtender database available and configured: " . $this->color->convert( $database === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\n\nExtender parameters:\n--------------------\n\n";

        $return .= "Framework path: " . $this->color->convert( "%g".EXTENDER_REAL_PATH."%n");

        $return .= "\nMultiprocess enabled: " . $this->color->convert( "%g".EXTENDER_MULTITHREAD_ENABLED."%n");

        $return .= "\nIdle time (daemon mode): " . $this->color->convert( "%g".EXTENDER_IDLE_TIME."%n");

        $return .= "\nMax result bytes per task: " . $this->color->convert( "%g".EXTENDER_MAX_RESULT_BYTES."%n");

        $return .= "\nMax child runtime: " . $this->color->convert( "%g".EXTENDER_MAX_CHILDS_RUNTIME."%n");

        $return .= "\nParent niceness: " . $this->color->convert( defined('EXTENDER_PARENT_NICENESS') ? "%g".EXTENDER_PARENT_NICENESS."%n" : "%ydefault%n" );

        $return .= "\nChilds niceness: " . $this->color->convert( defined('EXTENDER_CHILD_NICENESS') ? "%g".EXTENDER_CHILD_NICENESS."%n" : "%ydefault%n" );

        return $return;     

    }

}
