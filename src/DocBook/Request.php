<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\FrontController,
    DocBook\Helper,
    DocBook\NotFoundException;

use Library\Helper\Directory as DirectoryHelper,
    Library\HttpFundamental\Request as BaseRequest;

/**
 */
class Request extends BaseRequest
{

    protected $routing = array();

    /**
     * Constructor : defines the current URL and gets the routes
     */
    public function __construct()
    {
        parent::guessFromCurrent();
        $server_uri = $_SERVER['REQUEST_URI'];
        $server_query = $_SERVER['QUERY_STRING'];
        $full_query_string = str_replace(array('?',$server_query), '', trim($server_uri, '/'));
        parse_str($full_query_string, $full_query);
        if (!empty($full_query)) {
            $this->setArguments(array_merge($this->getArguments(), $full_query));
        }
    }

    public function setRouting(array $routing)
    {
        $this->routing = $routing;
        return $this;
    }

    public function getRouting()
    {
        return $this->routing;
    }

    public function parseDocBookRequest()
    {
        $server_pathtrans = $_SERVER['PATH_TRANSLATED'];
        $server_uri = $_SERVER['REQUEST_URI'];
        $server_query = $_SERVER['QUERY_STRING'];
        $server_argv = $_SERVER['argv'];
        $docbook = FrontController::getInstance();
        $locator = new Locator;
        $file = $path = $action = null;

/*
echo '<br />server_pathtrans: '.var_export($server_pathtrans,1);
echo '<br />server_uri: '.var_export($server_uri,1);
echo '<br />server_query: '.var_export($server_query,1);
echo '<br />server_argv: '.var_export($server_argv,1);
*/
 
        // first: request path from URL
        if (!empty($server_query)) {
            $req = $server_query;
            if ($req===FrontController::DOCBOOK_INTERFACE) {
                $req = $server_uri;
            }

            // if '/action'
            if ($ctrl = $locator->findController(trim($req, '/'))) {
                $action = trim($req, '/');
            } else {
                $parts = explode('/', $req);
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
        }
/*
        // second: request from CLI
        if (empty($action)) {
            if (!empty($server_argv)) {
                $tmp_action = end($server_argv);
            } elseif (!empty($server_pathtrans)) {
                $tmp_action = $server_pathtrans;
            }

            if (!empty($tmp_action)) {
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
        }
*/
        if (!empty($file)) {
            $docbook->setInputFile($file);
            if (file_exists($file)) {
                $docbook->setInputPath(is_dir($file) ? $file : dirname($file));
            }
        } else {
            $docbook->setInputPath('/');
        }
        
        // if GET args
        if (!empty($action) && substr($action, 0, 1)==='?') {
            parse_str(substr($action, 1), $args);
            $docbook->setQuery($args);
            $action = null;
        }
        $docbook->setAction(!empty($action) ? $action : 'default');

/*
echo '<br />file: '.var_export($docbook->getInputFile(),1);
echo '<br />path: '.var_export($docbook->getInputPath(),1);
echo '<br />action: '.var_export($docbook->getAction(),1);
echo '<br />arguments: '.var_export($docbook->getQuery(),1);
exit('yo');
*/
        return $this;
    }

    public function getDocBookRouting()
    {
        $docbook = FrontController::getInstance();
        $original_page_type = $docbook->getAction();
        $page_type = !empty($original_page_type) ? $original_page_type : 'default';
        $input_file = $docbook->getInputFile();
        if (empty($input_file)) {
            $input_path = $docbook->getInputPath();
            if (!empty($input_path)) {
                $input_file = DirectoryHelper::slashDirname($docbook->getPath('base_dir_http')).trim($input_path, '/');
            }
        }

        $ctrl_infos = $docbook->getLocator()->findController($page_type);
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
