<?php namespace Comodojo\Extender\Command;

/**
 * Common superclass for extender default commands
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
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

class StandardCommand {

    protected $options = null;

    protected $args = null;

    protected $color = null;

    protected $tasks = array();

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

}
