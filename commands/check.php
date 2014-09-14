<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\Checks;

class check extends StandardCommand implements CommandInterface {

	public function execute() {

		$constants = Checks::constants();

		$multithread = Checks::multithread();

		$signals = Checks::signals();

		$database = Checks::database();

		$return = "Extender checks:\n----------------\n\n";

		$return .= "Extender minimum parameters configured: " . $this->color->convert( $constants === true ? "%gPASSED%n" : "%r".$constants."%n" );

		$return .= "\nMultiprocess support available: " . $this->color->convert( $multithread === true ? "%gYES%n" : "%rNO%n" );

		$return .= "\nDaemon support (signaling): " . $this->color->convert( $signals === true ? "%gYES%n" : "%rNO%n" );

		$return .= "\nExtender database available and configured: " . $this->color->convert( $database === true ? "%gYES%n" : "%rNO%n" );

		$return .= "\n\nExtender parameters:\n--------------------\n\n";

		$return .= "Framework path: " . $this->color->convert( "%g".EXTENDER_REAL_PATH."%n");

		$return .= "\nMultiprocess enabled: " . $this->color->convert( "%g".EXTENDER_MULTITHREAD_ENABLED."%n");

		$return .= "\nIdle time (daemon mode): " . $this->color->convert( "%g".EXTENDER_IDLE_TIME."%n");

		$return .= "\nMax result bytes per task: " . $this->color->convert( "%g".EXTENDER_MAX_RESULT_BYTES."%n");

		$return .= "\nMax child runtime: " . $this->color->convert( "%g".EXTENDER_MAX_CHILDS_RUNTIME."%n");

		$return .= "\nParent niceness: " . $this->color->convert( defined('EXTENDER_PARENT_NICENESS') ? "%g".EXTENDER_PARENT_NICENESS."%n" : "%ydefault%n" );

		$return .= "\nChilds niceness: " . $this->color->convert( defined('EXTENDER_CHILD_NICENESS') ? "%g".EXTENDER_CHILD_NICENESS."%n" : "%ydefault%n" );

		return $return;		

	}

}
