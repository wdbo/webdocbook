<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\Abstracts\AbstractFrontController;
use DocBook\Abstracts\AbstractPage;
use Markdown\Parser, \Markdown\ExtraParser;
use Patterns\Commons\ConfigurationRegistry;

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
    protected $input_path;
    protected $page_type = 'default';

    // dependences
    protected $registry;
    protected $page_handler;
    protected $template_builder;
    protected $markdown_parser;

    protected function __construct()
    {
        $this->registry = new ConfigurationRegistry();
        $src_dir = __DIR__.'/../';
        $base_dir = $src_dir.'../';

        $this
            ->addPath('app_manifest', $base_dir.'composer.json')
            ->addPath('base_dir_http', $base_dir.'www/')
            ->addPath('base_dir', $src_dir)
            ->addPath('root_dir', $base_dir);

        Helper::ensureDirectoryExists($base_dir.'user/');
        $this->addPath('user_dir', $base_dir.'user/');

        Helper::ensureDirectoryExists($base_dir.'tmp/');
        $this->addPath('tmp', $base_dir.'tmp/');

        Helper::ensureDirectoryExists($base_dir.'tmp/cache/');
        $this->addPath('cache', $base_dir.'tmp/cache/');

        $this->addPath('base_templates', $src_dir.'templates/');

        Helper::ensureDirectoryExists($base_dir.'user/templates/');
        $this->addPath('user_templates', $base_dir.'user/templates/');
    }

    protected function init()
    {
        // the docbook config (required)
        $docbook_cfgfile = $this->fallbackFinder(self::DOCBOOK_CONFIG, 'config');
        if (!empty($docbook_cfgfile)) {
            $this->registry->setConfig('docbook', parse_ini_file($docbook_cfgfile, true));
        } else {
            throw new Exception(
                sprintf('DocBook configuration file not found but is required (searching "%s")!', self::DOCBOOK_CONFIG)
            );
        }

        // the actual manifest
        $this->registry->setConfig('manifest', json_decode(file_get_contents($this->getPath('app_manifest')), true));

        // the markdown config (not required)
        $emd_cfgfile = $this->fallbackFinder(self::MARKDOWN_CONFIG, 'config');
        if (!empty($emd_cfgfile)) {
            $this->registry->setConfig('emd', parse_ini_file($emd_cfgfile, true));
        }

        // creating the Markdown parser
        $emd_cfg = $this->registry->getConfig('emd', array());
        foreach($emd_cfg as $name=>$val) {
            @define($name, $val);
        }
        $this->setMarkdownParser(new ExtraParser);

        // creating the application default headers
        $charset = $this->registry->get('html:charset', 'utf-8', 'docbook');
        $content_type = $this->registry->get('html:content-type', 'text/html', 'docbook');
        $this->addHeader('Content-type', $content_type.'; charset: '.$charset);
        $app_name = $this->registry->get('title', null, 'manifest');
        $app_version = $this->registry->get('version', null, 'manifest');
        $app_website = $this->registry->get('homepage', null, 'manifest');
        $this->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        
        // the template builder
        $this->setTemplateBuilder(new TemplateBuilder() );

        // some PHP configs
        @date_default_timezone_set( $this->registry->get('app:timezone', 'Europe/London', 'docbook') );

        $this->setInputPath($_SERVER['REQUEST_URI']);
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

        $page = $this->getPage();
        $result = !empty($page) ? $page->parse() : '';
        if (true===$return) {
            return $result;
        } else {
            $this->display($result);
        }
    }
    
    public function notFound()
    {
        $template = $this->getTemplate('not_found');
        if (!empty($template)) {
            $full_content = $this->getTemplateBuilder()->render($template);
        }
        $this->_renderHeaders();
        echo !empty($full_content) ? $full_content : 'Not found!';
        exit(0);
    }
    
    public function display($content = '')
    {
        $page = $this->getPage();
        $template = $this->getTemplate($page::$template_name);

        $path = $this->getInputPath();
        $breadcrumbs = array();
        if (!empty($path)) {
            $parts = explode('/', $path);
            $breadcrumbs = array_filter($parts);
        }

        $title = Helper::buildPageTitle($this->getInputFile());
        if (empty($title)) {
            if (!empty($breadcrumbs)) {
                $title = Helper::buildPageTitle(end($breadcrumbs));
            } else {
                $title = 'Home';
            }
        }

        if (!empty($template)) {
            $file = $this->getInputFile();
            if (empty($file)) {
                $path = $this->getInputPath();
                $file = Helper::findPathReadme(rtrim($this->getPath('base_dir_http'), '/').'/'.trim($path, '/'));
            }
            $update_time = !empty($file) ? Helper::getDateTimeFromTimestamp(filemtime($file)) : null;
            $params = array(
                'title'         => $title,
                'breadcrumbs'   => $breadcrumbs,
                'content'       => $content,
                'page'          => array(
                    'path'      => $file,
                    'update'    => $update_time
                ),
            );
            $full_content = $this->getTemplateBuilder()->render($template, $params);
        }

        $this->_renderHeaders();
        echo !empty($full_content) ? $full_content : $content;
    }

