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

namespace DocBook\Composer;

use \Composer\Script\Event;
use \DocBook\Composer\ScriptInterface;
use \DocBook\Composer\Exception;

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
     * @var \DocBook\Composer\ScriptInterface
     */
    protected static $_cmd      = null;

    /**
     * @var \Composer\Script\Event
     */
    protected static $_event    = null;

    /**
     * Initialize DocBook environment
     *
     * @param   \Composer\Script\Event $event
     * @param   \DocBook\Composer\ScriptInterface $cmd
     * @throws  \DocBook\Composer\Exception
     * @see     \DocBook\Kernel::boot()
     */
    public static function init(Event $event, ScriptInterface $cmd)
    {
        if (!self::$_inited) {
            try {
                self::$_event   = $event;
                self::$_cmd     = $cmd;
                if (false===@class_exists('\DocBook\Util\Filesystem')) {
                    include_once    __DIR__.'/../Util/Filesystem.php';
                }
                if (false===@class_exists('\DocBook\Kernel')) {
                    include_once    __DIR__.'/../Kernel.php';
                }
                \DocBook\Kernel::boot(true);
            } catch (\Exception $e) {
                self::error(null, $e);
            }
            self::$_inited = true;
        }
    }

    /**
     * @param string $str
     * @param \Exception $e
     * @throws \DocBook\Composer\Exception
     */
    public static function error($str, \Exception $e = null)
    {
        if (is_null($e)) {
            $e = new Exception($str, 0, null, self::$_cmd);
        } elseif (!($e instanceof \DocBook\Composer\Exception)) {
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
        self::$_event->getIO()->write('<info>[DocBook] '.$str.'</info>');
    }

}

// Endfile
