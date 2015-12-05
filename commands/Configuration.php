<?php namespace Comodojo\Extender\Command;

use \Comodojo\Extender\CommandSource\Configuration as SourceConfiguration;
use \Console_Color2;
use \Console_Table;
use \Comodojo\Exception\ShellException;
use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * @package     Comodojo extender commands
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

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
