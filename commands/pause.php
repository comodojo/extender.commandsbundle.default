<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;

/**
 * Pause extender using sigStop (SIGSTP)
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

class pause extends StandardCommand implements CommandInterface {

    static private $lockfile = "extender.pid";

    /**
     * Execute statement (what this command will do)
     *
     * Pause extender using sigStop (SIGSTP)
     *
     * Command syntax:
     *
     * ./econtrol.php pause
     *
     * @return  string
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

        if ( \Comodojo\Extender\Checks::signals() === false ) throw new ShellException("This version of PHP does not support pnctl");

        $lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

        try {
            
            $pid = self::pushStopEvent($lockfile);

        } catch (ShellException $se) {
            
            throw $se;

        }

        return $this->color->convert("%yExtender paused%n")."\n";

    }

    static private function pushStopEvent($lockfile) {
    
        $pid = file_get_contents($lockfile);

        if ( $pid === false ) throw new ShellException("Extender not running or not in daemon mode");

        $signal = posix_kill($pid, SIGTSTP);

        if ( $signal === false ) throw new ShellException("Unable to send signal STOP to extender process");

        return $pid;

    }

}
