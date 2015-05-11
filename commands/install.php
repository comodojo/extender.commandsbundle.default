<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;

/**
 * Install the framework (post composer)
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class install extends StandardCommand implements CommandInterface {

    /**
     * Execute statement (what this command will do)
     *
     * Install the framework (post composer)
     *
     * Command syntax:
     *
     * ./econtrol.php install
     *
     * @return  string
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Comodojo\Exception\ShellException
     */
    public function execute() {

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
            $jobs_column_firstrun    = new Column('firstrun','INTEGER');

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
               ->column($jobs_column_firstrun->length(64)->notNull())
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
