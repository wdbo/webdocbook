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
     * Name of the DocBook's manifest
     */
    const APP_MANIFEST              = 'composer.json';

    /**
     * Name of the distributed DocBook's manifest
     */
    const APP_MANIFEST_DIST         = 'composer.dist.json';

    /**
     * Name of the DocBook's config file
     */
    const APP_CONFIG_DEFAULT        = '%user_dir%/docbook.ini';

    /**
     * Name of the distributed DocBook's config file
     */
    const APP_CONFIG_DIST           = 'docbook.dist.ini';

    /**
     * Name of the DocBook's language file
     */
    const APP_I18N_DEFAULT          = '%user_dir%/docbook_i18n.csv';

    /**
     * Name of the distributed DocBook's language file
     */
    const APP_I18N_DIST             = 'docbook_i18n.dist.csv';

    /**
     * Name of the DocBook's web interface file
     */
    const APP_WEBINTERFACE_DEFAULT  = '%web_dir%/index.php';

    /**
     * Name of the distributed DocBook's web interface file
     */
    const APP_WEBINTERFACE_DIST     = 'www_index.dist.php';

    public static function boot()
    {

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
