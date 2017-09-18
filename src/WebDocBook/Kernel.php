<?php
/**
 * This file is part of the WebDocBook package.
 *
 * Copyleft (ↄ) 2008-2017 Pierre Cassat <me@picas.fr> and contributors
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook;

use \WebDocBook\Filesystem\Helper as FilesystemHelper;

/**
 * Class Kernel
 *
 * This is the central kernel of WebDocBook, fully static.
 *
 * It only throws "classic" `\Exception` as it is used by Composer's
 * commands scripts (when the namespace is not yet loaded).
 *
 * The `$_defaults` table defines default required paths and variables.
 */
class Kernel
{

    /**
     * Name of the constant to set a base directory of WebDocBook
     */
    const BASEDIR_CONSTNAME     = 'WEBDOCBOOK_BASEDIR';

    /**
     * @var bool Flag to initiate the Kernel object at each run
     */
    private static $_booted     = false;

    /**
     * @var array Table of defaults paths and variables (not over-writable)
     */
    protected static $_defaults = array(
        'php' => array(
            'default_controller'    => 'default',
            'default_action'        => 'index'
        ),
        'package_paths' => array(
            'src_dir'               => 'src',
            'resources_dir'         => 'src/WebDocBook/Resources',
        ),
        'paths' => array(
            'user_dir'              => 'user',
            'var_dir'               => 'var',
            'web_dir'               => 'www',
        ),
        'dirnames' => array(
            'config_dir'            => 'config',
            'templates_dir'         => 'templates',
            'cache_dir'             => 'cache',
            'log_dir'               => 'log',
            'i18n_dir'              => 'i18n',
            'vendor_dir'            => 'vendor',
        ),
        'filenames' => array(
            'app_manifest'          => 'composer.json',
            'app_i18n'              => 'webdocbook_i18n.dist.csv',
            'app_config'            => 'webdocbook.dist.ini',
            'user_config'           => 'user_config.ini'
        )
    );

    /**
     * @var array Table of all configuration values
     */
    protected static $_registry = array();

// ----------------------------
// use this for hard debug: Kernel::debug()
// ----------------------------

    /**
     * @var array
     */
    public $debug_vars = array();

    /**
     * Hard debug: dump of an object (the Kernel itself by default)
     * @param $what
     */
    public static function debug($what = null)
    {
        if (self::isDevMode()) {
            @ini_set('html_errors', 0);
            if (is_null($what)) {
                $what = new Kernel;
            }
            $name = is_object($what) ? get_class($what) : gettype($what);
            $dump = print_r($what, true);
            if (strpos($dump, '*RECURSION*') !== false) {
                ob_start();
                var_dump($what);
                $dump = ob_get_clean();
            } else {
                eval('$dump = ' . var_export($what, true) . ';');
                $dump = var_export($dump, true);
            }
            header('Content-Type: text/plain');
            echo $dump . PHP_EOL;
            exit("-- $name DEBUG --");
        }
    }

    /**
     * Magic method used while running `var_export(Kernel)`
     *
     * @param array $vars
     * @return Kernel
     */
    public static function __set_state(array $vars)
    {
        $a = new self;
        $a->debug_vars = self::$_registry;
        return $a;
    }

// ----------------------------
// Booting system
// ----------------------------

    /**
     * @return void
     */
    public static function init()
    {
        self::$_registry = array();
    }

