<?php namespace Comodojo\Extender\CommandSource;

use \Comodojo\Extender\CommandSource\Configuration
use \Comodojo\Exception\RpcException;
use \Exception;

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