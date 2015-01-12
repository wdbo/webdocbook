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

use \Library\Helper\Directory as DirectoryHelper;

/**
 * Class Locator
 */
class Locator
{

    /**
     * @param $route
     * @return array|null
     */
    public function findController($route)
    {
        $cfg        = FrontController::getInstance()->getConfig('app', array());
        $def_ctrl   = isset($cfg['default_controller']) ? $cfg['default_controller'] : 'default';
        $def_act    = isset($cfg['default_action']) ? $cfg['default_action'] : 'default';
        $routes     = FrontController::getInstance()->getConfig('routes', array());

        $ctrl = $action = null;
        if (array_key_exists($route, $routes)) {
            $route_info = $routes[$route];
            if (false===strpos($route_info, ':')) {
                $ctrl   = $def_ctrl;
                $action = str_replace('Action', '', $route_info).'Action';
            } else {
                list($ctrl, $action) = explode(':', $route_info);
                $action = str_replace('Action', '', $action).'Action';
            }
        }

        if (!empty($ctrl)) {
            $_cls = 'DocBook\\Controller\\'.ucfirst($ctrl).'Controller';
            if (class_exists($_cls)) {
                return array(
                    'controller_classname' => $_cls,
                    'action' => $action
                );
            }
        }
        return null;
    }

    /**
     * @param $path
     * @return null|string
     */
    public function locateDocument($path)
    {
        if (file_exists($path)) {
            return $path;
        }
        $file_path = DirectoryHelper::slashDirname(Kernel::getPath('web'))
            .trim($path, '/');
        if (file_exists($file_path)) {
            return $file_path;
        }
        return null;
    }

    /**
     * @param bool $local
     * @return string
     */
    public function getUserConfigPath($local = false)
    {
        $docbook        = FrontController::getInstance();
        $user_path      = Kernel::getPath('user');
        $config_file    = DirectoryHelper::slashDirname($user_path)
            .$docbook->getConfig('app:user_config_file', 'docbook.config');
        if ($local) {
            $config_file = str_replace(
                DirectoryHelper::slashDirname(Kernel::getPath('app_base_path'))
                , '', $config_file);
        }
        return $config_file;
    }

}

// Endfile
