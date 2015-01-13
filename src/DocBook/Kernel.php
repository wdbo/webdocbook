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

/**
 * Class Kernel
 *
 * This is the central kernel of DocBook, fully static.
 *
 * It only throws "classic" `\Exception` as it is used by Composer's
 * commands scripts (when the namespace is not yet loaded).
 *
 * The `$_defaults` table defines default required paths and variables.
 */
class Kernel
{

    /**
     * @var bool Flag to initiate the Kernel object at each run
     */
    private static $_booted = false;

    /**
     * @var array Table of defaults paths and variables (not over-writable)
     */
    protected static $_defaults = array(
        'php' => array(
            'default_controller'    => 'default',
            'default_action'        => 'index'
        ),
        'paths' => array(
            'src_dir'               => 'src',
            'resources_dir'         => 'src/DocBook/Resources',
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
            'app_i18n'              => 'docbook_i18n.dist.csv',
            'app_config'            => 'docbook.dist.ini',
            'user_config'           => 'user_config.ini'
        )
    );

    /**
     * @var array Table of all configuration values
     */
    protected static $_registry = array();

//*/
// ----------------------------
// use this for hard debug: Kernel::debug()
// ----------------------------


    public $debug_vars = array();

    public static function debug()
    {
        $a = new Kernel;
        eval('$b = ' . var_export($a, true) . ';');
        header('Content-TYpe: text/plain');
        var_export($b);
        echo PHP_EOL;
        exit('-- Kernel DEBUG --');
    }

    public static function __set_state(array $vars)
    {
        $a = new self;
        $a->debug_vars = self::$_registry;
        return $a;
    }
//*/

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

        // installation base path
        self::$_registry['app_base_path'] = self::slashDirname(dirname(dirname(__DIR__)));

        // 1st level paths
        foreach (self::$_defaults['paths'] as $name=>$path) {
            $path_name = str_replace('_dir', '_path', $name);
            self::$_registry[$path_name] = self::$_registry['app_base_path'].self::slashDirname($path);
        }

        // var must be writable
        if (!is_writable(self::getPath('var'))) {
            throw new \Exception("Directory 'var/' must be writable!");
        }

        // user must be writable
        if (!is_writable(self::getPath('user'))) {
            throw new \Exception("Directory 'user/' must be writable!");
        }

        // src/config/
        self::$_registry['config_path'] = self::$_registry['resources_path']
            .self::slashDirname(self::$_defaults['dirnames']['config_dir']);

        // src/templates/
        self::$_registry['templates_path'] = self::$_registry['src_path']
            .self::slashDirname(self::$_defaults['dirnames']['templates_dir']);

        // user/config/
        self::$_registry['user_config_path'] = self::$_registry['user_path']
            .self::slashDirname(self::$_defaults['dirnames']['config_dir']);
        if (!is_writable(self::getPath('user_config'))) {
            throw new \Exception("Directory 'user/config/' must be writable!");
        }

        // user/templates/
        $user_templates_dir = self::$_registry['user_path']
            .self::slashDirname(self::$_defaults['dirnames']['templates_dir']);
        if (file_exists($user_templates_dir)) {
            self::$_registry['user_templates_path'] = $user_templates_dir;
        }

        // var/cache/
        self::$_registry['cache_path'] = self::$_registry['var_path']
            .self::slashDirname(self::$_defaults['dirnames']['cache_dir']);
        if (!is_writable(self::getPath('cache'))) {
            throw new \Exception("Directory 'var/cache/' must be writable!");
        }

        // var/i18n/
        self::$_registry['i18n_path'] = self::$_registry['var_path']
            .self::slashDirname(self::$_defaults['dirnames']['i18n_dir']);
        if (!is_writable(self::getPath('i18n'))) {
            throw new \Exception("Directory 'var/i18n/' must be writable!");
        }

        // var/log/
        self::$_registry['log_path'] = self::$_registry['var_path']
            .self::slashDirname(self::$_defaults['dirnames']['log_dir']);
        if (!is_writable(self::getPath('log'))) {
            throw new \Exception("Directory 'var/log/' must be writable!");
        }

        // app manifest
        self::$_registry['app_manifest_filepath'] = self::$_registry['app_base_path']
            .self::$_defaults['filenames']['app_manifest'];

        // config
        self::$_registry['app_config_filepath'] = self::getPath('user_config')
            .str_replace('.dist', '', self::$_defaults['filenames']['app_config']);