    /**
     * @param bool $soft_boot Set to `true` to not load configuration (during Composer installation)
     * @return void
     * @throws \Exception
     */
    public static function boot($soft_boot = false)
    {
        if (self::$_booted) {
            return;
        }

        // initialize object
        self::init();

        try {

            // installation base path
            self::$_registry['package_base_path'] = FilesystemHelper::slashDirname(dirname(dirname(__DIR__)));
            if (defined(self::BASEDIR_CONSTNAME)) {
                $basedir = constant(self::BASEDIR_CONSTNAME);
                if (true===@file_exists($basedir)) {
                    self::$_registry['app_base_path'] = FilesystemHelper::slashDirname($basedir);
                } else {
                    throw new \Exception(
                        sprintf('Base directory "%s" not found!', $basedir)
                    );
                }
            } else {
                self::$_registry['app_base_path'] = FilesystemHelper::slashDirname(dirname(dirname(__DIR__)));
            }

            // 1st level package paths
            foreach (self::$_defaults['package_paths'] as $name=>$path) {
                $path_name = str_replace('_dir', '_path', $name);
                self::$_registry[$path_name] = self::$_registry['package_base_path'].FilesystemHelper::slashDirname($path);
            }

            // 1st level paths
            foreach (self::$_defaults['paths'] as $name=>$path) {
                $path_name = str_replace('_dir', '_path', $name);
                self::$_registry[$path_name] = self::$_registry['app_base_path'].FilesystemHelper::slashDirname($path);
            }

            // var must exist and be writable
            self::ensurePathExists('var');
            self::ensurePathIsWritable('var');

            // user must exist and be writable
            self::ensurePathExists('user');
            self::ensurePathIsWritable('user');

            // src/config/
            self::$_registry['config_path'] = self::$_registry['resources_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['config_dir']);

            // src/templates/
            self::$_registry['templates_path'] = self::$_registry['src_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['templates_dir']);

            // user/config/
            self::$_registry['user_config_path'] = self::$_registry['user_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['config_dir']);
            self::ensurePathExists('user_config');
            self::ensurePathIsWritable('user_config');

            // user/templates/
            $user_templates_dir = self::$_registry['user_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['templates_dir']);
            if (false!==@file_exists($user_templates_dir)) {
                self::$_registry['user_templates_path'] = $user_templates_dir;
            }

            // var/cache/
            self::$_registry['cache_path'] = self::$_registry['var_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['cache_dir']);
            self::ensurePathExists('cache');
            self::ensurePathIsWritable('cache');

            // var/i18n/
            self::$_registry['i18n_path'] = self::$_registry['var_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['i18n_dir']);
            self::ensurePathExists('i18n');
            self::ensurePathIsWritable('i18n');

            // var/log/
            self::$_registry['log_path'] = self::$_registry['var_path']
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['log_dir']);
            self::ensurePathExists('log');
            self::ensurePathIsWritable('log');

            // app manifest
            self::$_registry['app_manifest_filepath'] = self::$_registry['package_base_path']
                .self::$_defaults['filenames']['app_manifest'];

            // config
            self::$_registry['app_config_filepath'] = self::getPath('user_config')
                .str_replace('.dist', '', self::$_defaults['filenames']['app_config']);

            // i18n
            self::$_registry['app_i18n_filepath'] = self::getPath('user_config')
                .str_replace('.dist', '', self::$_defaults['filenames']['app_i18n']);

            // user_config
            self::$_registry['user_config_filepath'] = self::getPath('user_config')
                .self::$_defaults['filenames']['user_config'];

            // stops here for soft boot
            if ($soft_boot) {
                return;
            }

            // configuration file
            $config = self::parseIniFile(self::getPath('app_config_filepath'));
            self::$_registry['webdocbook'] = $config;

            // web interface
            self::$_registry['app_interface_path'] = self::getPath('web')
                .(isset($config['app']['app_interface']) ? $config['app']['app_interface'] : 'index.php');

            // web webdocbook assets
            self::$_registry['webdocbook_assets_path'] = self::getPath('web')
                .FilesystemHelper::slashDirname(
                    isset($config['app']['internal_assets_dir']) ? $config['app']['internal_assets_dir'] : 'webdocbook_assets'
                );

            // web vendor assets
            self::$_registry['vendor_assets_path'] = self::getPath('webdocbook_assets')
                .FilesystemHelper::slashDirname(self::$_defaults['dirnames']['vendor_dir']);

        } catch (\Exception $e) {
            throw $e;
        }

        // hard debug if so
        if (!empty($_GET) && isset($_GET['harddebug'])) {
            Kernel::debug();
        }
    }

// ----------------------------
// Kernel API
// ----------------------------

    /**
     * @return bool
     * @api
     */
    public static function isDevMode()
    {
        return (defined('WEBDOCBOOK_MODE') && WEBDOCBOOK_MODE==='dev');
    }

    /**
     * Get the value of a specific option with depth
     *
     * @param   string  $name       The index of the configuration value to get, with a scope using notation `index:name`
     * @param   mixed   $default    The default value to return if so (`null` by default)
     * @param   string  $scope      The scope to use in the configuration registry if it is not defined in the `$name` parameter
     * @return  mixed   The value retrieved in the registry or the default value otherwise
     * @api
     */
    public static function getConfig($name = null, $default = null, $scope = 'webdocbook')
    {
        $stack = self::get($scope);
        if (is_null($name)) {
            return $stack;
        } else {
            if (strpos($name, ':')) {
                list($entry, $name) = explode(':', $name);
                $cfg = self::getConfig($entry, array(), $scope);
                return isset($cfg[$name]) ? $cfg[$name] : $default;
            } else {
                return isset($stack[$name]) ? $stack[$name] : $default;
            }
        }
    }

    /**
     * Set an array of options
     *
     * @param   mixed   $value    The array of values to set for the configuration entry
     * @param   string  $scope    The scope to use in the configuration registry
     * @return  void
     * @api
     */
    public static function setConfig($value, $scope)
    {
        self::$_registry[$scope] = $value;
    }

    /**
     * @param $name
     * @return mixed
     * @api
     */
    public static function get($name)
    {
        return (array_key_exists($name, self::$_registry) ? self::$_registry[$name] : null);

    }

    /**
     * @param   string  $name
     * @param   bool    $local
     * @param   string  $base_path
     * @return  string
     * @throws  \Exception
     * @api
     */
    public static function getPath($name, $local = false, $base_path = 'app_base_path')
    {
        // absolute path first
        $_name = str_replace(array('_dir', '_path'), '', $name) . '_path';
        if (array_key_exists($_name, self::$_registry)) {
            if ($local) {
                return str_replace(self::getPath($base_path), '', self::$_registry[$_name]);
            } else {
                return self::$_registry[$_name];
            }

        // else relative one
        } elseif (array_key_exists($name, self::$_registry)) {
            if ($local) {
                return str_replace(self::getPath($base_path), '', self::$_registry[$name]);
            } else {
                return self::$_registry[$name];
            }

        // else error
        } else {
            throw new \Exception(
                sprintf('Unknown WebDocBook path "%s"!', $name)
            );
        }
    }

    /**
     * @param $path
     * @return null|string
     * @api
     */
    public static function findDocument($path)
    {
        if (true===@file_exists($path)) {
            return $path;
        }
        $file_path = self::getPath('web') . trim($path, '/');
        if (true===@file_exists($file_path)) {
            return $file_path;
        }
        return null;
    }

    /**
     * @param $route
     * @return array|null
     * @api
     */
    public static function findController($route)
    {
        $ctrl       = null;
        $action     = null;
        $def_ctrl   = self::$_defaults['php']['default_controller'];
        $def_act    = self::$_defaults['php']['default_action'];
        $routes     = self::getConfig('routes');

        if (array_key_exists($route, $routes)) {
            $route_info = $routes[$route];
            if (false === strpos($route_info, ':')) {
                $ctrl           = $def_ctrl;
                $action_name    = $route_info;
            } else {
                list($ctrl, $action_name) = explode(':', $route_info);
            }
        }
        $action = str_replace('Action', '', (isset($action_name) ? $action_name : $def_act)).'Action';

        if (!empty($ctrl)) {
            $_cls = 'WebDocBook\\Controller\\'.ucfirst($ctrl).'Controller';
            if (class_exists($_cls)) {
                return array(
                    'controller_classname' => $_cls,
                    'action' => $action
                );
            } elseif (class_exists($ctrl)) {
                return array(
                    'controller_classname' => $ctrl,
                    'action' => $action
                );
            }
        }
        return null;
    }

    /**
     * @param   $filename
     * @return  string
     * @api
     */
    public static function findTemplate($filename)
    {
        return self::fallbackFinder($filename, 'templates');
    }

    /**
     * @param $filename
     * @param string $filetype
     * @return null|string
     * @api
     */
    public static function fallbackFinder($filename, $filetype)
    {
        // user first
        $filetype_name      = 'user_'.$filetype;
        $user_path          = self::getPath(str_replace('_dir', '_path', $filetype_name));
        $user_file_path     = $user_path.$filename;
        if (true===@file_exists($user_file_path)) {
            return $user_file_path;
        }

        // default
        $filetype_default   = 'src_'.$filetype;
        $default_path       = self::getPath(str_replace('_dir', '_path', $filetype_default));
        $def_file_path      = $default_path.$filename;
        if (true===@file_exists($def_file_path)) {
            return $def_file_path;
        }

        return null;
    }

// ----------------------------
// Kernel actions
// ----------------------------

    /**
     * Install WebDocBook's config files
     * @return bool
     * @throws \Exception
     * @api
     */
    public static function installConfig()
    {
        $conf_tgt = self::get('app_config_filepath');
        $i18n_tgt = self::get('app_i18n_filepath');

        if (false===@file_exists($conf_tgt)) {
            $conf_src = self::getPath('config') . self::$_defaults['filenames']['app_config'];
            if (false===@copy($conf_src, $conf_tgt)) {
                throw new \Exception(
                    sprintf('Can not copy distributed configuration file to "%s"!', self::getPath('app_config', true))
                );
            }
        }

        if (false===@file_exists($i18n_tgt)) {
            $i18n_src = self::getPath('config') . self::$_defaults['filenames']['app_i18n'];
            if (false===@copy($i18n_src, $i18n_tgt)) {
                throw new \Exception(
                    sprintf('Can not copy distributed language file to "%s"!', self::getPath('app_i18n', true))
                );
            }
        }

        return true;
    }

    /**
     * Clear WebDocBook's cache (var/cache/)
     * @return bool
     * @api
     */
    public static function clearCache()
    {
        return self::remove(self::getPath('cache'));
    }

    /**
     * Clear WebDocBook's language files (var/i18n/)
     * @return bool
     * @api
     */
    public static function clearI18n()
    {
        return self::remove(self::getPath('i18n'));
    }

// ----------------------------
// Utilities
// ----------------------------

    /**
     * @param $path_name
     * @return bool
     * @throws \Exception
     */
    public static function ensurePathExists($path_name)
    {
        if (true!==FilesystemHelper::ensureExists(self::getPath($path_name))) {
            throw new \Exception(
                sprintf('Directory "%s" can not be created!', self::getPath($path_name, true))
            );
        }
        return true;
    }

    /**
     * @param $path_name
     * @return bool
     * @throws \Exception
     */
    public static function ensurePathIsWritable($path_name)
    {
        if (true!==FilesystemHelper::ensureIsWritable(self::getPath($path_name))) {
            throw new \Exception(
                sprintf('Directory "%s" must be writable for your web-server\'s user!', self::getPath($path_name, true))
            );
        }
        return true;
    }

    /**
     * @param $file
     * @return array
     * @throws \Exception
     */
    public static function parseIniFile($file)
    {
        if (true===@file_exists($file)) {
            $config =  FilesystemHelper::parseIni($file);
            if ($config) {
                return $config;
            } else {
                throw new \Exception(
                    sprintf('Configuration file "%s" seems malformed!', str_replace(self::getPath('app_base'), '', $file))
                );
            }
        } else {
            throw new \Exception(
                sprintf('Configuration file "%s" not found!', str_replace(self::getPath('app_base'), '', $file))
            );
        }
    }

    /**
     * @param string $path
     * @return bool
     * @throws \Exception
     */
    public static function remove($path)
    {
        if (true!==FilesystemHelper::remove($path, false)) {
            throw new \Exception(
                sprintf('Can not remove path "%s"!', $path)
            );
        }
        return true;
    }

}

// Endfile
