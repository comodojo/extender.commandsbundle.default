<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\TasksTable;
use \Comodojo\Extender\Log\EcontrolLogger;
use \Exception;

class Tasks {

    public static function show() {

        return TasksTable::load(EcontrolLogger::create(false))->getTasks(true);

    }

}