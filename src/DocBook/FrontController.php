<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\Abstracts\AbstractFrontController,
    DocBook\Abstracts\AbstractPage,
    DocBook\Locator,
    DocBook\NotFoundException,
    DocBook\DocBookException,
    DocBook\DocBookRuntimeException;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
class FrontController extends AbstractFrontController
{

    const DOCBOOK_INTERFACE = 'docbook.php';
    const MARKDOWN_CONFIG = 'markdown.ini';
    const DOCBOOK_CONFIG = 'docbook.ini';
    const APP_MANIFEST = 'composer.json';

    const USER_DIR = 'user';
    const TEMPLATES_DIR = 'templates';
    const CONFIG_DIR = 'config';

    protected $params = array();
    protected $input_file;
    protected $input_path;
    protected $page_type;

    // dependences
    protected $page_handler;
    protected $template_builder;
    protected $markdown_parser;

    protected function __construct()
    {
        parent::__construct();

        $src_dir = __DIR__.'/../';
        $base_dir = $src_dir.'../';

        $this
            ->addPath('app_manifest', $base_dir.self::APP_MANIFEST)
            ->addPath('base_dir_http', $base_dir.'www/')
            ->addPath('base_dir', $src_dir)
            ->addPath('root_dir', $base_dir);

        Helper::ensureDirectoryExists($base_dir.'tmp/');
        $this->addPath('tmp', $base_dir.'tmp/');

        Helper::ensureDirectoryExists($base_dir.'tmp/cache/');
        $this->addPath('cache', $base_dir.'tmp/cache/');

        $this->addPath('base_templates', $src_dir.self::TEMPLATES_DIR);

        Helper::ensureDirectoryExists($base_dir.self::USER_DIR);
        $this->addPath('user_dir', $base_dir.self::USER_DIR);

        Helper::ensureDirectoryExists($base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR);
        $this->addPath('user_templates', $base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR);
    }

    protected function init()
    {
        $locator = new Locator;
        
        // the docbook config (required)
        $docbook_cfgfile = $locator->fallbackFinder(self::DOCBOOK_CONFIG, 'config');
        if (!empty($docbook_cfgfile)) {
            $this->registry->setConfig('docbook', parse_ini_file($docbook_cfgfile, true));
        } else {
            throw new DocBookException(
                sprintf('DocBook configuration file not found but is required (searching "%s")!', self::DOCBOOK_CONFIG)
            );
        }

        // the actual manifest
        $this->registry->setConfig('manifest', json_decode(file_get_contents($this->getPath('app_manifest')), true));

        // the markdown config (not required)
        $emd_cfgfile = $locator->fallbackFinder(self::MARKDOWN_CONFIG, 'config');
        if (!empty($emd_cfgfile)) {
            $this->registry->setConfig('emd', parse_ini_file($emd_cfgfile, true));
        }

        // creating the application default headers
        $charset = $this->registry->get('html:charset', 'utf-8', 'docbook');
        $content_type = $this->registry->get('html:content-type', 'text/html', 'docbook');
        $this->response->addHeader('Content-type', $content_type.'; charset: '.$charset);
        $app_name = $this->registry->get('title', null, 'manifest');
        $app_version = $this->registry->get('version', null, 'manifest');
        $app_website = $this->registry->get('homepage', null, 'manifest');
        $this->response->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        
        // the template builder
        $this->setTemplateBuilder(new TemplateBuilder);

        // some PHP configs
        @date_default_timezone_set( $this->registry->get('app:timezone', 'Europe/London', 'docbook') );
    }

    public function distribute($return = false)
    {
        $this->request->parseDocBookRequest();
        $this->_createPageHandler($this->getPageType());
        $page = $this->getPage();
        $result = !empty($page) ? $page->parse() : '';

        // for dev
        if (!empty($_GET) && isset($_GET['dbg'])) {
            $this->debug();
        }

        if (true===$return) {
            return $result;
        } else {
            $this->display($result);
        }
    }
    
    public function notFound($str = '')
    {
        $template = $this->getTemplate('not_found');
        if (!empty($template)) {
            $full_content = $this->getTemplateBuilder()->render($template, array(
                'message'=>$str
            ));
        }
        $this->response->send(!empty($full_content) ? $full_content : 'Not found!');
    }
    
    public function display($content = '')
    {
        $page = $this->getPage();
        $template = $this->getTemplate($page::$template_name);

        $path = $this->getInputFile();
        $breadcrumbs = array();
        if (!empty($path)) {
            $parts = explode('/', str_replace($this->getPath('base_dir_http'), '', $path));
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
                $file = Locator::findPathReadme(rtrim($this->getPath('base_dir_http'), '/').'/'.trim($path, '/'));
            }
            $update_time = !empty($file) ? Helper::getDateTimeFromTimestamp(filemtime($file)) : null;
            $params = array(
                'title'         => $title,
                'breadcrumbs'   => $breadcrumbs,
                'content'       => $content,
                'page'          => array(
                    'name'      => basename($file),
                    'path'      => $file,
                    'update'    => $update_time
                ),
            );
            $full_content = $this->getTemplateBuilder()->render($template, $params);
        }
        $this->response->send(!empty($full_content) ? $full_content : $content);
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
            throw new DocBookRuntimeException(
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
        $this->input_file = $path;
        return $this;
    }

    public function getInputFile()
    {
        return $this->input_file;
    }

    public function setInputPath($path)
    {
        $this->input_path = $path;
        return $this;
    }

    public function getInputPath()
    {
        return $this->input_path;
    }

    public function setQueryString($uri)
    {
        $this->uri = $uri;
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
        if (empty($this->markdown_parser)) {
            // creating the Markdown parser
            $emd_cfg = $this->registry->getConfig('emd', array());
            foreach($emd_cfg as $name=>$val) {
                @define($name, $val);
            }
            $this->setMarkdownParser(new ExtraParser);
        }
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
// Process
// ---------------------

    protected function _createPageHandler($type = null, $file = null)
    {
        $cfg = $this->registry->getConfig('page_types', array(), 'docbook');
        $original_page_type = !is_null($type) ? $type : $this->getPageType();
        $page_type = !empty($original_page_type) ? $original_page_type : 'default';
        $input_file = !is_null($file) ? $file : $this->getInputFile();
        if (empty($input_file)) {
            $input_path = $this->getInputPath();
            if (!empty($input_path)) {
                $input_file = rtrim($this->getPath('base_dir_http'), '/').'/'.trim($input_path, '/');
            }
        }

        $locator = new Locator;
        $page_action_cls = $locator->getPageAction($page_type);
        if ($page_action_cls) {
            $this->setPage( new $page_action_cls($input_file) );
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
