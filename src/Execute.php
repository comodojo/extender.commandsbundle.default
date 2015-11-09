<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\TasksTable;
use \Exception;

class Execute {

    public static function runTask($task, $parameters) {

        $tasks = self::getTasks();

        if ( $tasks->isTaskRegistered($task) == false ) throw new Exception("Task is not registered");

        $class = $tasks->getClass($task);

        if ( class_exists($class) === false ) throw new Exception("Task cannot be loaded");

        $start_timestamp = microtime(true);

        $name = 'ECONTROL';

        $id = 0;

        try {

            // create a task instance

            $thetask = new $class($parameters, null, $name, $start_timestamp, false);

            // get the task pid (we are in singlethread mode)

            $pid = $thetask->getPid();

            // run task

            $result = $thetask->start();
        
        } catch (Exception $e) {
        
            return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id, null);
        
        }

        return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id, $result["worklogid"]);

    }

    private static function getTasks() {

        $extender = TasksTable::load();

        return $extender;

    }

}