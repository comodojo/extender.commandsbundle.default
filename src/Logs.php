<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class Logs {

    public static function filterByWid($filter, $extra) {

        if ( empty($filter) || !is_int($filter) ) throw new Exception("Invalid Worklog ID");

        try{

            $db = self::getDatabase()
                ->where("id","=",$filter)
                ->orderBy("id","DESC");

            if ( is_null($extra) ) $data = $db->get();

            else $data = $db->get($extra);

        } catch (Exception $e) {

            throw $e;

        }

        return $data->getData();

    }

    public static function filterByJid($filter, $extra) {

        if ( empty($filter) || !is_int($filter) ) throw new Exception("Invalid Job ID");

        if ( !is_null($extra) && !is_int($extra) ) throw new Exception("Invalid number of rows");

        try{

            $db = self::getDatabase()
                ->where("jobid","=",$filter)
                ->orderBy("id","DESC");

            if ( is_null($extra) ) $data = $db->get();

            else $data = $db->get($extra);

        } catch (Exception $e) {

            throw $e;

        }

        return $data->getData();

    }

    public static function filterByTime($filter, $extra) {

        if ( empty($filter) || !self::validateTimestamp($filter) ) throw new Exception("Invalid start time reference");

        if ( !empty($extra) && !self::validateTimestamp($extra) ) throw new Exception("Invalid end time reference");

        try{

            $db = self::getDatabase()->orderBy("id","DESC");

            if ( empty($extra) ) {

                $end = mktime(23, 59, 59, date("m", $filter), date("d", $filter), date("Y", $filter));

                $db->where('start', 'BETWEEN', array($filter,$end));

            } else {

                $db->where('start', 'BETWEEN', array($filter,$extra));

            }

            $data = $db->get();

        } catch (Exception $e) {

            throw $e;

        }

        return $data->getData();

    }

    public static function filterByLimit($filter, $extra) {

        if ( empty($filter) || !is_int($filter) ) throw new Exception("Invalid number of rows");

        if ( !empty($extra) && !is_int($extra) ) throw new Exception("Invalid offset");

        try{

            $db = self::getDatabase()
                ->orderBy("id","DESC");

            if ( is_null($extra) ) $data = $db->get($filter);

            else $data = $db->get($filter, $extra);

        } catch (Exception $e) {

            throw $e;

        }

        return $data->getData();

    }

    public static function show() {

        try{

            $data = self::getDatabase()
                ->orderBy("id","DESC")
                ->get(10, 0);

        } catch (Exception $e) {

            throw $e;

        }

        return $data->getData();

    }


    private static function getDatabase() {

        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
                ->keys(array("id","pid","jobid","name","task","status","success","result","start","end"));

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $db;

    }


    /**
     * Checks if a string is a valid timestamp.
     *
     * @param  string $timestamp Timestamp to validate.
     *
     * @return bool
     */
    private static function validateTimestamp($timestamp) {

        $check = (is_int($timestamp) || is_float($timestamp))
            ? $timestamp
            : (string) (int) $timestamp;

        return  ($check === $timestamp)
            AND ( (int) $timestamp <=  PHP_INT_MAX)
            AND ( (int) $timestamp >= ~PHP_INT_MAX);
    }

}
