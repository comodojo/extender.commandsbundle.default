<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\System;
use \Comodojo\Exception\RpcException;
use \Exception;

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