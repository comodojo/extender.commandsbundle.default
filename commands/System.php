<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\System as SourceSystem;
use \Comodojo\Exception\ShellException;
use \Exception;

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

        return $color->convert("%yExtender paused (pid ".$pid.")%n")."\n";

    }

    private function resume() {

        try {
            
            $pid = SourceSystem::resume();

        } catch (Exception $se) {
            
            throw $se;

        }

        return $color->convert("%yExtender resumed (pid ".$pid.")%n")."\n";

    }

    private function install($force, $clean) {

        $installed = SourceSystem::checkInstalled();

        if ( $installed AND $clean ) {

            try {
                
                SourceSystem::emptyDatabase();

            } catch (Exception $se) {

                throw $se;

            }

            return $this->color->convert("\n%gExtender database cleaned.%n\n");

        }

        if ( $installed AND is_null($force) ) return $this->color->convert("\n%yExtender already installed, use --force to reinstall.%n\n");

        try {

            SourceSystem::installDatabase();

        } catch (Exception $se) {

            throw $e;

        }

        return $this->color->convert("\n%gExtender successfully installed%n\n");

    }

    static private function convert($size) {

        $unit = array('b','kb','mb','gb','tb','pb');

        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

    }

}
