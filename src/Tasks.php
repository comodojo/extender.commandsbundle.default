<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\TasksTable;
use \Exception;

class Tasks {

    public static function show() {

        return TasksTable::load()->getTasks(true);

    }

}