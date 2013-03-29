<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\Abstracts\AbstractFrontController,
    DocBook\Abstracts\AbstractPage,
    DocBook\Locator,
    DocBook\NotFoundException,
    DocBook\DocBookException,
    DocBook\DocBookRuntimeException,
    DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
class FrontController extends AbstractFrontController
{

    const DOCBOOK_ASSETS = 'docbook_assets';
    const DOCBOOK_INTERFACE = 'index.php';
    const MARKDOWN_CONFIG = 'markdown.ini';
    const DOCBOOK_CONFIG = 'docbook.ini';
    const APP_MANIFEST = 'composer.json';

    const USER_DIR = 'user';
    const TEMPLATES_DIR = 'templates';
    const CONFIG_DIR = 'config';

    const README_FILE = 'README.md';
    const INDEX_FILE = 'INDEX.md';
    const ASSETS_DIR = 'assets';
    const WIP_DIR = 'wip';

    // dependences
    protected $input_file;
    protected $input_path;
    protected $action;
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
        // the docbook config (required)
        $docbook_cfgfile = $this->locator->fallbackFinder(self::DOCBOOK_CONFIG, 'config');
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
        $emd_cfgfile = $this->locator->fallbackFinder(self::MARKDOWN_CONFIG, 'config');
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
        $routing = $this->request
            ->parseDocBookRequest()
            ->getDocBookRouting();

        $input_file = $this->getInputFile();
        if (empty($input_file)) {
            $input_path = $this->getInputPath();
            if (!empty($input_path)) {
                $input_file = Helper::slashDirname($this->getPath('base_dir_http')).trim($input_path, '/');
            }
        }
                
        $result = null;
        if (!empty($routing)) {
            $ctrl_cls = $routing['controller_classname'];
            $ctrl_obj = new $ctrl_cls();
            $this->setController($ctrl_obj);
            $result = Helper::fetchArguments(
                $this->getController(), $routing['action'], array('path'=>$input_file)
            );
        }
        if (empty($result) || !is_array($result) || (count($result)!=2 && count($result)!=3)) {
            $str = gettype($result);
            if (is_array($result)) $str .= ' length '.count($result);
            throw new DocBookRuntimeException(
                sprintf('A controller action must return a two or three entries array like [ template file , content (, params) ]! Received %s from class "%s::%s()".',
                $str, $routing['controller_classname'], $routing['action'])
            );
        } else {
            $template = $result[0];
            $content = $result[1];
            $params = isset($result[2]) ? $result[2] : array();
        }

        // for dev
        if (!empty($_GET) && isset($_GET['dbg'])) {
            $this->debug();
        }

        $this->display($content, $template, $params, true);
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
    
    public function display($content = '', $template_name = null, array $params = array(), $send = false)
    {
        $template = $this->getTemplate($template_name);
        $full_params = array_merge($params, array(
            'content' => $content,
        ));
        if ($template_name==='default') {
            $full_params['profiler'] = Helper::getProfiler();
        }
        $full_content = $this->getTemplateBuilder()->render($template, $full_params);

        if (Request::isAjax()) {
            $this->response->setContentType('json', true);
            $full_content = array_merge($params, array('body' => $full_content));
            $full_content = json_encode($full_content);
        }
        if ($send) {
            $this->response->send(!empty($full_content) ? $full_content : $content);
        } else {
            return !empty($full_content) ? $full_content : $content;
        }
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
    
    public function setAction($type)
    {
        $this->action = $type;
        return $this;
    }

    public function getAction()
    {
        return $this->action;
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

    public function getTemplate($name)
    {
        return $this->registry->get('templates:'.$name, null, 'docbook');
    }

    public function getChapters()
    {
        $www_http = $this->getPath('base_dir_http');
        $dir = new DocBookRecursiveDirectoryIterator($www_http);
        $paths = array();
        foreach($dir as $file) {
            if ($file->isDir()) {
                $paths[] = array(
                    'path'      =>Helper::getSecuredRealpath($file->getRealPath()),
                    'route'     =>Helper::getRoute($file->getRealPath()),
                    'name'      =>$file->getHumanReadableFilename(),
                );
            }
        }
        return $paths;
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
