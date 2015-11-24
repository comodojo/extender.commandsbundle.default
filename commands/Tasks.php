<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Tasks as SourceTasks;
use \Console_Color2;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class Tasks extends AbstractCommand {

    public function execute() {

        $extensive = $this->getOption("extensive");

        try {
            
            $return = $this->tasks($extensive);

        } catch (ShellException $se) {
            
            throw $se;

        } catch (Exception $e) {
            
            throw $e;

        }

        return $return;

    }

    private function tasks($extensive) {

        $header = "\nAvailable tasks:\n---------------\n\n";

        $tasks = SourceTasks::show();

        $content = $extensive ? self::checks_extensive($this->color, $tasks) : self::checks_brief($this->color, $tasks);

        return $header.$content;

    }

    static private function checks_brief($color, $tasks) {

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Name',
            'Description'
        ));

        foreach ($tasks as $task => $parameters) {

            if ( empty($parameters["description"]) ) $description = "No description available";

            else $description = strlen($parameters["description"]) >= 60 ? substr($parameters["description"],0,80)."..." : $parameters["description"];

            $tbl->addRow(array(
                $color->convert("%g".$task."%n"),
                $description
            ));

        }

        return $tbl->getTable();

    }

    static private function checks_extensive($color, $tasks) {

        $return = '';

        foreach ($tasks as $task => $parameters) {

            $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

            $tbl->addRow(array("Name",$color->convert("%g".$task."%n")));

            $tbl->addRow(array("Class",$parameters["class"]));

            $tbl->addSeparator();

            $tbl->addRow(array("Description", empty($parameters["description"]) ? "No description available" : $parameters["description"] ));

            $return .= $tbl->getTable()."\n";

        }

        return $return;

    }

}
