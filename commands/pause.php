<?php namespace Comodojo\Extender\Command;

use \Comodojo\Exception\ShellException;

class pause extends StandardCommand implements CommandInterface {

	static private $lockfile = "extender.pid";

	public function execute() {

		if ( \Comodojo\Extender\Checks::signals() === false ) throw new ShellException("This version of PHP does not support pnctl");

		$lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

		try {
			
			$pid = self::pushStopEvent($lockfile);

		} catch (ShellException $se) {
			
			throw $se;

		}

		return $this->color->convert("%yExtender paused%n")."\n";

	}

	static private function pushStopEvent($lockfile) {
	
		$pid = file_get_contents($lockfile);

		if ( $pid === false ) throw new ShellException("Extender not running or not in daemon mode");

		$signal = posix_kill($pid, SIGTSTP);

		if ( $signal === false ) throw new ShellException("Unable to send signal STOP to extender process");

		return $pid;

	}

}
