<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Execute;
use \Comodojo\Exception\RpcException;
use \Exception;

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
