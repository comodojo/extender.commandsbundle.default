<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;

class status implements CommandInterface {

	private $options = null;

	private $args = null;

	private $color = null;

	private $tasks = array();

	static private $lockfile = "extender.pid";

	static private $statusfile = "extender.status";

	public function setOptions($options) {

		$this->options = $options;

		return $this;

	}

	public function setArguments($args) {

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

	public function getArgument($arg) {

		if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

		else return null;

	}

	public function execute() {

		$lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

		$statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

		try {
			
			$pid = self::pushUsrEvent($lockfile);

			sleep(1);

			$status = self::readStatus($statusfile);

			$return = self::displayStatus($pid, $status, $this->color);

		} catch (ShellException $se) {
			
			throw $se;

		}

		return $return;

	}

	static private function pushUsrEvent($lockfile) {
	
		$pid = file_get_contents($lockfile);

		if ( $pid === false ) throw new ShellException("Extender not running or not in daemon mode");

		$signal = posix_kill($pid, SIGUSR1);

		if ( $signal === false ) throw new ShellException("Unable to send signal USR1 to extender process");

		return $pid;

	}

	static private function readStatus($statusfile) {
		
		$status = file_get_contents($statusfile);

		if ( $status === false ) throw new ShellException("Unable to read status file");

		return unserialize($status);

	}

	static private function displayStatus($pid, $status, $color) {
		
		$return = "\n *** Extender Status Resume *** \n";
		$return .= "  ------------------------------ \n\n";

		$return .= " - Process PID: ".$color->convert("%g".$pid."%n")."\n";
		$return .= " - Process running since: ".$color->convert("%g".date("r", (int)$status["STARTED"])."%n")."\n";
		$return .= " - Process runtime (sec): ".$color->convert("%g".(int)$status["TIME"]."%n")."\n\n";

		$return .= " - Completed jobs: ".$color->convert("%g".$status["COMPLETED"]."%n")."\n";
		$return .= " - Failed jobs: ".$color->convert("%r".$status["FAILED"]."%n")."\n\n";

		$return .= " - Current CPI load (avg): ".$color->convert("%g".implode(", ", $status["CPUAVG"])."%n")."\n";
		$return .= " - Allocated memory (real): ".$color->convert("%g".self::convert($status["MEM"])."%n")."\n";
		$return .= " - Allocated memory (peak): ".$color->convert("%g".self::convert($status["MEMPEAK"])."%n")."\n\n";

		$return .= " - User: ".$color->convert("%g".$status["USER"]."%n")."\n";
		$return .= " - Niceness: ".$color->convert("%g".$status["NICENESS"]."%n")."\n\n";

		return $return;

	}

	static private function convert($size) {

		$unit = array('b','kb','mb','gb','tb','pb');

		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

	}

}
