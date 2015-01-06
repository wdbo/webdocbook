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

/**
 */
class Scripts
{

    public static function emptyCache(Event $event)
    {
        $_ds = DIRECTORY_SEPARATOR;
        $composer = $event->getComposer();
        $io = $event->getIO();
        $app_base_path = realpath(__DIR__.'/../../..') . $_ds;
        if (self::remove($app_base_path.'tmp')) {
            $io->write( '<info>Docbook cache has been cleared</info>' );
        }
    }

    /**
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
            if ($ok) rmdir($path);
            clearstatcache();
            return true;
        }
        return false;
    }

}

// Endfile
