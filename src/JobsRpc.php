<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Jobs;
use \Comodojo\Exception\RpcException;
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
 
class JobsRpc {

    public static function show($params) {

        $name = $params->get('name');

        try{

            $result = Jobs::show($name);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

    public static function enable($params) {

        $name = $params->get('name');

        try{

            $result = Jobs::enable($name);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

    public static function disable($params) {

        $name = $params->get('name');

        try{

            $result = Jobs::disable($name);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

    public static function remove($params) {

        $name = $params->get('name');

        try{

            $result = Jobs::remove($name);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

    public static function add($params) {

        $expression = $params->get('expression');

        $name = $params->get('name');

        $task = $params->get('task');

        $description = $params->get('description');

        $parameters = $params->get('parameters');

        try {

            $result = Jobs::addToScheduler($expression, $name, $task, $description, $parameters);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

}
