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

namespace WebDocBook\Composer;

use \Composer\Script\Event;

/**
 * Class Manager
 *
 * Manager for Composer events.
 *
 * @see https://getcomposer.org/apidoc/master/index.html
 */
class Manager
{

    /**
     * @var bool
     */
    protected static $_inited   = false;

    /**
     * @var \WebDocBook\Composer\ScriptInterface
     */
    protected static $_cmd      = null;

    /**
     * @var \Composer\Script\Event
     */
    protected static $_event    = null;

    /**
     * Initialize WebDocBook environment
     *
     * @param   \Composer\Script\Event $event
     * @param   \WebDocBook\Composer\ScriptInterface $cmd
     * @throws  \WebDocBook\Composer\Exception
     * @see     \WebDocBook\Kernel::boot()
     */
    public static function init(Event $event, ScriptInterface $cmd)
    {
        if (!self::$_inited) {
            try {
                self::$_event   = $event;
                self::$_cmd     = $cmd;
                if (false===@class_exists('\WebDocBook\Filesystem\Helper')) {
                    include_once    __DIR__.'/../Filesystem/Helper.php';
                }
                if (false===@class_exists('\WebDocBook\Kernel')) {
                    include_once    __DIR__.'/../Kernel.php';
                }
                self::parseArguments();
                if (!defined(\WebDocBook\Kernel::BASEDIR_CONSTNAME)) {
                    self::parseExtra();
                }
                \WebDocBook\Kernel::boot(true);
            } catch (\Exception $e) {
                self::error($e);
            }
            self::$_inited = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function parseExtra()
    {
        $extra = self::$_event->getComposer()->getPackage()->getExtra();
        if (!empty($extra) && array_key_exists('wdb-basedir', $extra)) {
            $path = $extra['wdb-basedir'];
            self::setBaseDir($path);
        }
    }

    /**
     * @throws Exception
     */
    public static function parseArguments()
    {
        $args = self::$_event->getArguments();
        if (!empty($args)) {
            if (strpos($args[0], 'basedir')===false || strpos($args[0], '=')===false) {
                throw new \Exception('Un-understood argument! You must use "--basedir=PATH".');
            }
            list(, $path) = explode('=', $args[0]);
            self::setBaseDir($path);
        }
    }

    /**
     * @param $path
     * @throws \Exception
     */
    public static function setBaseDir($path)
    {
        $realpath = realpath($path);
        if (!empty($realpath)) {
            $path = $realpath;
        }
        if ( ! file_exists($path)) {
            throw new \Exception(
                sprintf('Base directory "%s" not found!', $path)
            );
        }
        if ( ! is_dir($path)) {
            throw new \Exception(
                sprintf('Base directory "%s" is not a directory!', $path)
            );
        }
        define(\WebDocBook\Kernel::BASEDIR_CONSTNAME, $path);
    }

    /**
     * @param string|\Exception $e
     * @throws \WebDocBook\Composer\Exception
     */
    public static function error($e = null)
    {
        if (is_string($e)) {
            $tmp = new Exception($e, 0, null, self::$_cmd);
            $e = $tmp;
        } elseif (!($e instanceof \WebDocBook\Composer\Exception)) {
            $tmp = new Exception(
                (is_null($e) ? '' : $e->getMessage()),
                0, null, self::$_cmd
            );
            $e = $tmp;
        }
        throw $e;
    }

    /**
     * @param $str
     */
    public static function info($str)
    {
        self::$_event->getIO()->write('<info>[WebDocBook] '.$str.'</info>');
    }

}

// Endfile
