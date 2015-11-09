<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Jobs;
use \Comodojo\Exception\RpcException;
use \Exception;

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