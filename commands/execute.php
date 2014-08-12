<?php namespace Comodojo\Extender\Shell\Commands;

use \Comodojo\Exception\ShellException;
use \Console_Table;

class execute implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = array();

	public function setOptions($options) {

		$this->options = $options;

		return $this;

	}

	public function setArgs($args) {

		$this->args = $args;

		return $this;

	}

	public function setColor($color) {

		$this->color = $color;

		return $this;

	}

	public function setTasks($tasks) {

		$this->tasks = $tasks;

		return $this;

	}

	public function getOption($option) {

		if ( array_key_exists($option, $this->options) ) return $this->options[$option];

		else return null;

	}

	public function getArg($arg) {

		if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

		else return null;

	}

	public function exec() {

		$task = $this->getArg('task');

		if ( array_key_exists($task, $this->tasks) == false ) throw new ShellException("Task is not registered");

		if ( file_exists($this->tasks[$task]['target']) === false ) throw new ShellException("Task file does not exists");

		if ( (include($this->tasks[$task]['target'])) === false ) throw new ShellException("Task file cannot be included");

		print "\nExecuting task ".$task."...\n"; 

		$run_result = $this->runTask($task);

		$pid = $run_result[0];

		$success = $run_result[2];

		$start_timestamp = $run_result[3];

		$end_timestamp = $run_result[4];

		$result = strlen($run_result[5]) >= 80 ? substr($run_result[5],0,80)."..." : $run_result[5];

		$tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

		$tbl->setHeaders(array(
			'Pid',
			'Success',
			'Result (truncated)',
			'Time elapsed'
		));

		$tbl->addRow(array(
			$pid,
			$this->color->convert($success ? "%gYES%n" : "%rNO%n"),
			$this->color->convert($success ? "%g".$result."%n" : "%r".$result."%n"),
			$success ? ($end_timestamp-$start_timestamp) : "--"
		));

		return $tbl->getTable();

	}

	private function runTask($task) {

		$start_timestamp = microtime(true);

		$name = 'ECONTROL';

		$id = 0;

		$parameters = array();

		$task = $task;

		$class = $this->tasks[$task]['class'];

		$task_class = "\\Comodojo\\Extender\\Task\\".$class;

		try {

			// create a task instance

			$thetask = new $task_class($parameters, null, $name, $start_timestamp, false);

			// get the task pid (we are in singlethread mode)

			$pid = $thetask->getPid();

			// run task

			$result = $thetask->start();
		
		}
		catch (Exception $e) {
		
			return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id);
		
		}

		return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id);

	}

}










