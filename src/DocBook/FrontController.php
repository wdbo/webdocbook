<?php
/**
 * This file is part of the DocBook package.
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
 * <http://github.com/atelierspierrot/docbook>.
 */

namespace DocBook;

use \DocBook\Abstracts\AbstractFrontController;
use \DocBook\Abstracts\AbstractPage;
use \DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator;
use \MarkdownExtended\MarkdownExtended;
use \I18n\I18n;
use \I18n\Loader as I18n_Loader;
use \I18n\Twig\I18nExtension as I18n_Twig_Extension;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Logger;

/**
 * Class FrontController
 *
 * This is the kernel of DocBook
 *
 * @package DocBook
 */
class FrontController
    extends AbstractFrontController
{

    /**
     * Name of the DocBook's manifest (composer.json)
     */
    const APP_MANIFEST = 'composer.json';

    /**
     * Name of the templates directory (src/templates/)
     */
    const TEMPLATES_DIR = 'templates';

    /**
     * Name of the configurations directory (src/config/)
     */
    const CONFIG_DIR = 'config';

    /**
     * Name of the directory of DocBook's assets (www/docbook_assets/)
     */
    const DOCBOOK_ASSETS = 'docbook_assets';

    /**
     * Name of the DocBook's web interface (www/index.php)
     */
    const DOCBOOK_INTERFACE = 'index.php';

    /**
     * Name of the Markdown configuration file
     * @TODO check if it really used ?
     */
    const MARKDOWN_CONFIG = 'markdown.ini';

    /**
     * Name of the DocBook's configuration file (src/config/docbook.ini)
     */
    const DOCBOOK_CONFIG = 'docbook.ini';

    /**
     * Name of the DocBook's languages file (src/config/docbook_i18n.csv)
     */
    const APP_I18N = 'docbook_i18n.csv';

    /**
     * Name of a special customization directory (user/)
     */
    const USER_DIR = 'user';

    /**
     * Default name of the README files for DocBook contents
     */
    const README_FILE = 'README.md';

    /**
     * Default name of the INDEX files for DocBook contents
     */
    const INDEX_FILE = 'INDEX.md';

    /**
     * Default name of the ASSETS sub-directory for DocBook contents
     */
    const ASSETS_DIR = 'assets';

    /**
     * Default name of the WORK-IN-PROGRESS sub-directory for DocBook contents
     */
    const WIP_DIR = 'wip';

    /**
     * @var string
     */
    protected $input_file;

    /**
     * @var string
     */
    protected $input_path;

    /**
     * @var array
     */
    protected $uri;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \MarkdownExtended\MarkdownExtended
     */
    protected $markdown_parser;

    /**
     * @var \Library\Logger
     */
    protected $logger;

    /**
     * Front controller protected constructor
     *
     * To actually build a FrontController instance, use:
     *
     *      FrontController::getInstance()
     *
     */
    protected function __construct()
    {
        session_start();
        parent::__construct();
    }

    protected function boot()
    {
        try {
            $src_dir    = DirectoryHelper::slashDirname(dirname(__DIR__));
            $base_dir   = DirectoryHelper::slashDirname(dirname($src_dir));
            $tmp_dir    = DirectoryHelper::slashDirname($base_dir.'tmp');
            Helper::ensureDirectoryExists($tmp_dir);
            if (!is_writable($tmp_dir)) {
                throw new \Exception("Directory '$tmp_dir' must be writable!");
            }
            $cache_dir  = DirectoryHelper::slashDirname($tmp_dir.'cache');
            Helper::ensureDirectoryExists($cache_dir);
            if (!is_writable($cache_dir)) {
                throw new \Exception("Directory '$cache_dir' must be writable!");
            }
            $i18n_dir   = DirectoryHelper::slashDirname($tmp_dir.'i18n');
            Helper::ensureDirectoryExists($i18n_dir);
            if (!is_writable($i18n_dir)) {
                throw new \Exception("Directory '$i18n_dir' must be writable!");
            }
            $log_dir    = DirectoryHelper::slashDirname($tmp_dir.'log');
            Helper::ensureDirectoryExists($log_dir);
            if (!is_writable($log_dir)) {
                throw new \Exception("Directory '$log_dir' must be writable!");
            }

            $this
                ->addPath('app_manifest', $base_dir.self::APP_MANIFEST)
                ->addPath('base_dir_http', $base_dir.'www/')
                ->addPath('base_dir', $src_dir)
                ->addPath('root_dir', $base_dir)
                ->addPath('tmp', $base_dir.'tmp/')
                ->addPath('cache', $base_dir.'tmp/cache/')
                ->addPath('i18n', $base_dir.'tmp/i18n/')
                ->addPath('logs', $base_dir.'tmp/log/')
                ->addPath('base_templates', $src_dir.self::TEMPLATES_DIR)
            ;

            if (file_exists($base_dir.self::USER_DIR)) {
                $this->addPath('user_dir', $base_dir.self::USER_DIR);
                if (file_exists($base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR)) {
                    $this->addPath('user_templates', $base_dir.self::USER_DIR.'/'.self::TEMPLATES_DIR);
                }
            }

        } catch (\Exception $e) {
            header('Content-Type: text/plain');
            echo PHP_EOL.'[DocBook startup error] : '.$e->getMessage().PHP_EOL;
            echo PHP_EOL.'-------------------------'.PHP_EOL;
            echo 'For more info, see the "INSTALL.md" file.'.PHP_EOL;
            exit(1);
        }
    }

    protected function init()
    {
        $this->boot();

        // the docbook config (required)
        $docbook_cfgfile = $this->locator->fallbackFinder(self::DOCBOOK_CONFIG, 'config');
        if (!empty($docbook_cfgfile)) {
            $this->registry->setConfig('docbook', parse_ini_file($docbook_cfgfile, true));
        } else {
            throw new DocBookException(
                sprintf('DocBook configuration file not found but is required (searching "%s")!', self::DOCBOOK_CONFIG)
            );
        }

        // the logger
        $this->logger = new Logger(array_merge(
            array('directory'=>$this->getPath('logs')),
            $this->registry->get('logger', array(), 'docbook')
        ));

        // the actual manifest
        $this->registry->setConfig('manifest', json_decode(file_get_contents($this->getPath('app_manifest')), true));

        // the markdown config (not required)
        $emd_cfgfile = $this->locator->fallbackFinder(self::MARKDOWN_CONFIG, 'config');
        if (!empty($emd_cfgfile)) {
            $this->registry->setConfig('markdown', parse_ini_file($emd_cfgfile, true));
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
        $i18n_loader_opts = array(
            'language_directory' => $this->getPath('i18n'),
            'language_strings_db_directory' =>
                DirectoryHelper::slashDirname($this->getPath('base_dir')).self::CONFIG_DIR,
            'language_strings_db_filename' => self::APP_I18N,
            'force_rebuild' => true,
            'available_languages' => array_combine(array_keys($langs), array_keys($langs)),
        );
        if (defined('DOCBOOK_MODE') && DOCBOOK_MODE==='dev') {
            $i18n_loader_opts['show_untranslated'] = true;
        }
        $translator = I18n::getInstance(new I18n_Loader($i18n_loader_opts));

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
        $this->getRequest()->setArguments($this->uri);
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
            $emd_config = $this->registry->getConfig('markdown', array(), 'docbook');
            $emd_config_strs = $this->registry->getConfig('markdown_i18n', array(), 'docbook');
            if (!empty($emd_config_strs) && is_array($emd_config_strs) && count($emd_config_strs)==1 && isset($emd_config_strs['markdown_i18n'])) {
                $emd_config_strs = $emd_config_strs['markdown_i18n'];
            }
            if (empty($emd_config)) $emd_config = array();
            $translator = I18n::getInstance();
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
                    'route'     =>Helper::getRoute($file->getDocBookPath()),
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

    public function log($message, $level = 'debug', array $context = array(), $logname = null)
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context, $logname);
        }
    }

}

// Endfile
