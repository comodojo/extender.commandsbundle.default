<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Logs;
use \Comodojo\Exception\RpcException;
use \Exception;

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