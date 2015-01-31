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

namespace WebDocBook\Util;

use \WebDocBook\Kernel;
use \WebDocBook\FrontController;
use \Library\Command;

/**
 * Class WDBHelper
 *
 * This is the internal WebDocBook helper class
 */
class WDBHelper
{

    /**
     * @param $regexp
     * @param null $path
     * @return array|null
     */
    public static function makeSearch($regexp, $path = null)
    {
        $wdb        = FrontController::getInstance();
        if (is_null($path)) {
            $path   = '/';
        }
        $path       = Kernel::getPath('web') . $path;

        $is_dir     = file_exists($path) && is_dir($path);
        if ($is_dir) {
            $path   = FilesystemHelper::slashDirname($path);
        }

        $grep_cmd   = Command::getCommandPath('grep');

        $cmd_filenames = $grep_cmd.' -rnl'
            .($is_dir ? ' --include="*.md"' : '')
            .' "'.$regexp.'" '.rtrim($path, '/');
        FrontController::getInstance()->log('Running command: '.$cmd_filenames);
        list($stdout, $status, $stderr) = $wdb->getTerminal()->run($cmd_filenames, $path);
        if ($status > 0) {
            return null;
        }
        $filenames = explode("\n", $stdout);

        $cmd_matches = $grep_cmd.' -rn -A 2 -B 2'
            .($is_dir ? ' --include="*.md"' : '')
            .' "'.$regexp.'" '.rtrim($path, '/');
        FrontController::getInstance()->log('Running command: '.$cmd_matches);
        list($stdout, $status, $stderr) = $wdb->getTerminal()->run($cmd_matches, $path);
        if ($status > 0) {
            return null;
        }
        $result_files = explode("\n--", $stdout);

        $result     = array();
        $fn_index   = 0;
        foreach($result_files as $stack) {
            $stack  = trim($stack);
            if (empty($stack)) {
                continue;
            }

            while (
                ($fn_index < count($filenames)) &&
                (substr($stack, 0, strlen($filenames[$fn_index])) !== $filenames[$fn_index])
            ) {
                $fn_index++;
            }

            $stack_lines    = explode("\n", $stack);
            if (isset($filenames[$fn_index])) {
                $filename   = $filenames[$fn_index];
            } else {
                $filename   = $is_dir ? substr($stack, 0, strpos($stack, '-')) : '';
            }
            $filepath       = str_replace($path, '', $filename);
            if (!isset($result[$filename])) {
                $result[$filename] = array();
            }
            foreach($stack_lines as $line) {
                $filename_rest  = $is_dir ? substr($line, strlen($filename)+1) : $line;
                $delim_dash     = strpos($filename_rest, '-') ?: 10000;
                $delim_column   = strpos($filename_rest, ':') ?: 10000;
                $delim          = min($delim_dash, $delim_column);
                $linenumber     = substr($filename_rest, 0, $delim);
                $linecontent    = substr($filename_rest, $delim+1);
                $result[$filename][] = array(
                    'path'          => $filename,
                    'line'          => $linenumber,
                    'content'       => (!empty($linecontent) ? $linecontent : ''),
                    'highlighted'   => ($delim === $delim_column)
                );
            }
        }
        if (!empty($result)) {
            foreach ($result as $_ind=>$_val) {
                if (!is_string($_ind)) {
                    unset($result[$_ind]);
                }
            }
            $result = array_filter($result);
        }

        return $result;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function isTranslationFile($path)
    {
        $parts = explode('.', basename($path));
        return ( (count($parts) === 3) && (strlen($parts[1]) === 2) && ($parts[2] === 'md'));
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function isFileValid($file_path)
    {
        $name = basename($file_path);
        return (
            $name !== basename(Kernel::getPath('app_interface')) &&
            $name !== Kernel::getConfig('user_config:readme_filename', 'README.md')
        );
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function isDirValid($file_path)
    {
        $name = basename($file_path);
        return (
            $name !== basename(Kernel::getPath('webdocbook_assets'))
        );
    }

    /**
     * @param string $path
     * @return array
     */
    public static function getDirectoryMetaFiles($path)
    {
        $data = array();
        if (file_exists($path) && is_dir($path)) {
            $meta_files_cfg = Kernel::getConfig('meta_files');
            if (!empty($meta_files_cfg)) {
                foreach ($meta_files_cfg as $name=>$fn) {
                    $p = FilesystemHelper::slashDirname($path).$fn;
                    $data[$name] = file_exists($p) ? $p : null;
                }
            }
        }
        return $data;
    }

}

// Endfile
