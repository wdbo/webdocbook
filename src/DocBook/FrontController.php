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
                $input_file = rtrim($this->getPath('base_dir_http'), '/').'/'.trim($input_path, '/');
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
        if (empty($result) || !is_array($result) || count($result)!=2) {
            $str = gettype($result);
            if (is_array($result)) $str .= ' length '.count($result);
            throw new DocBookRuntimeException(
                sprintf('A controller action must return a two entries array like [ template file , content ]! Received %s from class "%s::%s()".',
                $str, $routing['controller_classname'], $routing['action'])
            );
        } else {
            $template = $result[0];
            $content = $result[1];
        }

        // for dev
        if (!empty($_GET) && isset($_GET['dbg'])) {
            $this->debug();
        }

        $this->display($content, $template);
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
    
    public function display($content = '', $template_name = null)
    {
        $path = $this->getInputFile();
        $breadcrumbs = Helper::getBreadcrumbs($path);
        $title = Helper::buildPageTitle($this->getInputFile());
        if (empty($title)) {
            if (!empty($breadcrumbs)) {
                $title = Helper::buildPageTitle(end($breadcrumbs));
            } else {
                $title = 'Home';
            }
        }

        $template = $this->getTemplate($template_name);
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

        if (Request::isAjax()) {
            $this->response->setContentType('json');
            $full_content = is_array($full_content) ? $full_content : array('body' => $full_content);
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
