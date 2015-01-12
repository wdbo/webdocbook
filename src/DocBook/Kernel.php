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
 * Class Kernel of DocBook
 */
class Kernel
{

    /**
     * @var array
     */
    protected static $_defaults = array(
        'php' => array(
            'default_controller'    => 'default',
            'default_action'        => 'index'
        ),
        'paths' => array(
            'src_dir'               => 'src',
            'user_dir'              => 'user',
            'var_dir'               => 'var',
            'web_dir'               => 'www'
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
            'app_i18n'              => 'docbook_i18n.csv',
            'config_file'           => 'docbook.ini',
            'user_config_file'      => 'user_config.ini'
        )
    );

    /**
     * @var array Table of all configuration values
     */
    protected static $_registry = array();

//*/
    /**
     * @var array Table of properties for debug
     */
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

    /**
     * @return void
     */
    public static function init()
    {
        self::$_registry = array();
    }

    /**
     * @throws \Exception
     */
    public static function boot()
    {
        try {
            // initialize object
            self::init();

            // installation base path
            self::$_registry['app_base_path'] = self::slashDirname(dirname(dirname(__DIR__)));

            // 1st level paths
            foreach (self::$_defaults['paths'] as $name=>$path) {
                $path_name = str_replace('_dir', '_path', $name);
                self::$_registry[$path_name] = self::$_registry['app_base_path'].self::slashDirname($path);
            }

            // vendor dirname
            self::$_registry['vendor'] = self::slashDirname(self::$_defaults['dirnames']['vendor_dir']);

            // var must be writable
            if (!is_writable(self::getPath('var'))) {
                throw new \Exception("Directory 'var/' must be writable!");
            }

            // user must be writable
            if (!is_writable(self::getPath('user'))) {
                throw new \Exception("Directory 'user/' must be writable!");
            }

            // src/config/
            self::$_registry['config_path'] = self::$_registry['src_path']
                .self::slashDirname(self::$_defaults['dirnames']['config_dir']);

            // src/templates/
            self::$_registry['templates_path'] = self::$_registry['src_path']
                .self::slashDirname(self::$_defaults['dirnames']['templates_dir']);

            // user/config/
            self::$_registry['user_config_path'] = self::$_registry['user_path']
                .self::slashDirname(self::$_defaults['dirnames']['config_dir']);

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
            self::$_registry['app_manifest_path'] = self::$_registry['app_base_path']
                .self::$_defaults['filenames']['app_manifest'];

            // config
            self::$_registry['app_config_path'] = self::getPath('user_config')
                .self::$_defaults['filenames']['config_file'];

            // i18n
            self::$_registry['app_i18n_path'] = self::getPath('user_config')
                .self::$_defaults['filenames']['app_i18n'];

        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @param array $config
     * @throws \Exception
     */
    public static function parseConfig(array $config)
    {
        try {

            // web interface
            self::$_registry['app_interface_path'] = self::getPath('web')
                .$config['app']['app_interface'];

            // web docbook assets
            self::$_registry['docbook_assets_path'] = self::getPath('web')
                .$config['app']['internal_assets_dir'];

        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @return bool
     */
    public static function isDevMode()
    {
        return (defined('DOCBOOK_MODE') && DOCBOOK_MODE==='dev');
    }

    /**
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public static function getPath($name)
    {
        // absolute path first
        $_name = str_replace('_dir', '', $name) . '_path';
        if (array_key_exists($_name, self::$_registry)) {
            return self::$_registry[$_name];

        // else relative one
        } elseif (array_key_exists($name, self::$_registry)) {
            return self::$_registry[$name];

        // else error
        } else {
            throw new \Exception(
                sprintf('Unknown DocBook path "%s"!', $name)
            );
        }
    }

    /**
     * @param $filename
     * @param string $filetype
     * @return bool|string
     * @throws \Exception
     */
    public static function fallbackFinder($filename, $filetype = 'templates_dir')
    {
        try {
            // user first
            $filetype_name  = 'user_'.$filetype;
            $user_path      = self::getPath(str_replace('_dir', '_path', $filetype_name));
            $user_file_path = $user_path.$filename;
            if (file_exists($user_file_path)) {
                return $user_file_path;
            }

            // default
            $filetype_default   = 'src_'.$filetype;
            $default_path       = self::getPath(str_replace('_dir', '_path', $filetype_default));
            $def_file_path      = $default_path.$filename;
            if (file_exists($def_file_path)) {
                return $def_file_path;
            } else {
                throw new \Exception(
                    sprintf('File "%s" not found!', $filename)
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
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
     * Clear DocBook's cache on Composer's event
     *
     * @return void
     */
    public static function clearCache()
    {
        $base_path  = realpath(__DIR__.'/../..') . DIRECTORY_SEPARATOR;
        return self::remove($base_path.'var');
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
