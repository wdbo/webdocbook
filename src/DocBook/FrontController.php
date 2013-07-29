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

use MarkdownExtended\MarkdownExtended;

use I18n\I18n,
    I18n\Loader as I18n_Loader,
    I18n\Twig\I18nExtension as I18n_Twig_Extension;

use Library\Helper\Directory as DirectoryHelper;

/**
 */
class FrontController extends AbstractFrontController
{

    const DOCBOOK_ASSETS = 'docbook_assets';
    const DOCBOOK_INTERFACE = 'index.php';
    const MARKDOWN_CONFIG = 'markdown.ini';
    const DOCBOOK_CONFIG = 'docbook.ini';
    const APP_MANIFEST = 'composer.json';
    const APP_I18N = 'docbook_i18n.csv';

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
        session_start();
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

        Helper::ensureDirectoryExists($base_dir.'tmp/i18n/');
        $this->addPath('i18n', $base_dir.'tmp/i18n/');

        $this->addPath('base_templates', $src_dir.self::TEMPLATES_DIR);

        if (file_exists($base_dir.self::USER_DIR)) {
            $this->addPath('user_dir', $base_dir.self::USER_DIR);
            if (file_exists($base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR))
                $this->addPath('user_templates', $base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR);
        }
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
        
        // expose app ?
        $expose_docbook = $this->registry->get('app:expose_docbook', true, 'docbook');
        if (true===$expose_docbook || 'true'===$expose_docbook || '1'===$expose_docbook)
            $this->response->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        
        // the template builder
        $this->setTemplateBuilder(new TemplateBuilder);

        // some PHP configs
        @date_default_timezone_set( $this->registry->get('app:timezone', 'Europe/London', 'docbook') );
        
        // the internationalization
        $langs = $this->registry->get('languages:langs', array('en'=>'English'), 'docbook');
        $i18n_loader = new I18n_Loader(array(
            'language_directory' => $this->getPath('i18n'),
            'language_strings_db_directory' =>
                DirectoryHelper::slashDirname($this->getPath('base_dir')).self::CONFIG_DIR,
            'language_strings_db_filename' => self::APP_I18N,
            'force_rebuild' => true,
            'available_languages' => array_combine(array_keys($langs), array_keys($langs)),
        ));
        $translator = I18n::getInstance($i18n_loader);

        // language
        $def_ln = $this->registry->get('languages:default', 'auto', 'docbook');
        if (!empty($def_ln) && $def_ln==='auto') {
            $translator->setDefaultFromHttp();
            $def_ln = $this->registry->get('languages:fallback_language', 'en', 'docbook');
        }
        $trans_ln = $translator->getLanguage();
        if (empty($trans_ln)) {
            $translator->setLanguage($def_ln);
        }

        $this->getTemplateBuilder()->getTwigEngine()->addExtension(new I18n_Twig_Extension($translator)); 
    }

    public function distribute($return = false)
    {
        $this->processSessionValues();
        
        $routing = $this->request
            ->parseDocBookRequest()
            ->getDocBookRouting();

        $this->processQueryArguments();

        $input_file = $this->getInputFile();
        if (empty($input_file)) {
            $input_path = $this->getInputPath();
            if (!empty($input_path)) {
                $input_file = DirectoryHelper::slashDirname($this->getPath('base_dir_http')).trim($input_path, '/');
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
    
    public function display($content = '', $template_name = null, array $params = array(), $send = false)
    {
        $template = $this->getTemplate($template_name);
        $full_params = array_merge($params, array(
            'content' => $content,
        ));
        if (in_array($template_name, array('default', 'not_found', 'forbidden'))) {
            $full_params['profiler'] = Helper::getProfiler();
        }
        $full_content = $this->getTemplateBuilder()->render($template, $full_params);

        if (Request::isAjax()) {
            $this->response->setContentType('json', true);
            $full_content = array_merge($params, array('body' => $full_content));
            $full_content = $full_content;
        }
        if ($send) {
            $this->response->send(!empty($full_content) ? $full_content : $content);
        } else {
            return !empty($full_content) ? $full_content : $content;
        }
    }

// ---------------------
// User settings
// ---------------------

    protected function processQueryArguments()
    {
        $args = $this->getQuery();
        if (!empty($args)) $this->parseUserSettings($args);
    }
    
    protected function processSessionValues()
    {
        if (!empty($_SESSION)) $this->parseUserSettings($_SESSION);
    }
    
    protected function parseUserSettings(array $args)
    {
        if (!empty($args)) {
            foreach ($args as $param=>$value) {
                
                if ($param==='lang') {
                    $langs = $this->registry->get('languages:langs', array('en'=>'English'), 'docbook');
/*
echo '<br />';
var_export($value);
var_export($langs);
*/
                    if (array_key_exists($value, $langs)) {
                        i18n::getInstance()->setLanguage($value);
                        $true_language = i18n::getInstance()->getLanguage();
//var_export($true_language);
                        if (!isset($_SESSION['lang']) || $_SESSION['lang']!==$true_language) {
                            $_SESSION['lang'] = $true_language;
                        }
                    }
                }
                
            }
        }

//var_export($args);
//exit('yo');
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

    public function setQuery(array $uri)
    {
        $this->uri = $uri;
        return $this;
    }
    
    public function getQuery()
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
    
    public function setMarkdownParser(MarkdownExtended $parser)
    {
        $this->markdown_parser = $parser;
        return $this;
    }
    
    public function getMarkdownParser()
    {
        if (empty($this->markdown_parser)) {
            // creating the Markdown parser
            $emd_config = $this->registry->getConfig('emd', array());
            if (empty($emd_config)) $emd_config = array();
            $translator = I18n::getInstance();
            $emd_config_strs = array(
                'link_mask_title', 'mailto_mask_title', 'fn_link_title',
                'fn_backlink_title', 'fng_link_title', 'fng_backlink_title',
                'fnc_link_title', 'fnc_backlink_title'
            );
            foreach ($emd_config_strs as $_str) {
                $emd_config[$_str] = $translator->translate($_str);
            }
            $this->setMarkdownParser(MarkdownExtended::create($emd_config));
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
