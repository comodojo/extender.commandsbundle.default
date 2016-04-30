<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\System as SourceSystem;
use \Comodojo\Exception\ShellException;
use \Exception;

/**
 * @package     Comodojo extender commands
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

class System extends AbstractCommand {

    public function execute() {

        $force = $this->getOption("force");

        $clean = $this->getOption("clean");

        $action = $this->getArgument("action");

        try {

            switch ($action) {

                case 'status':

                    $return = $this->status();

                    break;

                case 'check':

                    $return = $this->check();

                    break;

                case 'install':

                    $return = $this->install($force, $clean);

                    break;

                case 'pause':

                    $return = $this->pause();

                    break;

                case 'resume':

                    $return = $this->resume();

                    break;

                default:

                    $return = $this->color->convert("\n%yInvalid action ".$action."%n");

                    break;

            }

        } catch (ShellException $se) {

            throw $se;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

    private function check() {

        $checks = SourceSystem::doCheck();

        $constants = $checks['constants'];

        $multithread = $checks['multithread'];

        $signals = $checks['signals'];

        $database = $checks['database'];

        $real_path = $checks['real_path'];

        $multithread_enabled = $checks['multithread_enabled'];

        $idle_time = $checks['idle_time'];

        $max_bytes = $checks['max_result_bytes'];

        $max_childs = $checks['max_childs'];

        $max_childs_runtime = $checks['max_childs_runtime'];

        $parent_niceness = $checks['parent_niceness'];

        $child_niceness = $checks['child_niceness'];

        $return = "Extender checks:\n----------------\n\n";

        $return .= "Extender minimum parameters configured: " . $this->color->convert( $constants === true ? "%gPASSED%n" : "%r".$constants."%n" );

        $return .= "\nMultiprocess support available: " . $this->color->convert( $multithread === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\nDaemon support (signaling): " . $this->color->convert( $signals === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\nExtender database available and configured: " . $this->color->convert( $database === true ? "%gYES%n" : "%rNO%n" );

        $return .= "\n\nExtender parameters:\n--------------------\n\n";

        $return .= "Framework path: " . $this->color->convert( "%g".$real_path."%n");

        $return .= "\nMultiprocess enabled: " . $this->color->convert( "%g".$multithread_enabled."%n");

        $return .= "\nIdle time (daemon mode): " . $this->color->convert( "%g".$idle_time."%n");

        $return .= "\nMax result bytes per task: " . $this->color->convert( "%g".$max_bytes."%n");

        $return .= "\nMax childs: " . $this->color->convert( "%g".$max_childs."%n");

        $return .= "\nMax child runtime: " . $this->color->convert( "%g".$max_childs_runtime."%n");

        $return .= "\nParent niceness: " . $this->color->convert( !is_null($parent_niceness) ? "%g".$parent_niceness."%n" : "%ydefault%n" );

        $return .= "\nChilds niceness: " . $this->color->convert( !is_null($child_niceness) ? "%g".$child_niceness."%n" : "%ydefault%n" );

        return $return;

    }

    private function status() {

        list($pid, $status, $queue) = SourceSystem::getStatus();

        $return = "\n *** Extender Status Resume *** \n";
        $return .= "  ------------------------------ \n\n";

        $return .= " - Process PID: ".$this->color->convert("%g".$pid."%n")."\n";
        $return .= " - Process running since: ".$this->color->convert("%g".date("r", (int)$status["STARTED"])."%n")."\n";
        $return .= " - Process runtime (sec): ".$this->color->convert("%g".(int)$status["TIME"]."%n")."\n\n";

        $return .= " - Running jobs: ".$this->color->convert("%g".$queue["RUNNING"]."%n")."\n";
        $return .= " - Queued jobs: ".$this->color->convert("%g".$queue["QUEUED"]."%n")."\n\n";

        $return .= " - Current Status: ". ( $status["RUNNING"] == 1 ? $this->color->convert("%gRUNNING%n") : $this->color->convert("%yPAUSED%n") )."\n";
        $return .= " - Completed jobs: ".$this->color->convert("%g".$status["COMPLETED"]."%n")."\n";
        $return .= " - Failed jobs: ".$this->color->convert("%r".$status["FAILED"]."%n")."\n\n";

        $return .= " - Current CPU load (avg): ".$this->color->convert("%g".implode(", ", $status["CPUAVG"])."%n")."\n";
        $return .= " - Allocated memory (real): ".$this->color->convert("%g".self::convert($status["MEM"])."%n")."\n";
        $return .= " - Allocated memory (peak): ".$this->color->convert("%g".self::convert($status["MEMPEAK"])."%n")."\n\n";

        $return .= " - User: ".$this->color->convert("%g".$status["USER"]."%n")."\n";
        $return .= " - Niceness: ".$this->color->convert("%g".$status["NICENESS"]."%n")."\n\n";

        return $return;

    }

    private function pause() {

        try {

            $pid = SourceSystem::pause();

        } catch (Exception $se) {

            throw $se;

        }

        return $this->color->convert("%yExtender paused (pid ".$pid.")%n")."\n";

    }

    private function resume() {

        try {

            $pid = SourceSystem::resume();

        } catch (Exception $se) {

            throw $se;

        }

        return $this->color->convert("%gExtender resumed (pid ".$pid.")%n")."\n";

    }

    private function install($force, $clean) {

        $this->logger->debug("Checking database status");

        $installed = SourceSystem::checkInstalled();

        if ( $installed AND $clean ) {

            try {

                $this->logger->info("Truncating database");

                SourceSystem::emptyDatabase();

            } catch (Exception $se) {

                throw $se;

            }

            return $this->color->convert("\n%gExtender database cleaned.%n\n");

        }

        if ( $installed AND is_null($force) ) return $this->color->convert("\n%yExtender already installed, use --force to reinstall.%n\n");

        try {

            $this->logger->info("Installing database");

            SourceSystem::installDatabase();

        } catch (Exception $e) {

            throw $e;

        }

        return $this->color->convert("\n%gExtender successfully installed%n\n");

    }

    static private function convert($size) {

        $unit = array('b','kb','mb','gb','tb','pb');

        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

    }

}
