<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use \DocBook\Abstracts\AbstractFrontController;
use \DocBook\Abstracts\AbstractPage;
use \Markdown\Parser, \Markdown\ExtraParser;

/**
 */
class FrontController extends AbstractFrontController
{

    const DOCBOOK_INTERFACE = 'docbook.php';
    const MARKDOWN_CONFIG = 'markdown.ini';
    const DOCBOOK_CONFIG = 'docbook.ini';
    const TEMPLATES_DIR = 'templates';
    const CONFIG_DIR = 'config';

    protected $params = array();
    protected $headers = array();
    protected $input_file;
    protected $registry = array();
    protected $page_type = 'default';

    // dependences
    protected $page_handler;
    protected $template_builder;
    protected $markdown_parser;

    public function __construct()
    {
        $this
            ->addPath('app_manifest', __DIR__.'/../../composer.json')
            ->addPath('base_dir_http', __DIR__.'/../../www/')
            ->addPath('base_dir', __DIR__.'/../')
            ->addPath('user_dir', __DIR__.'/../../user/');

        $this->init();
    }

    protected function init()
    {
        // the docbook config (required)
        $docbook_cfgfile = $this->fallbackFinder(self::DOCBOOK_CONFIG, 'config');
        if (!empty($docbook_cfgfile)) {
            $this->registry['docbook'] = parse_ini_file($docbook_cfgfile, true);
        } else {
            throw new Exception(
                sprintf('DocBook configuration file not found but is required (searching "%s")!', self::DOCBOOK_CONFIG)
            );
        }

        @date_default_timezone_set( $this->getConfig('timezone', 'Europe/London') );

        // the actual manifest
        $this->registry['manifest'] = json_decode(file_get_contents(self::$app_manifest), true);

        // the markdown config (not required)
        $emd_cfgfile = $this->fallbackFinder(self::MARKDOWN_CONFIG, 'config');
        if (!empty($emd_cfgfile)) {
            $this->registry['emd'] = parse_ini_file($emd_cfgfile, true);
        }

        // creating the Markdown parser
        $emd_cfg = isset($this->registry['emd']) ? $this->registry['emd'] : array();
        foreach($emd_cfg as $name=>$val) {
            @define($name, $val);
        }
        $this->setMarkdownParser(new ExtraParser);

        // creating the application default headers
        $charset = $this->getConfig('html:charset', 'utf-8');
        $content_type = $this->getConfig('html:content-type', 'text/html');
        $this->addHeader('Content-type', $content_type.'; charset: '.$charset);
        $app_name = $this->getConfig('title', null, 'manifest');
        $app_version = $this->getConfig('version', null, 'manifest');
        $app_website = $this->getConfig('homepage', null, 'manifest');
        $this->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        
        // the template builder
//        $this->setTemplateBuilder(new TemplateBuilder($this) );
        // template engine
        $views_dir = $this->locateDirectory( array(
            __DIR__.'/../templates', __DIR__.'/../../user/templates'
        ) );
        $loader = new \Twig_Loader_Filesystem( $views_dir );
        $this->twig = new \Twig_Environment($loader, array(
            'cache'             => $this->locateDirectory( $this->getOption('cache') ),
            'charset'           => $this->getOption('charset'),
            'debug'             => $this->getOption('debug'),
//            'strict_variables'  => $this->getOption('debug'),
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addExtension(new \DocBook_Twig_Extension());
    }

    public function distribute($return = false)
    {
        $input_file = $this->getInputFile();
        if (empty($input_file)) {
            $this->_parseQueryString();
        }

        $this->_createPageHandler($page_type);

        // for dev        
//        var_export($_GET);
//        $this->debug();

        $result = $this->getPage()->parse();
        if (true===$return) {
            return $result;
        } else {
            $this->display($result);
        }
    }
    
    public function display($content = '')
    {
        $page = $this->getPage();
        $template = $page::$template_name;
        
        if (!empty($template)) {
            $full_content = $this->getTemplateBuilder()->render(
                $template,
                array(
                    'title' => Helper::buildPageTitle($this->getInputFile()),
                    'content' => $content,
                )
            );
        }

        $this->_renderHeaders();
        echo !empty($full_content) ? $full_content : $content;
    }

// ---------------------
// Config & Fallback process
// ---------------------

    public function fallbackFinder($filename, $filetype = 'template')
    {
        $base_path = 'template'===$filetype ? self::TEMPLATES_DIR : self::CONFIG_DIR;
        $file_path = rtrim($base_path, '/').'/'.$filename;
        
        // user first
        $user_file_path = rtrim(self::$user_dir, '/').'/'.$file_path;
        if (file_exists($user_file_path)) {
            return $user_file_path;
        }

        // default
        $def_file_path = rtrim(self::$base_dir, '/').'/'.$file_path;
        if (file_exists($def_file_path)) {
            return $def_file_path;
        }

        // else false        
        return false;
    }

    public function setConfig($name, $value, $scope = null)
    {
        if (!is_null($scope)) {
            if (!isset($this->registry[$scope])) {
                $this->registry[$scope] = array();
            }
            $this->registry[$scope][$name] = $value;
        } else {
            $this->registry[$name] = $value;
        }
    }
    
    public function getConfig($name, $default = null, $scope = 'docbook')
    {
        if (strpos($name, ':')) {
            list($entry, $name) = explode(':', $name);
            $cfg = $this->getConfig($entry, array(), $scope);
            return isset($cfg[$name]) ? $cfg[$name] : $default;
        } else {
            if (isset($this->registry[$scope])) {
                $cfg = $this->registry[$scope];
            } else {
                throw new \RuntimeException(
                    sprintf('Unknown configuration scope "%s"!', $scope)
                );
            }
            return isset($cfg[$name]) ? $cfg[$name] : $default;
        }        
    }

// ---------------------
// Setters / Getters
// ---------------------

    public function addPath($name, $value)
    {
        $this->setConfig($name, $value, 'paths');
        return $this;
    }

    public function setInputFile($path)
    {
        if (file_exists($path)) {
            $this->input_file = $path;
        } else {
            throw new \InvalidArgumentException(
                sprintf('File "%s" not found!', $path)
            );
        }
        return $this;
    }

    public function getInputFile()
    {
        return $this->input_file;
    }

    public function setQueryString($uri)
    {
        $real_uri = end(explode(self::DOCBOOK_INTERFACE, $uri));
        $parsed = parse_url($real_uri);

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $this->setParams($params);
        }

        if (!empty($parsed['path'])) {
            $this->uri = $parsed['path'];
        } elseif (empty($parsed['query'])) {
            $this->uri = $real_uri;
        }

        return $this;
    }
    
    public function getQueryString()
    {
        return $this->uri;
    }
    
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function addParam($name, $value = null)
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    public function setHeaders(array $params)
    {
        $this->headers = $params;
        return $this;
    }

    public function addHeader($name, $value = null)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }


