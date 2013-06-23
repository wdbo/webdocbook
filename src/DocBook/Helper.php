<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\DocBookException,
    DocBook\DocBookRuntimeException,
    DocBook\FrontController;

use Library\Command,
    Library\Helper\Directory as DirectoryHelper,
    Library\Helper\Url as UrlHelper;

use \DateTime,
    \ReflectionMethod;

use WebFilesystem\WebFilesystem;

/**
 */
class Helper
{

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
                    sprintf('An error occured while trying to create directory "%s"!', $directory)
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
            return WebFilesystem::getTransformedFilesize(1024*array_shift($result));
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
        return $result;
    }

    public static function getFileLinesCount($path)
    {
        $docbook = FrontController::getInstance();

        $wc_cmd = Command::getCommandPath('wc');
        $command = $wc_cmd.' -l '.$path;
        list($stdout, $status, $stderr) = $docbook->getTerminal()->run($command, $path);

        $lines = array_shift(explode(' ', trim($stdout)));
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
			'apache_version'    => apache_get_version(),
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

}

// Endfile
