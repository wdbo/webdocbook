<?php
/**
 * This file is part of the WebDocBook package.
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook\Composer;

use \Composer\Script\Event;
use \WebDocBook\Composer\ScriptInterface;
use \WebDocBook\Composer\Exception;

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
                if (false===@class_exists('\WebDocBook\Util\Filesystem')) {
                    include_once    __DIR__.'/../Util/Filesystem.php';
                }
                if (false===@class_exists('\WebDocBook\Kernel')) {
                    include_once    __DIR__.'/../Kernel.php';
                }
                self::parseArguments();
                \WebDocBook\Kernel::boot(true);
            } catch (\Exception $e) {
                self::error(null, $e);
            }
            self::$_inited = true;
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
            define('WEBDOCBOOK_BASEDIR', $path);
        }
    }

    /**
     * @param string $str
     * @param \Exception $e
     * @throws \WebDocBook\Composer\Exception
     */
    public static function error($str, \Exception $e = null)
    {
        if (is_null($e)) {
            $e = new Exception($str, 0, null, self::$_cmd);
        } elseif (!($e instanceof \WebDocBook\Composer\Exception)) {
            $tmp = new Exception(
                (is_null($str) ? (is_null($e) ? '' : $e->getMessage()) : $str),
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
