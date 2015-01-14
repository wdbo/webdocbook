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

/**
 * Class Scripts
 *
 * Defines actions to launch on Composer events.
 *
 * @see https://getcomposer.org/apidoc/master/index.html
 */
class Scripts
{

    /**
     * @var bool
     */
    protected static $_inited   = false;

    /**
     * @var null
     */
    protected static $_composer = null;

    /**
     * @var null
     */
    protected static $_io       = null;

    /**
     * Initialize DocBook environment
     *
     * @param   \Composer\Script\Event $event
     * @param   string $cmd
     * @throws  \Exception
     * @see     \DocBook\Kernel::boot()
     */
    protected static function __init(\Composer\Script\Event $event, $cmd = null)
    {
        if (!self::$_inited) {
            try {
                self::$_composer    = $event->getComposer();
                self::$_io          = $event->getIO();
                if (!@class_exists('\DocBook\Util\Filesystem')) {
                    include_once    __DIR__.'/../Util/Filesystem.php';
                }
                if (!@class_exists('\DocBook\Kernel')) {
                    include_once    __DIR__.'/../Kernel.php';
                }
                \DocBook\Kernel::boot(true);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                throw new \Exception(
                    "An error occurred while trying to init the app ...".PHP_EOL
                    .(!is_null($cmd) ? "You should correct the error and try: 'composer run-script $cmd'.".PHP_EOL : '')
                    ."Caught exception: '$message'."
                );
            }
            self::$_inited = true;
        }
    }

// ------------------
// Composer events
// ------------------

    /**
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     * @see     self::docbookInit()
     */
    public static function postCreateProject(\Composer\Script\Event $event)
    {
        try {
            self::docbookInit($event);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     * @see     self::docbookClearCache()
     */
    public static function postAutoloadDump(\Composer\Script\Event $event)
    {
        try {
            self::docbookClearCache($event);
        } catch (\Exception $e) {
            throw $e;
        }
   }

    /**
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     * @see     self::docbookClearCache()
     */
    public static function postUpdate(\Composer\Script\Event $event)
    {
        try {
            self::docbookClearCache($event);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     * @see     self::docbookInit()
     */
    public static function postInstall(\Composer\Script\Event $event)
    {
        try {
            self::docbookInit($event);
        } catch (\Exception $e) {
            throw $e;
        }
    }

// ------------------
// Custom commands
// ------------------

    /**
     * Initialize DocBook: flush cache and install configuration files
     *
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     * @see     self::docbookClearCache()
     */
    public static function docbookInit(\Composer\Script\Event $event)
    {
        try {
            self::__init($event, 'docbook-init');
            self::docbookClearCache($event);
            if (\DocBook\Kernel::installConfig()) {
                self::$_io->write( '<info>DocBook configuration has been installed</info>' );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Clear DocBook cache in `var/cache/`
     *
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     */
    public static function docbookClearCache(\Composer\Script\Event $event)
    {
        try {
            self::__init($event, 'docbook-clear-cache');
            if (\DocBook\Kernel::clearCache()) {
                self::$_io->write( '<info>DocBook cache has been cleared</info>' );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}

// Endfile
