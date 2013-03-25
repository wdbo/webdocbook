<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\DocBookException,
    DocBook\DocBookRuntimeException;

use \DateTime,
    \ReflectionMethod;

use DocBook\FrontController;

/**
 */
class Helper
{

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

    public static function securedPath($path)
    {
        $docbook = FrontController::getInstance();
        return str_replace($docbook->getPath('root_dir'), '/[***]', $path);
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
        return (true===$with_interface ? FrontController::DOCBOOK_INTERFACE.'?' : '/').trim($rel_path, '/').(!empty($type) ? '/'.$type : '');
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

}

// Endfile
