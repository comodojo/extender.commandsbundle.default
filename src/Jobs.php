<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Extender\Scheduler\Scheduler;
use \Exception;

class Jobs {

    public static function show($name) {

        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $query = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys(array("id","name","task","description",
                    "min","hour","dayofmonth","month","dayofweek","year",
                    "params","lastrun","firstrun","enabled"))
                ->orderBy("name");

            if ( !is_null($name) ) $query->where("name","=",$name);

            $result = $query->get();

        } catch (DatabaseException $de) {

            unset($db);

            throw $de;

        } catch (Exception $e) {

            unset($db);

            throw $e;

        }
        
        unset($db);

        return $result->getData();

    }

    public static function enable($name) {

        try {
            
            $result = Scheduler::enableSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        return $result;

    }

    public static function disable($name) {

        try {
            
            $result = Scheduler::disableSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        return $result;

    }

    public static function remove($name) {

        try {
            
            $result = Scheduler::removeSchedule($name);

        } catch (Exception $e) {
            
            throw $e;

        }

        return $result;
        
    }

    public static function add($expression, $name, $task, $description, $parameters, $enable) {

        try {
            
            $result = self::addToScheduler($expression, $name, $task, $description, $parameters);

            if ( $enable ) {

                $result["enabled"] = self::enable($name);

            }

        } catch (Exception $e) {
            
            throw $e;

        }

        return $result;

    }

    public static function addToScheduler($expression, $name, $task, $description, $parameters) {

        try {
            
            list($id, $next_calculated_run) = Scheduler::addSchedule($expression, $name, $task, $description, $parameters);

        } catch (Exception $e) {
            
            throw $e;

        }

        return array(
            "id" => $id,
            "nextrun" => $next_calculated_run,
            "enabled" => false
        );

    }

}