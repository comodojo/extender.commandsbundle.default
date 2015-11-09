<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\Checks;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class System {

    private static $lockfile = "extender.pid";

    private static $statusfile = "extender.status";

    private static $queuefile = "extender.queue";

    public static function doCheck() {

        return array(
            "constants" => Checks::constants(),
            "multithread" => Checks::multithread(),
            "signals" => Checks::signals(),
            "database" => Checks::database(),
            "real_path" => EXTENDER_REAL_PATH,
            "multithread_enabled" => EXTENDER_MULTITHREAD_ENABLED,
            "idle_time" => EXTENDER_IDLE_TIME,
            "max_result_bytes" => EXTENDER_MAX_RESULT_BYTES,
            "max_childs" => EXTENDER_MAX_CHILDS,
            "max_childs_runtime" => EXTENDER_MAX_CHILDS_RUNTIME,
            "parent_niceness" => defined('EXTENDER_PARENT_NICENESS') ? EXTENDER_PARENT_NICENESS : null,
            "child_niceness" => defined('EXTENDER_CHILD_NICENESS') ? EXTENDER_CHILD_NICENESS : null
        );

    }

    public static function getStatus() {

        $lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

        $statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

        $queuefile = EXTENDER_CACHE_FOLDER.self::$queuefile;

        try {
            
            $return = self::readStatus( $lockfile, $statusfile, $queuefile );

        } catch (Exception $e) {
            
            throw new Exception("Unable to read status file (maybe extender stopped?)");

        }

        return $return;

    }

    public static function pause() {

        if ( Checks::signals() === false ) throw new Exception("This version of PHP does not support pnctl");

        try {
            
            $pid = self::pushUsrEvent(SIGTSTP, self::$lockfile);

        } catch (Exception $e) {
         
            throw $e;

        }

        return $pid;

    }

    public static function resume() {

        if ( Checks::signals() === false ) throw new Exception("This version of PHP does not support pnctl");

        try {
            
            $pid = self::pushUsrEvent(SIGCONT, self::$lockfile);

        } catch (Exception $e) {
            
            throw $e;

        }

        return $pid;

    }

    public static function installDatabase() {

        try {

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->autoClean();

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->drop(true);

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_WORKLOGS)->drop(true);

            $jobs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)
               ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
               ->column(Column::create('name','STRING')->length(128)->notNull()->unique())
               ->column(Column::create('task','STRING')->length(128)->notNull())
               ->column(Column::create('description','TEXT')->defaultValue(null))
               ->column(Column::create('enabled','BOOL')->defaultValue(0))
               ->column(Column::create('min','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('hour','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('dayofmonth','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('month','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('dayofweek','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('year','STRING')->length(16)->defaultValue(null))
               ->column(Column::create('params','TEXT')->defaultValue(null))
               ->column(Column::create('lastrun','INTEGER')->length(64)->defaultValue(null))
               ->column(Column::create('firstrun','INTEGER')->length(64)->notNull())
               ->create(EXTENDER_DATABASE_TABLE_JOBS);

            // $db->clean();

            $worklogs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
               ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
               ->column(Column::create('pid','INTEGER')->unsigned()->defaultValue(null))
               ->column(Column::create('jobid','INTEGER')->unsigned()->defaultValue(null))
               ->column(Column::create('name','STRING')->length(128)->notNull())
               ->column(Column::create('task','STRING')->length(128)->notNull())
               ->column(Column::create('status','STRING')->length(12)->notNull())
               ->column(Column::create('success','BOOL')->defaultValue(0))
               ->column(Column::create('result','TEXT')->defaultValue(null))
               ->column(Column::create('start','STRING')->length(64)->notNull())
               ->column(Column::create('end','STRING')->length(64)->defaultValue(null))
               ->create(EXTENDER_DATABASE_TABLE_WORKLOGS);

        } catch (DatabaseException $de) {

            unset($db);

            throw new ShellException("Database error: ".$de->getMessage());
            
        }

        unset($db);            

    }

    public static function emptyDatabase() {

        try {

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->autoClean();

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->truncate();

            // $db->clean();

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_WORKLOGS)->truncate();

        } catch (DatabaseException $de) {

            unset($db);

            throw new ShellException("Database error: ".$de->getMessage());
            
        }

        unset($db);

    }

    public static function checkInstalled() {

        try {

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->keys("id")->get(1);

        } catch (DatabaseException $de) {

            unset($db);

            return false;
            
        }

        unset($db);

        return true;

    }

    static private function readStatus($lockfile, $statusfile, $queuefile) {

        set_error_handler( 

            function($severity, $message, $file, $line) {

                throw new Exception($message);

            }

        );

        try {
        
            $lock = file_get_contents($lockfile);
        
            $status = file_get_contents($statusfile);

            $queue = file_get_contents($queuefile);

        } catch (Exception $se) {
            
            throw $se;

        }

        restore_error_handler();

        if ( $lock === false ) throw new Exception("Unable to read lock file");

        if ( $status === false ) throw new Exception("Unable to read status file");

        if ( $queue === false ) throw new Exception("Unable to read queue file");

        return array(
            $lock,
            unserialize($status),
            unserialize($queue)
        );

    } 

    static private function pushUsrEvent($signal, $lockfile) {
    
        $pid = @file_get_contents($lockfile);

        if ( $pid === false ) throw new Exception("Extender not running or not in daemon mode");

        $signal = posix_kill($pid, $signal);

        if ( $signal === false ) throw new Exception("Unable to send signal ".$signal." to extender process");

        return $pid;

    }

}