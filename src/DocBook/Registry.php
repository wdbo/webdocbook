<?php
/**
 * This file is part of the DocBook package.
 *
 * Copyleft (ↄ) 2008-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/docbook>.
 */

namespace DocBook;

use \Patterns\Commons\ConfigurationRegistry;

/**
 * Class Registry
 *
 * This is just to override the default `$scope` of
 * the `get()` and `set()` methods to `docbook`.
 *
 * @package DocBook
 */
class Registry
    extends ConfigurationRegistry
{

    /**
     * Set the value of a specific option with depth
     *
     * @param   string  $name   The index of the configuration value to get, with a scope using notation `index:name`
     * @param   mixed   $value  The value to set for $name
     * @param   string  $scope  The scope to use in the configuration registry if it is not defined in the `$name` parameter
     * @return  self
     */
    public function set($name, $value, $scope = 'docbook')
    {
        return parent::set($name, $value, $scope);
    }

    /**
     * Get the value of a specific option with depth
     *
     * @param   string  $name       The index of the configuration value to get, with a scope using notation `index:name`
     * @param   mixed   $default    The default value to return if so (`null` by default)
     * @param   string  $scope      The scope to use in the configuration registry if it is not defined in the `$name` parameter
     * @return  mixed   The value retrieved in the registry or the default value otherwise
     */
    public function get($name, $default = null, $scope = 'docbook')
    {
        return parent::get($name, $default, $scope);
    }


}

// Endfile
