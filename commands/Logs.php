<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Logs as SourceLogs;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Exception;

class Logs extends AbstractCommand {

    public function execute() {

        $action = $this->getArgument("action");

        $filter = $this->getArgument("filter");

        $extra = $this->getArgument("extra");

        $extensive = $this->getOption("extensive");

        try {

            if ( is_null($action) ) {

                $return = $this->show($extensive);

            } else {

                switch ($action) {

                    case 'wid':
                    case 'id':

                        $return = $this->byWid($filter, $extra, $extensive);

                        break;

                    case 'jid':
                    case 'job':

                        $return = $this->byJid($filter, $extra, $extensive);

                        break;

                    case 'time':

                        $return = $this->byTime($filter, $extra, $extensive);

                        break;

                    case 'limit':

                        $return = $this->byLimit($filter, $extra, $extensive);

                        break;

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

    private function byWid($filter, $extra, $extensive) {

        try {

            $data = SourceLogs::filterByWid(intval($filter), intval($extra));

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $this->doShow($data, $extensive);

    }

    private function byJid($filter, $extra, $extensive) {

        try {

            $data = SourceLogs::filterByJid(intval($filter), intval($extra));

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $this->doShow($data, $extensive);

    }

    private function byTime($filter, $extra, $extensive) {

        $start = strtotime($filter);

        $end = strtotime($extra);

        if ( $start === false || ( !empty($extra) && $end === false ) ) throw new Exception("Invalid date filter");

        try {

            $data = SourceLogs::filterByTime($start, $end);

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $this->doShow($data, $extensive);

    }

    private function byLimit($filter, $extra, $extensive) {

        try {

            $data = SourceLogs::filterByLimit(intval($filter), intval($extra));

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $this->doShow($data, $extensive);

    }

    private function show($extensive) {

        try {

            $data = SourceLogs::show();

        } catch (DatabaseException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $this->doShow($data, $extensive);

    }

    private function doShow($data, $extensive) {

        $message = "Found ".$this->color->convert("%g".count($data)."%n")." worklog(s):\n--------------------\n\n";

        if ( $extensive ) return $message.self::showExtensive($this->color, $data);

        else return $message.self::showBrief($this->color, $data);

    }

    private static function showBrief($color, $data) {

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            "ID",
            "JID",
            "Status",
            "Name",
            "Start",
            "End",
            "S"
        ));

        foreach ($data as $worklog) {

            $result = strlen($worklog["result"]) >= 60 ? substr($worklog["result"],0,60)."..." : $worklog["result"];

            $start = date("r", (int)$worklog["start"]);

            $end = empty($worklog["end"]) ? "-" : date("r", (int)$worklog["end"]);

            $status = self::filterStatus($color, $worklog["status"]);

            $success = $color->convert( $worklog["success"] == true ? "%gV%n" : "%rX%n" );

            $id = $color->convert("%g".$worklog["id"]."%n");

            $jid = $color->convert("%y".(empty($worklog["jobid"]) ? "-" : $worklog["jobid"])."%n");

            $name = $color->convert("%y".$worklog["name"]."%n");

            $tbl->addRow(array(
                $id,
                $jid,
                $status,
                $name,
                $start,
                $end,
                $success
            ));

        }

        return $tbl->getTable();

    }

    private static function showExtensive($color, $data) {

        $return = "";

        foreach ($data as $worklog) {

            $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

            $result = wordwrap($worklog["result"], 60, "\n", true);

            $start = date("r", (int)$worklog["start"]);

            $end = empty($worklog["end"]) ? "-" : date("r", (int)$worklog["end"]);

            $total_time = empty($worklog["end"]) ? "-" : ((int)$worklog["end"] - (int)$worklog["start"]);

            $status = self::filterStatus($color, $worklog["status"]);

            $success = $color->convert( $worklog["success"] == true ? "%gV%n" : "%rX%n" );

            $id = $color->convert("%g".$worklog["id"]."%n");

            $jobid = $color->convert("%g".$worklog["jobid"]."%n");

            $name = $color->convert("%y".$worklog["name"]."%n");

            $pid = $worklog["pid"];

            $task = $color->convert("%y".$worklog["task"]."%n");

            $tbl->addRow(array("Log ID", $id));

            $tbl->addRow(array("Job ID", $jobid));

            $tbl->addRow(array("Name", $name));

            $tbl->addSeparator();

            $tbl->addRow(array("Status", $status));

            $tbl->addRow(array("PID", $pid));

            $tbl->addRow(array("Task", $task));

            $tbl->addSeparator();

            $tbl->addRow(array("Start", $start));

            $tbl->addRow(array("End",$end));

            $tbl->addRow(array("Total time", $total_time));

            $tbl->addRow(array("Success", $success));

            $tbl->addSeparator();

            $tbl->addRow(array("Result", $result));

            $return .= $tbl->getTable()."\n\n";

        }

        return $return;

    }

    private static function filterStatus($color, $status) {

        switch ($status) {

            case 'FINISHED':

                $return = $color->convert("%g".$status."%n");

                break;

            case 'RUNNING':

                $return = $color->convert("%y".$status."%n");

                break;

            case 'ERROR':
            default:

                $return = $color->convert("%r".$status."%n");

                break;

        }

        return $return;

    }

}