// ---------------------
// Setters / Getters
// ---------------------

    public function addPath($name, $value)
    {
        $realpath = realpath($value);
        if (!empty($realpath)) {
            $this->registry->setConfig($name, $realpath, 'paths');
            return $this;
        } else {
            throw new \RuntimeException(
                sprintf('Directory "%s" defined as an application path doesn\'t exist!', $value)
            );
        }
    }

    public function getPath($name)
    {
        return $this->registry->getConfig($name, null, 'paths');
    }

    public function setInputFile($path)
    {
        if (file_exists($path)) {
            $this->input_file = $path;
        } else {
            $this->uri = str_replace($this->getPath('base_dir_http'), '', $path);
        }
        return $this;
    }

    public function getInputFile()
    {
        return $this->input_file;
    }

    public function setInputPath($path)
    {
        $this->input_path = str_replace(basename($this->getInputFile()), '', $path);
        return $this;
    }

    public function getInputPath()
    {
        return $this->input_path;
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

    public function getRegistry()
    {
        return $this->registry;
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

    public function getTemplate($name)
    {
        return $this->registry->get('templates:'.$name, null, 'docbook');
    }

// ---------------------
// Fallback process
// ---------------------

    public function fallbackFinder($filename, $filetype = 'template')
    {
        $base_path = 'template'===$filetype ? self::TEMPLATES_DIR : self::CONFIG_DIR;
        $file_path = rtrim($base_path, '/').'/'.$filename;
        
        // user first
        $user_file_path = rtrim($this->getPath('user_dir'), '/').'/'.$file_path;
        if (file_exists($user_file_path)) {
            return $user_file_path;
        }

        // default
        $def_file_path = rtrim($this->getPath('base_dir'), '/').'/'.$file_path;
        if (file_exists($def_file_path)) {
            return $def_file_path;
        }

        // else false        
        return false;
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

        $file_path = rtrim($this->getPath('base_dir_http'), '/').'/'.implode('/', $parts);
        while(!file_exists($file_path) && count($parts)>0) {
            array_pop($parts);
            $file_path = rtrim($this->getPath('base_dir_http'), '/').'/'.implode('/', $parts);
        }

        if (count($parts)>0) {
            $this->setInputFile($file_path);
        }

        $diff = array_diff($original_parts, $parts);
        if (!empty($diff) && count($diff)===1) {
            $this->setPageType(array_shift($diff));
        }
    }
    
    protected function _createPageHandler($type = null, $file = null)
    {
        $cfg = $this->registry->getConfig('page_types', array(), 'docbook');
        $page_type = !is_null($type) ? $type : $this->getPageType();
        $input_file = !is_null($file) ? $file : $this->getInputFile();
        if (empty($input_file)) {
            $input_path = $this->getInputPath();
            if (!empty($input_path)) {
                $input_file = rtrim($this->getPath('base_dir_http'), '/').'/'.trim($input_path, '/');
            }
        }

        if (array_key_exists($page_type, $cfg)) {
            $_cls = 'DocBook\\Page\\'.ucfirst($cfg[$page_type]);
            $this->setPage( new $_cls($input_file) );
        } else {
            $this->notFound();
        }
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
