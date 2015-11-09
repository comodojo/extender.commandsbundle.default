<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Extender\Scheduler\Scheduler;
use \Exception;

class Configuration {

    public static function getBackup() {
        
        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys(array("id","name","task","description",
                    "min","hour","dayofmonth","month","dayofweek","year",
                    "params","enabled"))
                ->get();

        } catch (DatabaseException $de) {

            unset($db);

            throw $de;

        } catch (Exception $e) {

            unset($db);

            throw $e;

        }
        
        unset($db);

        $data = $result->getData();

        return base64_encode(serialize($data));
        
    }

    public static function doRestore($data, $clean=false) {
        
        $jobs = unserialize(base64_decode($data));

        if ( $jobs === false ) throw new Exception("Unable to parse data");

        try {

            if ( $clean ) self::truncate();

            $count = self::uploadJobs($jobs);
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }
        
        return $count;
        
    }

    private static function truncate() {
        
        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->truncate();

        }
        catch (DatabaseException $de) {

            unset($db);

            throw $de;

        }
        
        unset($db);

    }

    private static function uploadJobs($jobs) {
        
        $imported = 0;

        foreach ($jobs as $job) {
            
            $expression = $job["min"]." ".$job["hour"]." ".$job["dayofmonth"]." ".$job["month"]." ".$job["dayofweek"]." ".$job["year"];

            try {

                $parameters = unserialize($job["params"]);
            
                list($id, $next_calculated_run) = Scheduler::addSchedule($expression, $job["name"], $job["task"], $job["description"], $parameters);

                if ( $job["enabled"] ) Scheduler::enableSchedule($job["name"]);

            } catch (Exception $e) {
                
                throw $e;

            }

            $imported++;

        }

        return $imported;
        
    }

}