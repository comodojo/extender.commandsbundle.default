<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\TasksTable;
use \Comodojo\Extender\Log\EcontrolLogger;
use \Exception;

class Execute {

    public static function runTask($task, $parameters, $logger) {

        $tasks = self::getTasks();

        $logger->debug("Checking if task is registered");

        if ( $tasks->isRegistered($task) == false ) throw new Exception("Task is not registered");

        $logger->debug("Checking if class is loadable");

        $class = $tasks->getClass($task);

        if ( class_exists($class) === false ) throw new Exception("Task cannot be loaded");

        $start_timestamp = microtime(true);

        $name = 'ECONTROL';

        $id = 0;

        try {

            // create a task instance

            $logger->info("Creating the task");

            $thetask = new $class($parameters, $logger, null, $name, $start_timestamp, false);

            // get the task pid (we are in singlethread mode)

            $pid = $thetask->getPid();

            $logger->info("Task's PID: ".$pid);

            // run task

            $logger->info("Running the task");

            $result = $thetask->start();
        
        } catch (Exception $e) {
        
            return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id, null);
        
        }

        return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id, $result["worklogid"]);

    }

    private static function getTasks() {

        return TasksTable::load(EcontrolLogger::create(false));

    }

}