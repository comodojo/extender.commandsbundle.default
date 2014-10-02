<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
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

class status extends StandardCommand implements CommandInterface {

    static private $lockfile = "extender.pid";

    static private $statusfile = "extender.status";

    public function execute() {

        if ( \Comodojo\Extender\Checks::signals() === false ) throw new ShellException("This version of PHP does not support pnctl");

        $lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

        $statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

        try {
            
            $pid = self::pushUsrEvent($lockfile);

            sleep(1);

            $status = self::readStatus($statusfile);

            $return = self::displayStatus($pid, $status, $this->color);

        } catch (ShellException $se) {
            
            throw $se;

        }

        return $return;

    }

    static private function pushUsrEvent($lockfile) {
    
        $pid = @file_get_contents($lockfile);

        if ( $pid === false ) throw new ShellException("Extender not running or not in daemon mode");

        $signal = posix_kill($pid, SIGUSR1);

        if ( $signal === false ) throw new ShellException("Unable to send signal USR1 to extender process");

        return $pid;

    }

    static private function readStatus($statusfile) {
        
        $status = file_get_contents($statusfile);

        if ( $status === false ) throw new ShellException("Unable to read status file");

        return unserialize($status);

    }

    static private function displayStatus($pid, $status, $color) {
        
        $return = "\n *** Extender Status Resume *** \n";
        $return .= "  ------------------------------ \n\n";

        $return .= " - Process PID: ".$color->convert("%g".$pid."%n")."\n";
        $return .= " - Process running since: ".$color->convert("%g".date("r", (int)$status["STARTED"])."%n")."\n";
        $return .= " - Process runtime (sec): ".$color->convert("%g".(int)$status["TIME"]."%n")."\n\n";

        $return .= " - Current Status: ". ( $status["RUNNING"] == 1 ? $color->convert("%gRUNNING%n") : $color->convert("%yPAUSED%n") )."\n";
        $return .= " - Completed jobs: ".$color->convert("%g".$status["COMPLETED"]."%n")."\n";
        $return .= " - Failed jobs: ".$color->convert("%r".$status["FAILED"]."%n")."\n\n";

        $return .= " - Current CPU load (avg): ".$color->convert("%g".implode(", ", $status["CPUAVG"])."%n")."\n";
        $return .= " - Allocated memory (real): ".$color->convert("%g".self::convert($status["MEM"])."%n")."\n";
        $return .= " - Allocated memory (peak): ".$color->convert("%g".self::convert($status["MEMPEAK"])."%n")."\n\n";

        $return .= " - User: ".$color->convert("%g".$status["USER"]."%n")."\n";
        $return .= " - Niceness: ".$color->convert("%g".$status["NICENESS"]."%n")."\n\n";

        return $return;

    }

    static private function convert($size) {

        $unit = array('b','kb','mb','gb','tb','pb');

        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

    }

}
