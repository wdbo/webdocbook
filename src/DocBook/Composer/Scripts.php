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
    protected static $_inited = false;

    /**
     * Initialize DocBook environment
     * @throws \Exception
     * @see \DocBook\Kernel::boot()
     */
    public static function init()
    {
        if (!self::$_inited) {
            if (!@class_exists('\DocBook\Util\Filesystem')) {
                include_once __DIR__.'/../Util/Filesystem.php';
            }
            if (!@class_exists('\DocBook\Kernel')) {
                include_once __DIR__.'/../Kernel.php';
            }
            \DocBook\Kernel::boot(true);
            self::$_inited = true;
        }
    }

    /**
     * @param \Composer\Script\Event $event
     * @return void
     * @throws \Exception
     */
    public static function postCreateProject(\Composer\Script\Event $event)
    {
        $composer   = $event->getComposer();
        $io         = $event->getIO();

        try {
            self::init();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new \Exception(
                "An error occurred while trying to init the app ...".PHP_EOL
                ."You should correct the error and try: 'composer run-script post-create-project-cmd'.".PHP_EOL
                ."Caught exception: '$message'."
            );
        }

        \DocBook\Kernel::installConfig();
    }

    /**
     * @param \Composer\Script\Event $event
     * @return void
     * @throws \Exception
     */
    public static function postAutoloadDump(\Composer\Script\Event $event)
    {
        $composer   = $event->getComposer();
        $io         = $event->getIO();

        try {
            self::init();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new \Exception(
                "An error occurred while trying to init the app ...".PHP_EOL
                ."You should correct the error and try: 'composer run-script post-autoload-dump'.".PHP_EOL
                ."Caught exception: '$message'."
            );
        }

    }

    /**
     * @param \Composer\Script\Event $event
     * @return void
     * @throws \Exception
     */
    public static function postUpdate(\Composer\Script\Event $event)
    {
        $composer   = $event->getComposer();
        $io         = $event->getIO();

        try {
            self::init();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new \Exception(
                "An error occurred while trying to init the app ...".PHP_EOL
                ."You should correct the error and try: 'composer run-script post-update-cmd'.".PHP_EOL
                ."Caught exception: '$message'."
            );
        }

        if (\DocBook\Kernel::clearCache()) {
            $io->write( '<info>Docbook cache has been cleared</info>' );
        }
    }

    /**
     * @param \Composer\Script\Event $event
     * @return void
     * @throws \Exception
     */
    public static function postInstall(\Composer\Script\Event $event)
    {
        $composer   = $event->getComposer();
        $io         = $event->getIO();

        try {
            self::init();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new \Exception(
                "An error occurred while trying to init the app ...".PHP_EOL
                ."You should correct the error and try: 'composer run-script post-install-cmd'.".PHP_EOL
                ."Caught exception: '$message'."
            );
        }

        if (\DocBook\Kernel::clearCache()) {
            $io->write( '<info>Docbook cache has been cleared</info>' );
        }
    }

}

// Endfile
