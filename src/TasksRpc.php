<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Tasks;
use \Comodojo\Exception\RpcException;
use \Exception;

class TasksRpc {

    public static function show() {

        try {
            
            $return = Tasks::show();

        } catch (Exception $e) {
            
            throw new RpcException($e->getMessage(), -31001);

        }

        return $return;

    }

}