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

namespace WebDocBook;

use \WebDocBook\Exception\RuntimeException;
use \WebDocBook\Util\Filesystem;
use \Library\Command;
use \Library\Helper\Text as TextHelper;
use \Library\Helper\Url as UrlHelper;
use \DateTime;
use \ReflectionMethod;

/**
 * Class Helper
 */
class Helper
{

    /**
     * @param $message
     * @param string $level
     * @param array $context
     * @param null $logname
     */
    public static function log($message, $level = 'debug', array $context = array(), $logname = null)
    {
        FrontController::getInstance()->log($message, $level, $context, $logname);
    }

    /**
     * @param $string
     * @return string
     */
    public static function getSafeIdString($string)
    {
        return TextHelper::stripSpecialChars(
            TextHelper::slugify($string), '-_'
        );
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function getSlug($string)
    {
        return str_replace(array(' '), '_', strtolower($string));
    }

    /**
     * @param $filename
     * @return string
     */
    public static function buildPageTitle($filename)
    {
        $name = basename($filename);
        return ucfirst(
            str_replace(array('_', '.'), ' ', 
                str_replace('.md', '', $name)
            )
        );
    }

    /**
     * @param null $path
     * @return array
     */
    public static function getBreadcrumbs($path = null)
    {
        $breadcrumbs = array();
        if (!empty($path)) {
            $parts          = explode('/', str_replace(Kernel::getPath('web'), '', $path));
            $breadcrumbs    = array_filter($parts);
        }
        return $breadcrumbs;
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function getSecuredRealpath($path)
    {
        return str_replace(Kernel::getPath('app_base_path'), '/[***]', $path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function getRelativePath($path)
    {
        return str_replace(Kernel::getPath('web'), '', $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function getAbsolutePath($path)
    {
        return Kernel::getPath('web')
            .str_replace(Kernel::getPath('web'), '', $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function getAbsoluteRoute($path)
    {
        $url            = UrlHelper::parse(UrlHelper::getRequestUrl());
        $url['path']    = self::getRelativePath(self::getRoute($path));
        $url['params']  = array();
        return UrlHelper::build($url);
    }

    /**
     * @param $directory
     * @return bool
     */
    public static function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            if (file_exists($directory)) {
                throw new RuntimeException(
                    sprintf('"%s" exists and is not a directory!', $directory)
                );
            }
            if (!@mkdir($directory, 0777, true)) {
                throw new RuntimeException(
                    sprintf('An error occurred while trying to create directory "%s"!', $directory)
                );
            }
        }
        return true;
    }

    /**
     * @param $timestamp
     * @return DateTime
     */
    public static function getDateTimeFromTimestamp($timestamp)
    {
        $time = new DateTime;
        $time->setTimestamp( $timestamp );
        return $time;
    }

    /**
     * @param $path
     * @param null $type
     * @param bool $with_interface
     * @return string
     */
    public static function getRoute($path, $type = null, $with_interface = false)
    {
        $route          = $path;
        $rel_path       = str_replace(Kernel::getPath('web'), '', $path);
        $add_last_slash = (!empty($rel_path) && file_exists($path) && is_dir($path));
        return (true===$with_interface ?
                Kernel::getPath('app_interface').'?'
                :
                (!empty($rel_path) ? '/' : '')
            )
            .trim($rel_path, '/')
            .($add_last_slash ? '/' : '')
            .(!empty($type) ? ($add_last_slash ? '' : '/').$type : '');
    }

    /**
     * @param $path
     * @return int
     */
    public static function getDirectorySize($path)
    {
        $wdb        = FrontController::getInstance();
        $tmp        = Kernel::getPath('var');
        $du_cmd     = Command::getCommandPath('du');
        $grep_cmd   = Command::getCommandPath('grep');
        $command    = $du_cmd.' -cLak '.$path.' | '.$grep_cmd.' total';

        list($stdout, $status, $stderr) = $wdb->getTerminal()->run($command);
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
     * @param mixed $args A set of arguments to fetch
     * @return mixed
     */
    public static function fetchArguments($_class = null, $_method = null, $args = null)
    {
        if (empty($_class) || empty($_method)) {
            return null;
        }
        $args_def = array();
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

    /**
     * @param $regexp
     * @param null $path
     * @return array|null
     */
    public static function processDocBookSearch($regexp, $path = null)
    {
        $wdb        = FrontController::getInstance();
        if (is_null($path)) {
            $path   = '/';
        }
        $path       = Kernel::getPath('web') . $path;

        $is_dir     = file_exists($path) && is_dir($path);
        if ($is_dir) {
            $path   = Filesystem::slashDirname($path);
        }

        $grep_cmd   = Command::getCommandPath('grep');

        $cmd_filenames = $grep_cmd.' -rnl'
            .($is_dir ? ' --include="*.md"' : '')
            .' "'.$regexp.'" '.rtrim($path, '/');
        self::log('Running command: '.$cmd_filenames);
        list($stdout, $status, $stderr) = $wdb->getTerminal()->run($cmd_filenames, $path);
        if ($status > 0) {
            return null;
        }
        $filenames = explode("\n", $stdout);

        $cmd_matches = $grep_cmd.' -rn -A 2 -B 2'
            .($is_dir ? ' --include="*.md"' : '')
            .' "'.$regexp.'" '.rtrim($path, '/');
        self::log('Running command: '.$cmd_matches);
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
     * @return int|mixed
     */
    public static function getFileLinesCount($path)
    {
        $wdb        = FrontController::getInstance();
        $wc_cmd     = Command::getCommandPath('wc');
        $command    = $wc_cmd.' -l '.$path;

        list($stdout, $status, $stderr) = $wdb->getTerminal()->run($command, $path);
        $parts      = explode(' ', trim($stdout));
        $lines      = array_shift($parts);
        return (!empty($lines) ? $lines : 0);
    }

    /**
     * @return array
     */
    public static function getProfiler()
    {
        return array(
            'date'              => new DateTime(),
            'timezone'          => date_default_timezone_get(),
            'php_uname'         => php_uname(),
            'php_version'       => phpversion(),
            'php_sapi_name'     => php_sapi_name(),
            'apache_version'    => function_exists('apache_get_version') ? apache_get_version() : '?',
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
            'git_clone'         => Filesystem::isGitClone(Kernel::getPath('app_base_path')),
            'request'           => UrlHelper::getRequestUrl(),
        );
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
     * @param $repo_path
     * @return array|null
     */
    public static function getGitConfig($repo_path)
    {
        if (Filesystem::isGitClone($repo_path)) {
            $repo_config_file = Filesystem::slashDirname($repo_path) .
                Filesystem::slashDirname('.git') .
                'config';
            if (file_exists($repo_config_file)) {
                try {
                    $config = Filesystem::parseIni($repo_config_file);
                    return $config;
                } catch (\Exception $e) {}
            }
        }
        return null;
    }

    /**
     * @param $str
     * @param int $cut
     * @return mixed|string
     */
    public static function rssEncode($str, $cut = 800)
    {
        $str = preg_replace(',<h1(.*)</h1>,i', '', $str);
        $str = TextHelper::cut($str, $cut);
        return $str;
    }

    /**
     * @param $dirscan
     * @param bool $order_by_date
     * @return mixed
     */
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

    /**
     * @param $dirscan
     * @param array $add
     */
    protected static function _addDirscan(&$dirscan, array $add)
    {
        foreach ($add as $index=>$val) {
            $_ind = isset($dirscan['dirscan'][$index]) ? $index.uniqid() : $index;
            $dirscan['dirscan'][$_ind] = $val;
        }
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    protected static function _cmpDirscan($a, $b)
    {
        return strcmp($a['mtime']->getTimestamp(), $b['mtime']->getTimestamp());
    }

    /**
     * @var array The icons names cache
     */
    private static $_cfg_icons = null;

    /**
     * @param null $type
     * @param string $class
     * @return string
     */
    public static function getIcon($type = null, $class = '')
    {
        if (!empty($type)) {
            if (is_null(self::$_cfg_icons)) {
                self::$_cfg_icons = Kernel::getConfig('icons', array());
            }
            return '<span class="fa fa-'
                .(isset(self::$_cfg_icons[$type]) ? self::$_cfg_icons[$type] : self::$_cfg_icons['default'])
                .(!empty($class) ? ' '.$class : '')
                .'"></span>';
        }
        return '';
    }

}

// Endfile
