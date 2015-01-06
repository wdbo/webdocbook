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

use \Library\Command;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Helper\Text as TextHelper;
use \Library\Helper\Url as UrlHelper;
use \DateTime;
use \ReflectionMethod;
use \WebFilesystem\WebFilesystem;

/**
 */
class Helper
{

    public static function log($message, $level = 'debug', array $context = array(), $logname = null)
    {
        FrontController::getInstance()->log($message, $level, $context, $logname);
    }

    public static function getSafeIdString($string)
    {
        return TextHelper::stripSpecialChars(
            TextHelper::slugify($string), '-_'
        );
    }

    public static function getSlug($string)
    {
        return str_replace(array(' '), '_', strtolower($string));
    }

    public static function buildPageTitle($filename)
    {
        $name = basename($filename);
        return ucfirst(
            str_replace(array('_', '.'), ' ', 
                str_replace('.md', '', $name)
            )
        );
    }

    public static function getBreadcrumbs($path = null)
    {
        $docbook = FrontController::getInstance();
        $breadcrumbs = array();
        if (!empty($path)) {
            $parts = explode('/', str_replace($docbook->getPath('base_dir_http'), '', $path));
            $breadcrumbs = array_filter($parts);
        }
        return $breadcrumbs;
    }

    public static function getSecuredRealpath($path)
    {
        $docbook = FrontController::getInstance();
        return str_replace($docbook->getPath('root_dir'), '/[***]', $path);
    }

    public static function getRelativePath($path)
    {
        $docbook = FrontController::getInstance();
        return str_replace($docbook->getPath('base_dir_http'), '', $path);
    }

    public static function getAbsolutePath($path)
    {
        $docbook = FrontController::getInstance();
        return $docbook->getPath('base_dir_http').str_replace($docbook->getPath('base_dir_http'), '', $path);
    }

    public static function getAbsoluteRoute($path)
    {
        $url = UrlHelper::parse(UrlHelper::getRequestUrl());
        $url['path'] = self::getRelativePath(self::getRoute($path));
        $url['params'] = array();
        return UrlHelper::build($url);
    }

