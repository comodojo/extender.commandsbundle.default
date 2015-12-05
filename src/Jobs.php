<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Extender\Scheduler\Scheduler;
use \Exception;

/**
 * @package     Comodojo extender commands
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
