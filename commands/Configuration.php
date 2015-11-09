<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Configuration as SourceConfiguration;
use \Console_Color2;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class Configuration extends AbstractCommand {

    public function execute() {

        $action = $this->getArgument("action");

        $file = $this->getArgument("file");

        $clean = $this->getOption("clean");

        try {
            
            switch ($action) {

                case 'backup':

                    $return = $this->backup($file);

                    break;

                case 'restore':

                    $return = $this->restore($file, $clean);

                    break;

                default:

                    $return = $this->color->convert("\n%yInvalid action ".$action."%n");

                    break;

            }

        } catch (ShellException $se) {
            
            throw $se;

        } catch (Exception $e) {
            
            throw $e;

        }

        return $return;

    }

    private function backup($file) {

        try {

            $backup = SourceConfiguration::getBackup();

            $jobs = unserialize(base64_decode($backup));

            $data = json_encode($jobs);

            $export = @file_put_contents($file, $data);

            if ( $export === false ) throw new ShellException("Unable to write destination file");
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (ShellException $se) {

            throw $se;

        }

        return count($jobs) .  " jobs exported to " . $file;


    }

    private function restore($file, $clean) {

        $jobs = @file_get_contents($file);

        if ( $jobs === false ) throw new ShellException("Unable to read source file");

        $decoded = @json_decode($jobs, true);

        if ( $decoded === false ) throw new ShellException("Invalid source file");

        $data = base64_encode(serialize($decoded));

        try {

            $count = SourceConfiguration::doRestore($data, $clean);
           
        } catch (DatabaseException $de) {

            throw $de;

        } catch (ShellException $se) {

            throw $se;

        }

        return $count .  " job(s) imported" . ($clean ? ", database cleaned." : ".");

    }

}
