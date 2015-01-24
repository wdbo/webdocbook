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

namespace WebDocBook\HttpFundamental;

use \WebDocBook\FrontController;
use \WebDocBook\Kernel;
use \WebDocBook\Exception\NotFoundException;
use \Library\HttpFundamental\Request as BaseRequest;

/**
 * Class Request
 *
 * This is the global request of the application
 */
class Request
    extends BaseRequest
{

    /**
     * @var array
     */
    protected $routing = array();

    /**
     * Constructor : defines the current URL and gets the routes
     */
    public function __construct()
    {
        parent::guessFromCurrent();
        $server_uri         = $_SERVER['REQUEST_URI'];
        $server_query       = $_SERVER['QUERY_STRING'];
        $full_query_string  = str_replace(array('?',$server_query), '', trim($server_uri, '/'));
        parse_str($full_query_string, $full_query);
        if (!empty($full_query)) {
            $this->setArguments(array_merge($this->getArguments(), $full_query));
        }
    }

    /**
     * @param array $routing
     * @return $this
     */
    public function setRouting(array $routing)
    {
        $this->routing = $routing;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @return $this
     */
    public function parseWDBRequest()
    {
        $server_pathtrans   = isset($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : null;
        $server_uri         = $_SERVER['REQUEST_URI'];
        $server_query       = $_SERVER['QUERY_STRING'];
        $server_argv        = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;
        $wdb                = FrontController::getInstance();

        $file = $path = $action = null;
        $args = array();

        // first: request path from URL
        if (!empty($server_query)) {
            $req = $server_query;
            if ($req===basename(Kernel::getPath('app_interface'))) {
                $req = $server_uri;
            }

            // strip query string
            if (false!==strpos($req, '?')) {
                $tmp_action = substr($req, strpos($req, '?')+1);
                parse_str($tmp_action, $action_str_args);
                if (!empty($action_str_args)) {
                    $args = array_merge($args, $action_str_args);
                }
                $req = substr($req, 0, strpos($req, '?'));
            }

            // if '/action'
            if ($ctrl = Kernel::findController(trim($req, '/'))) {
                $action = trim($req, '/');
            } else {
                $parts      = explode('/', $req);
                $parts      = array_filter($parts);
                $int_index  = array_search(
                    basename(Kernel::getPath('app_interface')),
                    $parts
                );
                if (!empty($int_index)) {
                    unset($parts[$int_index]);
                }
                $original_parts = $parts;

                // classic case : XXX/YYY/...(/action)
                $test_file = Kernel::findDocument(implode('/', $parts));
                while(empty($test_file) && count($parts)>0) {
                    array_pop($parts);
                    $test_file = Kernel::findDocument(implode('/', $parts));
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
        }

        if (!empty($file)) {
            $wdb->setInputFile($file);
            if (file_exists($file)) {
                $wdb->setInputPath(is_dir($file) ? $file : dirname($file));
            }
        } else {
            $wdb->setInputPath('/');
        }
        
        // if GET args in action
        if (!empty($action) && strpos($action, '?')!==false) {
            $action_new     = substr($action, 0, strpos($action, '?'));
            $action_args    = substr($action, strpos($action, '?')+1);
            parse_str($action_args, $action_str_args);
            if (!empty($action_str_args)) {
                $args = array_merge($args, $action_str_args);
            }
            $action = $action_new;
        } 

        // if PHP GET args
        if (!empty($_GET)) {
            $args = array_merge($args, $_GET);
        }

        // if GET args from diff( uri-query )
        if (!empty($server_uri) && !empty($server_query) && 0<substr_count($server_uri, $server_query)) {
            $uri_diff = trim(str_replace($server_query, '', $server_uri), '/');
            if (!empty($uri_diff)) {
                if (substr($uri_diff, 0, 1)==='?') {
                    $uri_diff = substr($uri_diff, 1);
                }
                parse_str($uri_diff, $uri_diff_args);
                if (!empty($uri_diff_args)) {
                    $args = array_merge($args, $uri_diff_args);
                }
            }
        }

/*//
header('Content-Type: text/plain');
echo '$server_pathtrans=    '.var_export($server_pathtrans,1).PHP_EOL;
echo '$server_uri=          '.var_export($server_uri,1).PHP_EOL;
echo '$server_query=        '.var_export($server_query,1).PHP_EOL;
echo '$server_argv=         '.var_export($server_argv,1).PHP_EOL;
echo '$file=                '.var_export($file,1).PHP_EOL;
echo '$path=                '.var_export($path,1).PHP_EOL;
echo '$action=              '.var_export($action,1).PHP_EOL;
echo '$args=                '.var_export($args,1).PHP_EOL;
exit('end');
//*/

        if (!empty($args)) {
            $wdb->setQuery($args);
        }

        $wdb->setAction(!empty($action) ? $action : 'default');

        return $this;
    }

    /**
     * @return array
     * @throws \WebDocBook\Exception\NotFoundException
     */
    public function getWDBRouting()
    {
        $wdb                = FrontController::getInstance();
        $original_page_type = $wdb->getAction();
        $page_type          = !empty($original_page_type) ? $original_page_type : 'default';
        $input_file         = $wdb->getInputFile();
        if (empty($input_file)) {
            $input_path     = $wdb->getInputPath();
            if (!empty($input_path)) {
                $input_file = Kernel::getPath('web').trim($input_path, '/');
            }
        }

        $ctrl_infos = Kernel::findController($page_type);
        if ($ctrl_infos) {
            $this->setRouting($ctrl_infos);
        } else {
            if (!empty($original_page_type)) {
                throw new NotFoundException(
                    sprintf('The requested "%s" action was not found!', $original_page_type)
                );
            } else {
                throw new NotFoundException(
                    sprintf('The requested page was not found (searching "%s")!', $input_file)
                );
            }
        }
        return $this->getRouting();
    }

}

// Endfile
