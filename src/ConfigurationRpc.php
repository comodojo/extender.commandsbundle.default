<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Configuration;
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

class ConfigurationRpc {

    public static function getBackup() {

        try{

            $return = Configuration::getBackup();

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

    public static function doRestore($params) {

        $config = $params->get('config');

        $clean = $params->get('clean');

        try{

            $return = Configuration::doRestore($data, $clean);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

}
