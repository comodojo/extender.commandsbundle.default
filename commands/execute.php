<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;

class execute extends StandardCommand implements CommandInterface {

    public function execute() {

        $task = $this->getArgument('task');

        $parameters = self::processParameters($this->getArgument('parameters'));

        if ( $this->tasks->isTaskRegistered($task) == false ) throw new ShellException("Task is not registered");

        $task_target = $this->tasks->getTarget($task);

        if ( is_null($task_target) ) throw new ShellException("Task target not available");

        if ( file_exists($task_target) === false ) throw new ShellException("Task file does not exists");

        if ( (include($task_target)) === false ) throw new ShellException("Task file cannot be included");

        print "\nExecuting task ".$task."...\n"; 

        $run_result = $this->runTask($task, $parameters);

        $pid = $run_result[0];

        $success = $run_result[2];

        $start_timestamp = $run_result[3];

        $end_timestamp = $run_result[4];

        $result = strlen($run_result[5]) >= 80 ? substr($run_result[5],0,80)."..." : $run_result[5];

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Pid',
            'Success',
            'Result (truncated)',
            'Time elapsed'
        ));

        $tbl->addRow(array(
            $pid,
            $this->color->convert($success ? "%gYES%n" : "%rNO%n"),
            $this->color->convert($success ? "%g".$result."%n" : "%r".$result."%n"),
            $success ? ($end_timestamp-$start_timestamp) : "--"
        ));

        return $tbl->getTable();

    }

    private function runTask($task, $parameters) {

        $start_timestamp = microtime(true);

        $name = 'ECONTROL';

        $id = 0;

        $class = $this->tasks->getClass($task);

        $task_class = "\\Comodojo\\Extender\\Task\\".$class;

        try {

            // create a task instance

            $thetask = new $task_class($parameters, null, $name, $start_timestamp, false);

            // get the task pid (we are in singlethread mode)

            $pid = $thetask->getPid();

            // run task

            $result = $thetask->start();
        
        }
        catch (Exception $e) {
        
            return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id);
        
        }

        return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id);

    }

    static private function processParameters($parameters) {

        if ( is_null($parameters) ) return array();

        $params = array();

        $p = explode(",", trim($parameters));

        foreach ($p as $parameter) {
            
            $ps = explode("=", $parameter);

            if ( sizeof($ps) == 2 ) {

                $value = $ps[1];

                $params[$ps[0]] = $value;

            }

            else echo "\nSkipping invalid parameter: ".$parameter."\n";

        }

        return $params;

    }

}