    public function setPageType($type)
    {
        $this->page_type = $type;
        return $this;
    }

    public function getPageType()
    {
        return $this->page_type;
    }
    
    public function setPage(AbstractPage $page)
    {
        $this->page_handler = $page;
        return $this;
    }

    public function getPage()
    {
        return $this->page_handler;
    }

    public function setMarkdownParser(ExtraParser $parser)
    {
        $this->markdown_parser = $parser;
        return $this;
    }
    
    public function getMarkdownParser()
    {
        return $this->markdown_parser;
    }

    public function setTemplateBuilder(TemplateBuilder $builder)
    {
        $this->template_builder = $builder;
        return $this;
    }
    
    public function getTemplateBuilder()
    {
        return $this->template_builder;
    }

// ---------------------
// Process
// ---------------------

    protected function _parseQueryString()
    {
        $uri = $this->getQueryString();
        $parts = explode('/', $uri);
        $parts = array_filter($parts);
        $original_parts = $parts;
        
        $file_path = rtrim(self::$base_dir_http, '/').'/'.implode('/', $parts);
        while(!file_exists($file_path) && count($parts)>0) {
            array_pop($parts);
            $file_path = rtrim(self::$base_dir_http, '/').'/'.implode('/', $parts);
        }

        if (count($parts)>0) {
            $this->setInputFile(implode('/', $parts));
        }

        $diff = array_diff($original_parts, $parts);
        if (!empty($diff) && count($diff)===1) {
            $this->setPageType(array_shift($diff));
        }
    }
    
    protected function _createPageHandler($type = null, $file = null)
    {
        $cfg = $this->registry['docbook']['page_types'];
        $page_type = !is_null($type) ? $type : $this->getPageType();
        $input_file = !is_null($file) ? $file : $this->getInputFile();

        if (array_key_exists($page_type, $cfg)) {
            $_cls = 'DocBook\\Page\\'.ucfirst($cfg[$page_type]);
        } else {
            throw new \Exception(
                sprintf('Configuration for page type "%s" is not defined!', $page_type)
            );
        }

        $this->setPage( new $_cls($this, $input_file) );
    }

    protected function _renderHeaders()
    {
        if (headers_sent()) return;
        foreach($this->getHeaders() as $name=>$val) {
            header($name.': '.$val);
        }
    }
    
// ---------------------
// Dev
// ---------------------

    public function debug()
    {
        echo '<pre>';
        var_dump($this);
        echo '</pre>';
        exit(0);
    }

}

// Endfile
