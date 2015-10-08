<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Exception;

/**
 * Install the framework (post composer)
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class install extends AbstractCommand {

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

            $jobs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)
               ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
               ->column(Column::create('name','STRING')->length(64)->notNull()->unique())
               ->column(Column::create('task','STRING')->length(64)->notNull())
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
               ->column(Column::create('name','STRING')->length(64)->notNull())
               ->column(Column::create('task','STRING')->length(64)->notNull())
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
