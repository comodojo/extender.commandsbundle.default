<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;

class install implements CommandInterface {

    private $options = null;

    private $args = null;

    private $color = null;

    private $tasks = array();

    public function setOptions($options) {

        $this->options = $options;

        return $this;

    }

    public function setArgs($args) {

        $this->args = $args;

        return $this;

    }

    public function setColor($color) {

        $this->color = $color;

        return $this;

    }

    public function setTasks($tasks) {

        $this->tasks = $tasks;

        return $this;

    }

    public function getOption($option) {

        if ( array_key_exists($option, $this->options) ) return $this->options[$option];

        else return null;

    }

    public function getArg($arg) {

        if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

        else return null;

    }

    public function exec() {

        $force = $this->getOption("force");

        $clean = $this->getOption("clean");

        $installed = self::checkInstalled();

        if ( $installed AND $clean ) {

            try {
                
                self::emptyDatabase();

            } catch (ShellException $se) {

                throw $se;

            }

            return $this->color->convert("\n%gExtender database cleaned.%n\n");

        }

        if ( $installed AND is_null($force) ) return $this->color->convert("\n%yExtender already installed, use --force to reinstall.%n\n");

        try {

            self::installDatabase();

        } catch (ShellException $se) {

            throw $se;

        }

        return $this->color->convert("\n%gExtender successfully installed%n\n");

    }

    private static function installDatabase() {

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

            $jobs_column_id          = new Column('id','INTEGER');
            $jobs_column_name        = new Column('name','STRING');
            $jobs_column_task        = new Column('task','STRING');
            $jobs_column_description = new Column('description','TEXT');
            $jobs_column_enabled     = new Column('enabled','BOOL');
            $jobs_column_min         = new Column('min','STRING');
            $jobs_column_hour        = new Column('hour','STRING');
            $jobs_column_dayofmonth  = new Column('dayofmonth','STRING');
            $jobs_column_month       = new Column('month','STRING');
            $jobs_column_dayofweek   = new Column('dayofweek','STRING');
            $jobs_column_year        = new Column('year','STRING');
            $jobs_column_params      = new Column('params','TEXT');
            $jobs_column_lastrun     = new Column('lastrun','INTEGER');

            $jobs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
               ->column($jobs_column_id->unsigned()->autoIncrement()->primaryKey())
               ->column($jobs_column_name->length(64)->notNull()->unique())
               ->column($jobs_column_task->length(64)->notNull())
               ->column($jobs_column_description->defaultValue(null))
               ->column($jobs_column_enabled->defaultValue(0))
               ->column($jobs_column_min->length(16)->defaultValue(null))
               ->column($jobs_column_hour->length(16)->defaultValue(null))
               ->column($jobs_column_dayofmonth->length(16)->defaultValue(null))
               ->column($jobs_column_month->length(16)->defaultValue(null))
               ->column($jobs_column_dayofweek->length(16)->defaultValue(null))
               ->column($jobs_column_year->length(16)->defaultValue(null))
               ->column($jobs_column_params->defaultValue(null))
               ->column($jobs_column_lastrun->length(64)->defaultValue(null))
               ->create(EXTENDER_DATABASE_TABLE_JOBS, true);

            // $db->clean();

            $worklogs_column_id          = new Column('id','INTEGER');
            $worklogs_column_pid         = new Column('pid','INTEGER');
            $worklogs_column_name        = new Column('name','STRING');
            $worklogs_column_task        = new Column('task','STRING');
            $worklogs_column_status      = new Column('status','STRING');
            $worklogs_column_success     = new Column('success','BOOL');
            $worklogs_column_result      = new Column('result','TEXT');
            $worklogs_column_start       = new Column('start','STRING');
            $worklogs_column_end         = new Column('end','STRING');

            $worklogs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
               ->column($worklogs_column_id->unsigned()->autoIncrement()->primaryKey())
               ->column($worklogs_column_pid->unsigned()->defaultValue(null))
               ->column($worklogs_column_name->length(64)->notNull())
               ->column($worklogs_column_task->length(64)->notNull())
               ->column($worklogs_column_status->length(12)->notNull())
               ->column($worklogs_column_success->defaultValue(0))
               ->column($worklogs_column_result->defaultValue(null))
               ->column($worklogs_column_start->length(64)->notNull())
               ->column($worklogs_column_end->length(64)->defaultValue(null))
               ->create(EXTENDER_DATABASE_TABLE_WORKLOGS, true);

        } catch (DatabaseException $de) {

            unset($db);

            throw new ShellException("Database error: ".$de->getMessage());
            
        }

        unset($db);

    }

    private static function emptyDatabase() {

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

    private static function checkInstalled() {

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

}
