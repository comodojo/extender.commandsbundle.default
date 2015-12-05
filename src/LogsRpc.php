<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Logs;
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

class LogsRpc {

    public static function filterByWid($params) {

        $wid = $params->get('wid');

        try{

            $data = Logs::filterByWid($wid);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $data;

    }

    public static function filterByJid($params) {

        $jid = $params->get('jid');

        $rows = $params->get('rows');

        try{

            $data = Logs::filterByJid($jid, $rows);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $data;

    }

    public static function filterByTime($params) {

        $start = $params->get('start');

        $end = $params->get('end');

        try{

            $data = Logs::filterByTime($start, $end);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $data;

    }

    public static function filterByLimit($params) {

        $limit = $params->get('limit');

        $offset = $params->get('offset');

        try{

            $data = Logs::filterByLimit($limit, $offset);

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $data;

    }

    public static function show() {

        try{

            $data = Logs::show();

        } catch (Exception $e) {

            throw new RpcException($e->getMessage(), -31001);

        }

        return $data;

    }

}