        // i18n
        self::$_registry['app_i18n_path'] = self::getPath('user_config')
            .str_replace('.dist', '', self::$_defaults['filenames']['app_i18n']);

        // user_config
        self::$_registry['user_config_filepath'] = self::getPath('user_config')
            .self::$_defaults['filenames']['user_config'];

        // stops here for soft boot
        if ($soft_boot) {
            return;
        }

        // configuration file
        try {
            $config = self::parseIniFile(self::getPath('app_config_filepath'));
            self::$_registry['docbook'] = $config;
        } catch (\Exception $e) {
            throw $e;
        }

        // web interface
        self::$_registry['app_interface_path'] = self::getPath('web')
            .(isset($config['app']['app_interface']) ? $config['app']['app_interface'] : 'index.php');

        // web docbook assets
        self::$_registry['docbook_assets_path'] = self::getPath('web')
            .self::slashDirname(
                isset($config['app']['internal_assets_dir']) ? $config['app']['internal_assets_dir'] : 'docbook_assets'
            );

        // web vendor assets
        self::$_registry['vendor_assets_path'] = self::getPath('docbook_assets')
            .self::slashDirname(self::$_defaults['dirnames']['vendor_dir']);

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
        return (defined('DOCBOOK_MODE') && DOCBOOK_MODE==='dev');
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
    public static function getConfig($name = null, $default = null, $scope = 'docbook')
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
                sprintf('Unknown DocBook path "%s"!', $name)
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
        if (file_exists($path)) {
            return $path;
        }
        $file_path = self::getPath('web') . trim($path, '/');
        if (file_exists($file_path)) {
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
        if (file_exists($user_file_path)) {
            return $user_file_path;
        }

        // default
        $filetype_default   = 'src_'.$filetype;
        $default_path       = self::getPath(str_replace('_dir', '_path', $filetype_default));
        $def_file_path      = $default_path.$filename;
        if (file_exists($def_file_path)) {
            return $def_file_path;
        }

        return null;
    }

    /**
     * Install DocBook's config files
     * @return bool
     * @throws \Exception
     * @api
     */
    public static function installConfig()
    {
        $conf_tgt = self::getPath('app_config');
        $i18n_tgt = self::getPath('app_i18n');

        if (!file_exists($conf_tgt)) {
            $conf_src = self::getPath('config') . self::$_defaults['filenames']['app_config'];
            $ok = copy($conf_src, $conf_tgt);
            if (!$ok) {
                throw new \Exception(
                    sprintf('Can not copy distributed configuration file to "%s"!', self::getPath('app_config', true))
                );
            }
        }

        if (!file_exists($i18n_tgt)) {
            $i18n_src = self::getPath('config') . self::$_defaults['filenames']['app_i18n'];
            $ok = copy($i18n_src, $i18n_tgt);
            if (!$ok) {
                throw new \Exception(
                    sprintf('Can not copy distributed configuration file to "%s"!', self::getPath('app_i18n', true))
                );
            }
        }

        return true;
    }

    /**
     * Clear DocBook's cache
     * @return bool
     * @api
     */
    public static function clearCache()
    {
        return self::remove(self::getPath('var'));
    }

// ----------------------------
// Utilities
// ----------------------------

    /**
     * @param string $str
     * @return string
     */
    public static function slashDirname($str)
    {
        $str = rtrim($str, DIRECTORY_SEPARATOR.'/');
        return $str.DIRECTORY_SEPARATOR;
    }

    /**
     * @param $file
     * @return array
     * @throws \Exception
     */
    public static function parseIniFile($file)
    {
        if (file_exists($file)) {
            $config =  parse_ini_file($file, true);
            if ($config) {
                return $config;
            } else {
                throw new \Exception(
                    sprintf('DocBook configuration file "%s" seems malformed!', $file)
                );
            }
        } else {
            throw new \Exception(
                sprintf('DocBook configuration file not found but is required (searching "%s")!', $file)
            );
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function remove($path)
    {
        if (file_exists($path) && is_dir($path)) {
            if (!is_dir($path) || is_link($path)) {
                return unlink($path);
            }
            $ok = true;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::SELF_FIRST | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
            );
            foreach($iterator as $item) {
                if (in_array($item->getFilename(), array('.', '..'))) {
                    continue;
                }
                if ($item->isDir()) {
                    $ok = self::remove($item);
                } else {
                    $ok = unlink($item);
                }
            }
            if ($ok) {
                rmdir($path);
            }
            clearstatcache();
            return true;
        }
        return false;
    }

}

// Endfile
