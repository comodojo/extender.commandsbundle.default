<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Execute as SourceExecute;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Exception;

class Execute extends AbstractCommand {

    public function execute() {

        $task = $this->getArgument('task');

        $parameters = self::processParameters($this->getArgument('parameters'));

        print "\nExecuting task ".$task."...\n";

        try {

            $run_result = SourceExecute::runTask($task, $parameters);
            
        } catch (Exception $e) {
            
            throw $e;

        }

        $pid = $run_result[0];

        $success = $run_result[2];

        $start_timestamp = $run_result[3];

        $end_timestamp = $run_result[4];

        $result = strlen($run_result[5]) >= 80 ? substr($run_result[5],0,80)."..." : $run_result[5];

        $wid = $run_result[7];

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Pid',
            'Wid',
            'Success',
            'Result (truncated)',
            'Time elapsed'
        ));

        $tbl->addRow(array(
            $pid,
            $wid,
            $this->color->convert($success ? "%gYES%n" : "%rNO%n"),
            $this->color->convert($success ? "%g".$result."%n" : "%r".$result."%n"),
            $success ? ($end_timestamp-$start_timestamp) : "--"
        ));

        return $tbl->getTable();

    }

    private static function processParameters($parameters) {

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
