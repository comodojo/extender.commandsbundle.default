<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Execute;
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
 
class ExecuteRpc {

    public static function runTask($params) {

        $task = $params->get('task');

        $parameters = $params->get('parameters');

        try {

            if ( is_null($parameters) ) {

                $result = Execute::runTask($task, array());

            } else {

                $result = Execute::runTask($task, $parameters);

            }

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $result;

    }

}
