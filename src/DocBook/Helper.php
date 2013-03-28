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
    DocBook\CommandNotFoundException,
    DocBook\FrontController;

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

    public static function slashDirname($dirname)
    {
        return rtrim($dirname, '/ '.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
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
        $tmp = self::slashDirname($docbook->getPath('tmp'));
        $du_cmd = exec('which du');
        if (empty($du_cmd)) {
            throw new CommandNotFoundException('du');
        }

        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();
        $command = $du_cmd.' -cLak '.$path.' | grep total';
        $resource = proc_open($command, $descriptorspec, $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($stdout && !$status) {
            $result = explode(' ', $stdout);
            return WebFilesystem::getTransformedFilesize(1024*array_shift($result));
        }
        return 0;
    }

    /**
     * Get the current browser URL
     * @param bool $entities Protect '&' entities parsing them in '&amp;' ? (default is FALSE)
     * @param bool $base Do you want just the base URL, without any URI (default is FALSE)
     * @param bool $no_file Do you want just the base URL path, without the input file and any URI (default is FALSE)
     * @return string The URL found
     */
    public static function currentUrl($entities = false, $base = false, $no_file = false)
    {
        $protocl = self::getProtocol();
        if (!isset($GLOBALS['REQUEST_URI'])){
            if (isset($_SERVER['REQUEST_URI'])) {
                $GLOBALS['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            } else {
                $GLOBALS['REQUEST_URI'] = $_SERVER['PHP_SELF'];
                if ($_SERVER['QUERY_STRING']
                AND !strpos($_SERVER['REQUEST_URI'], '?'))
                    $GLOBALS['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
            }
        }
        $url = $protocl.'://'.$_SERVER['HTTP_HOST'].$GLOBALS['REQUEST_URI'];
        if ($base && strpos($url, '?'))
            $url = substr($url, 0, strrpos($url, '?'));
        if ($no_file && strpos($url, '/'))
            $url = substr($url, 0, strrpos($url, '/')).'/';
        if (true===$entities)
            $url = str_replace('&', '&amp;', $url);
        return $url;
    }
    
    /**
     * @ignore
     */
    public static function getProtocol()
    {
        return ((
            (isset($_SERVER["SCRIPT_URI"]) AND
                substr($_SERVER["SCRIPT_URI"],0,5) == 'https')
            OR isset($_SERVER['HTTPS'])
        ) ? 'https' : 'http');
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
        $path = self::slashDirname($docbook->getPath('base_dir_http')).self::slashDirname($path);
        $grep_cmd = exec('which grep');
        if (empty($grep_cmd)) {
            throw new CommandNotFoundException('grep');
        }

        $command = $grep_cmd.' -Rn -A 2 -B 2 --include="*.md" "'.$regexp.'" '.$path;

        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();
        $resource = proc_open($command, $descriptorspec, $pipes, $path);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($status) return null;

        $result_files = explode("\n--", $stdout);
        $result = array();
        foreach($result_files as $stack) {
            $filename = substr($stack, 0, strpos($stack, '-'));
            $filepath = str_replace($path, '', $filename);
            if (!isset($result[$filename])) {
                $result[$filename] = array();
            }
            foreach(explode("\n", $stack) as $line) {
                $filename_rest = substr($line, strlen($filename)+1);
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


}

// Endfile
