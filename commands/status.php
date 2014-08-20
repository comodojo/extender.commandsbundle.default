<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;
use \Console_Table;

class jobs implements CommandInterface {

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

			$status = self::readStatus($statusfile);

			self::displayStatus($pid, $status);

		} catch (ShellException $se) {
			
			throw $se;

		}

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

	static private function displayStatus($pid, $status) {
		
		$return = "\n *** Extender Status Resume *** \n";
		$return .= "  ------------------------------ \n\n";

		$return .= " - Process PID: ".$this->color->convert("%g".$pid."%n")."\n";
		$return .= " - Process running since: ".$this->color->convert("%g".date("c", (int)$status["STARTED"])."%n")."\n";
		$return .= " - Process runtime (sec): ".$this->color->convert("%g".$status["TIME"]."%n")."\n\n";

		$return .= " - Completed jobs: ".$this->color->convert("%g".$status["COMPLETED"]."%n")."\n";
		$return .= " - Failed jobs: ".$this->color->convert("%r".$status["FAILED"]."%n")."\n\n";

		$return .= " - Current CPI load (avg): ".$this->color->convert("%g".implode(", ", $status["CPUAVG"])."%n")."\n";
		$return .= " - Allocated memory (real): ".$this->color->convert("%g".self::convert($status["MEM"])."%n")."\n";
		$return .= " - Allocated memory (peak): ".$this->color->convert("%g".self::convert($status["MEMPEAK"])."%n")."\n\n";

		$return .= " - User: ".$this->color->convert("%g".$status["USER"]."%n")."\n";
		$return .= " - Niceness: ".$this->color->convert("%g".$status["NICENESS"]."%n")."\n\n";

		return $return;

	}

	static private function convert($size) {

		$unit = array('b','kb','mb','gb','tb','pb');

		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

	}

}
