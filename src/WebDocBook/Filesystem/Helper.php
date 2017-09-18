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

namespace WebDocBook\Filesystem;

/**
 * Class Helper
 *
 * This class MUST NOT depend on any other as it is used
 * by the `\WebDocBook\Composer\Manager` object to load
 * the system on Composer's action (the autoloader may not
 * be loaded).
 */
class Helper
{

    /**
     * Get a dirname with one and only trailing slash
     *
     * @param   string  $dirname
     * @return  string
     */
    public static function slashDirname($dirname = null)
    {
        if (is_null($dirname) || empty($dirname)) {
            return '';
        }
        return rtrim($dirname, '/ '.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * Test if a path seems to be a git clone
     *
     * @param   string  $path
     * @return  bool
     */
    public static function isGitClone($path = null)
    {
        if (is_null($path) || empty($path)) {
            return false;
        }
        $dir_path = self::slashDirname($path).'.git';
        return (bool) (file_exists($dir_path) && is_dir($dir_path));
    }

    /**
     * Test if a filename seems to have a dot as first character
     *
     * @param   string  $path
     * @return  bool
     */
    public static function isDotPath($path = null)
    {
        if (is_null($path) || empty($path)) {
            return false;
        }
        return (bool) ('.'===substr(basename($path), 0, 1));
    }

    /**
     * Test if a directory exists and try to create it if so
     *
     * @param $path
     * @return bool
     */
    public static function ensureExists($path)
    {
        if (false===@file_exists($path)) {
            return @mkdir($path);
        } else {
            return true;
        }
    }

    /**
     * Test if a path exists and is writable
     *
     * @param $path
     * @return bool
     */
    public static function ensureIsWritable($path)
    {
        if (true===self::ensureExists($path)) {
            return @is_writable($path);
        } else {
            return false;
        }
    }

    /**
     * Try to remove a path
     *
     * @param   string $path
     * @param   bool $parent
     * @return  bool
     */
    public static function remove($path, $parent = true)
    {
        $ok = true;
        if (true===self::ensureExists($path)) {
            if (false===@is_dir($path) || true===is_link($path)) {
                return @unlink($path);
            }
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
                    $ok = @unlink($item);
                }
            }
            if ($ok && $parent) {
                @rmdir($path);
            }
            @clearstatcache();
        }
        return $ok;
    }

    /**
     * Read and parse a INI content file
     *
     * @param $path
     * @return array|bool
     */
    public static function parseIni($path)
    {
        if (true===@file_exists($path)) {
            $data = parse_ini_file($path, true);
            if ($data && !empty($data)) {
                return $data;
            }
        }
        return false;
    }

    /**
     * Read and parse a JSON content file
     *
     * @param $path
     * @return bool|mixed
     */
    public static function parseJson($path)
    {
        if (true===@file_exists($path)) {
            $ctt = file_get_contents($path);
            if ($ctt!==false) {
                $data = json_decode($ctt, true);
                if ($data && !empty($data)) {
                    return $data;
                }
            }
        }
        return false;
    }

}

// Endfile
