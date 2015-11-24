<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Jobs as SourceJobs;
use \Console_Color2;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class Jobs extends AbstractCommand {

    public function execute() {

        $action = $this->getArgument("action");

        $name = $this->getArgument("name");

        $expression = $this->getArgument("expression");

        $task = $this->getArgument("task");

        $description = $this->getArgument("description");

        $parameters = $this->getArgument("parameters");

        $extensive = $this->getOption("extensive");

        $enable = $this->getOption("enable");

        try {

            if ( is_null($action) ) {

                $return = $this->show($name, $extensive);

            } else {

                switch ($action) {

                    case 'enable':
                    case 'ena':

                        if ( is_null($name) ) throw new ShellException("Wrong job name");

                        $return = $this->enable($name);

                        break;

                    case 'disable':
                    case 'dis':

                        if ( is_null($name) ) throw new ShellException("Wrong job name");

                        $return = $this->disable($name);

                        break;

                    case 'add':

                        if ( is_null($name) || is_null($expression) || is_null($task) ) {

                            throw new ShellException("Wrong job definition");

                        }

                        $return = $this->add($name, $expression, $task, $description, $parameters, $enable);

                        break;

                    case 'remove':
                    case 'rem':
                    case 'delete':
                    case 'del':

                        if ( is_null($name) ) throw new ShellException("Wrong job name");

                        $return = $this->remove($name);

                        break;

                    case 'show':
                    case 'sh':

                        $return = $this->show($name, $extensive);

                    default:

                        throw new ShellException("Unknown action: ".$action);

                        break;

                }

            }

        } catch (ShellException $se) {
            
            throw $se;

        } catch (Exception $e) {
            
            throw $e;

        }

        return $return;

    }

    private function show($name, $extensive) {

        try {

            $jobs = SourceJobs::show($name);

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        if ( $extensive ) return self::jobs_extensive($this->color, $jobs);

        else return self::jobs_brief($this->color, $jobs);

    }

    static private function jobs_brief($color, $jobs) {

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Expression',
            'Name',
            'Task',
            'Description',
            'Enabled'
        ));

        foreach ($jobs as $job) {

            $description = strlen($job["description"]) >= 60 ? substr($job["description"],0,60)."..." : $job["description"];

            $tbl->addRow(array(
                implode(" ",array($job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"])),
                $job["name"],
                $job["task"],
                $description,
                $color->convert($job["enabled"] ? "%gYES%n" : "%rNO%n"),
            ));

        }

        return $return = "\nAvailable jobs:\n---------------\n\n".$tbl->getTable();

    }

    static private function jobs_extensive($color, $jobs) {

        $return = "\nAvailable jobs:\n---------------\n\n";

        foreach ($jobs as $job) {

            $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

            $tbl->addRow(array("Name",$job["name"]));

            $tbl->addSeparator();

            $tbl->addRow(array("Expression",implode(" ",array($job["min"],$job["hour"],$job["dayofmonth"],$job["month"],$job["dayofweek"],$job["year"]))));

            $tbl->addRow(array("Task",$job["task"]));

            $tbl->addRow(array("Description",$job["description"]));

            $tbl->addRow(array("Enabled",$color->convert($job["enabled"] ? "%gYES%n" : "%rNO%n")));

            $tbl->addRow(array("Lastrun",empty($job["lastrun"]) ? $color->convert("%rNEVER%n") : date("r", (int)$job["lastrun"])));

            $tbl->addRow(array("Firstrun", date("r", (int)$job["firstrun"])));

            $tbl->addSeparator();

            $tbl->addRow(array("Parameters",var_export(unserialize($job["params"]), true)));

            $return .= $tbl->getTable()."\n";

        }

        return $return;

    }

    private function enable($job) {

        $result = SourceJobs::enable($job);

        if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

        return $this->color->convert("\n%gJob activated%n");

    }

    private function disable($job) {

        $result = SourceJobs::disable($job);

        if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

        return $this->color->convert("\n%gJob deactivated%n");

    }

    private function remove($job) {

        $result = SourceJobs::remove($job);

        if ( $result == false ) return $this->color->convert("\n%yJob not found%n");

        return $this->color->convert("\n%gJob deleted%n");

    }

    private function add($name, $expression, $task, $description, $parameters, $enable) {

        $parameters = self::processParameters($this->color, $parameters);

        try {
            
            $result = SourceJobs::add($expression, $name, $task, $description, $parameters, $enable);

        } catch (Exception $e) {
            
            throw $e;

        }

        $id = $result["id"];

        $nextrun = $result["nextrun"];

        $enabled = $result["enabled"];

        if ( $enabled ) return $this->color->convert("\n%gJob added and activated (id: ".$id."); next calculated runtime: ".$nextrun."%n");

        else return $this->color->convert("\n%gJob added but not activated (id: ".$id.").%n");

    }

    static private function processParameters($color, $parameters) {

        $params = array();

        if ( !is_null($parameters) ) {

            $p = explode(",", trim($parameters));

            foreach ($p as $parameter) {
                
                $ps = explode("=", $parameter);

                if ( sizeof($ps) == 2 ) {

                    $value = $ps[1];

                    $params[$ps[0]] = $value;

                }

                else echo $color->convert("\n%ySkipping invalid parameter: ".$parameter."%n");

            }


        }

        return $params;

    }

}
