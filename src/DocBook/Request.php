<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\Helper;

/**
 */
class Request
{

	/**
	 * The URL to work on
	 *
	 * @see current_url()
	 */
	var $url;

	/**
	 * The GET arguments
	 */
	var $get;

	/**
	 * The POST arguments
	 */
	var $post;

	/**
	 * The current user SESSION
	 */
	var $session;

	/**
	 * The current user COOKIES
	 */
	var $cookies;

	/**
	 * Constructor : defines the current URL and gets the routes
	 */
	public function __construct()
	{
		$this->url = Helper::currentUrl();
		$this->get = $_GET;
		$this->post = $_POST;
		$this->session = $_SESSION;
		$this->cookies = $_COOKIE;
	}

	/**
	 * Get the value of a specific argument from current parsed URL
	 *
	 * @param string $param The parameter name if so, or 'args' to get all parameters values
	 * @param const $flags The PHP flags used with htmlentities() (default is ENT_QUOTES)
	 * @param string $encoding The encoding used with htmlentities() (default is UTF-8)
	 * @return string The cleaned value
	 */
	public function cleanArg($arg_value, $flags = ENT_QUOTES, $encoding = 'UTF-8') 
	{
		if (is_string($arg_value)) {
	  		$result = stripslashes( htmlentities($arg_value, $flags, $encoding) );
		} elseif (is_array($arg_value)) {
			$result = array();
			foreach($arg_value as $arg=>$value) {
				$result[$arg] = $this->cleanArg($value, $flags, $encoding);
			}
		}
	  	return $result;
	}

	/**
	 * Get the value of a specific argument from current parsed URL
	 *
	 * @param string $param The parameter name if so, or 'args' to get all parameters values
	 * @param misc $default The default value sent if the argument is not setted
	 * @param bool $clean Clean the argument before return ? (default is true)
	 * @param const $flags The PHP flags used with htmlentities() (default is ENT_QUOTES)
	 * @param string $encoding The encoding used with htmlentities() (default is UTF-8)
	 * @return string The value retrieved, $default otherwise
	 */
	public function getGet($param = null, $default = false, $clean = true, $clean_flags = ENT_QUOTES, $clean_encoding = 'UTF-8') 
	{
  		if (!empty($this->get) && isset($this->get[$param])) {
			return true===$clean ? $this->cleanArg($this->get[$param], $clean_flags, $clean_encoding) : $this->get[$param];
  		}
	  	return $default;
	}

	/**
	 * Get the value of a specific argument from current posted values with request
	 *
	 * @param string $param The parameter name if so, or 'args' to get all parameters values
	 * @param misc $default The default value sent if the argument is not setted
	 * @param bool $clean Clean the argument before return ? (default is true)
	 * @param const $flags The PHP flags used with htmlentities() (default is ENT_QUOTES)
	 * @param string $encoding The encoding used with htmlentities() (default is UTF-8)
	 * @return string The value retrieved, $default otherwise
	 */
	public function getPost($param = null, $default = false, $clean = true, $clean_flags = ENT_QUOTES, $clean_encoding = 'UTF-8' ) 
	{
  		if (!empty($this->post) && isset($this->post[$param])) {
			return true===$clean ? $this->cleanArg($this->post[$param], $clean_flags, $clean_encoding) : $this->post[$param];
		}
	  	return $default;
	}

	/**
	 * Get the value of a specific argument value from current parsed URL
	 *
	 * @param string $param The parameter name if so, or 'args' to get all parameters values
	 * @param misc $default The default value sent if the argument is not setted
	 * @return string The value retrieved, $default otherwise
	 */
	public function getParam($param = null, $default = false) 
	{
	    $post = $this->getPost($param);
	    if (!empty($post)) return $post;

	    $get = $this->getGet($param);
	    if (!empty($get)) return $get;

	  	return $default;
	}

	/**
	 * Check if the request is sent by command line interface
	 *
	 * @return boolean TRUE if it is so ...
	 */
	public function isCli() 
	{
	  	return (php_sapi_name() == 'cli');
	}

    public function parseDocBookRequest()
    {
        $server_uri = $_SERVER['REQUEST_URI'];
        $server_argv = $_SERVER['argv'];
        $docbook = FrontController::getInstance();
        $locator = new Locator;
        $file = $path = $action = null;

        // first: request path from URL
        if (!empty($server_uri)) {
            $parts = explode('/', $server_uri);
            $parts = array_filter($parts);
            $int_index = array_search(FrontController::DOCBOOK_INTERFACE, $parts);
            if (!empty($int_index)) unset($parts[$int_index]);
            $original_parts = $parts;

            // classic case : XXX/YYY/...(/action)
            $test_file = $locator->locateDocument(implode('/', $parts));
            while(empty($test_file) && count($parts)>0) {
                array_pop($parts);
                $test_file = $locator->locateDocument(implode('/', $parts));
            }
            if (count($parts)>0) {
                $file = $test_file;
                $diff = array_diff($original_parts, $parts);
                if (!empty($diff) && count($diff)===1) {
                    $action = array_shift($diff);
                }
            } else {

                // case of a non-existing file : XXX/YYY/.../ZZZ.md(/action)
                $parts = $original_parts;
                $isMd = '.md'===substr(end($parts), -3);
                while (true!==$isMd && count($parts)>0) {
                    array_pop($parts);
                    $isMd = '.md'===substr(end($parts), -3);
                }
                if ($isMd && count($parts)>0) {
                    $file = implode('/', $parts);
                    $diff = array_diff($original_parts, $parts);
                    if (!empty($diff) && count($diff)===1) {
                        $action = array_shift($diff);
                    }
                }
            }
        }

        // second: request from CLI
        if (empty($action) && !empty($server_argv)) {
            $tmp_action = end($server_argv);
            if (!empty($file) && ($tmp_action===$file || trim($tmp_action, '/')===$file)) {
                $tmp_action = null;
            }
            if (!empty($tmp_action) && false!==strpos($tmp_action, $docbook->getPath('base_dir_http'))) {
                $tmp_action = str_replace($docbook->getPath('base_dir_http'), '', $tmp_action);
            }
            if (!empty($file) && ($tmp_action===$file || trim($tmp_action, '/')===$file)) {
                $tmp_action = null;
            }

            if (!empty($tmp_action)) {
                $action_parts = explode('/', $tmp_action);
                $action_parts = array_filter($action_parts);
                $action = array_shift($action_parts);
            }
        }

        if (!empty($file)) {
            $docbook->setInputFile($file);
            if (file_exists($file)) {
                $docbook->setInputPath(is_dir($file) ? $file : dirname($file));
            }
        } else {
            $docbook->setInputPath('/');
        }
        $docbook->setPageType(!empty($action) ? $action : 'default');
    }

}

// Endfile