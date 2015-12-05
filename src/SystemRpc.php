<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\System;
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
 
class SystemRpc {

    public static function doCheck($params) {

        return System::doCheck();

    }

    public static function getStatus($params) {

        try {

            $return = System::getStatus();

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

    public static function pause($params) {

        try {

            $return = System::pause();

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

    public static function resume($params) {

        try {

            $return = System::resume();

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

}