    public static function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            if (file_exists($directory)) {
                throw new DocBookRuntimeException(
                    sprintf('"%s" exists and is not a directory!', $directory)
                );
            }
            if (!@mkdir($directory, 0777, true)) {
                throw new DocBookRuntimeException(
                    sprintf('An error occurred while trying to create directory "%s"!', $directory)
                );
            }
        }
    }

    public static function getDateTimeFromTimestamp($timestamp)
    {
        $time = new DateTime;
        $time->setTimestamp( $timestamp );
        return $time;
    }

    public static function getRoute($path, $type = null, $with_interface = false)
    {
        $route = $path;
        $docbook = FrontController::getInstance();
        $rel_path = str_replace($docbook->getPath('base_dir_http'), '', $path);
        $add_last_slash = !empty($rel_path) && file_exists($path) && is_dir($path);
        return (true===$with_interface ? FrontController::DOCBOOK_INTERFACE.'?' : !empty($rel_path) ? '/' : '')
            .trim($rel_path, '/')
            .($add_last_slash ? '/' : '')
            .(!empty($type) ? ($add_last_slash ? '' : '/').$type : '');
    }

    public static function getDirectorySize($path)
    {
        $docbook = FrontController::getInstance();
        $tmp = DirectoryHelper::slashDirname($docbook->getPath('tmp'));

        $du_cmd = Command::getCommandPath('du');
        $grep_cmd = Command::getCommandPath('grep');
        $command = $du_cmd.' -cLak '.$path.' | '.$grep_cmd.' total';
        list($stdout, $status, $stderr) = $docbook->getTerminal()->run($command);

        if ($stdout && !$status) {
            $result = explode(' ', $stdout);
            return (1024*array_shift($result));
//            return WebFilesystem::getTransformedFilesize(1024*array_shift($result));
        }
        return 0;
    }

    /**
     * Launch a class's method fetching arguments
     *
     * @param string $_class The class name
     * @param string $_method The class method name
     * @param misc $args A set of arguments to fetch
     */
    public static function fetchArguments($_class = null, $_method = null, $args = null)
    {
        if (empty($_class) || empty($_method)) return;
        $args_def=array();
        if (!empty($args)) {
            $analyze = new ReflectionMethod($_class, $_method);
            foreach($analyze->getParameters() as $_param) {
                $arg_index = $_param->getName();
                $args_def[$_param->getPosition()] = isset($args[$arg_index]) ?
                    $args[$arg_index] : ( $_param->isOptional() ? $_param->getDefaultValue() : null );
            }
        }
        return call_user_func_array( array($_class, $_method), $args_def );
    }


    public static function processDocBookSearch($regexp, $path = null)
    {
        $docbook = FrontController::getInstance();
        if (is_null($path)) {
            $path = '/';
        }
        $path = DirectoryHelper::slashDirname($docbook->getPath('base_dir_http')).$path;
        $is_dir = file_exists($path) && is_dir($path);
        if ($is_dir) $path = DirectoryHelper::slashDirname($path);

        $grep_cmd = Command::getCommandPath('grep');
        $command = $grep_cmd.' -rn -A 2 -B 2'
            .($is_dir ? ' --include="*.md"' : '')
            .' "'.$regexp.'" '.$path;
        self::log('Running command: '.$command);
        list($stdout, $status, $stderr) = $docbook->getTerminal()->run($command, $path);

        if ($status) return null;

        $result_files = explode("\n--", $stdout);
        $result = array();
        foreach($result_files as $stack) {
            $filename = $is_dir ? substr($stack, 0, strpos($stack, '-')) : '';
            $filepath = str_replace($path, '', $filename);
            if (!isset($result[$filename])) {
                $result[$filename] = array();
            }
            foreach(explode("\n", $stack) as $line) {
                $filename_rest = $is_dir ? substr($line, strlen($filename)+1) : $line;
                $delim_dash = strpos($filename_rest, '-') ?: 10000;
                $delim_column = strpos($filename_rest, ':') ?: 10000;
                $delim = min($delim_dash, $delim_column);
                $linenumber = substr($filename_rest, 0, $delim);
                $linecontent = substr($filename_rest, $delim+1);
                $result[$filename][] = array(
                    'path'=>$filename,
                    'line'=>$linenumber,
                    'content'=>!empty($linecontent) ? $linecontent : '',
                    'highlighted'=>$delim===$delim_column
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

    public static function getFileLinesCount($path)
    {
        $docbook = FrontController::getInstance();

        $wc_cmd = Command::getCommandPath('wc');
        $command = $wc_cmd.' -l '.$path;
        list($stdout, $status, $stderr) = $docbook->getTerminal()->run($command, $path);

        $parts = explode(' ', trim($stdout));
        $lines = array_shift($parts);
        return !empty($lines) ? $lines : 0;
    }

    public static function getProfiler()
    {
        $docbook = FrontController::getInstance();
        return array(
            'date'              => new DateTime(),
            'timezone'          => date_default_timezone_get(),
            'php_uname'         => php_uname(),
            'php_version'       => phpversion(),
            'php_sapi_name'     => php_sapi_name(),
            'apache_version'    => function_exists('apache_get_version') ? apache_get_version() : '?',
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
            'git_clone'         => DirectoryHelper::isGitClone($docbook->getPath('root_dir')),
            'request'           => UrlHelper::getRequestUrl(),
        );
    }

    public static function isTranslationFile($path)
    {
        $parts = explode('.', basename($path));
        return count($parts)===3 && strlen($parts[1])===2 && $parts[2]==='md';
    }

    public static function isFileValid($file_path)
    {
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_INTERFACE && 
            $name!==FrontController::README_FILE
        );
    }
    
    public static function isDirValid($file_path)
    {
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_ASSETS
        );
    }

    public static function getGitConfig($repo_path)
    {
        if (DirectoryHelper::isGitClone($repo_path)) {
            $repo_config_file = DirectoryHelper::slashDirname($repo_path) .
                DirectoryHelper::slashDirname('.git') .
                'config';
            if (file_exists($repo_config_file)) {
                try {
                    $config = parse_ini_file($repo_config_file, true);
                    return $config;
                } catch (\Exception $e) {}
            }
        }
        return null;
    }

    public static function rssEncode($str, $cut = 800)
    {
        $str = preg_replace(',<h1(.*)</h1>,i', '', $str);
        $str = TextHelper::cut($str, $cut);
        return $str;
    }

    public static function getFlatDirscans($dirscan, $order_by_date = false)
    {
        $new_dirscan = $dirscan;
        if (isset($dirscan['dirscan']) && !empty($dirscan['dirscan']) && is_array($dirscan['dirscan'])) {
            foreach ($dirscan['dirscan'] as $i=>$val) {
                if (isset($val['dirscan']) && !empty($val['dirscan']) && is_array($val['dirscan'])) {
                    $sub_dirscan = $val['dirscan'];
                    self::getFlatDirscans($sub_dirscan);
                    self::_addDirscan($new_dirscan, $sub_dirscan);
                }
            }
        }

        if ($order_by_date) {
            usort($new_dirscan['dirscan'], "self::_cmpDirscan");
        }

        return $new_dirscan;
    }

    protected static function _addDirscan(&$dirscan, array $add)
    {
        foreach ($add as $index=>$val) {
            $_ind = isset($dirscan['dirscan'][$index]) ? $index.uniqid() : $index;
            $dirscan['dirscan'][$_ind] = $val;
        }
    }

    protected static function _cmpDirscan($a, $b)
    {
        return strcmp($a['mtime']->getTimestamp(), $b['mtime']->getTimestamp());
    }

    public static function getIcon($type = null, $class = '')
    {
        if (!empty($type)) {
            $docbook = FrontController::getInstance();
            $icons = $docbook->getRegistry()->get('icons', array(), 'docbook');
            return '<span class="glyphicon glyphicon-'
                .(isset($icons[$type]) ? $icons[$type] : $icons['default'])
                .(!empty($class) ? ' '.$class : '')
                .'"></span>';
        }
        return '';
    }

}

// Endfile
