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
        parent::__construct();
    }

    /**
     * @param string $config_file
     * @return void
     */
    protected function boot($config_file)
    {
        try {
            $this->session->start();

            // the docbook config (required)
            if (file_exists($config_file)) {
                $config =  parse_ini_file($config_file, true);
                if ($config) {
                    $this->registry->setConfig('docbook', $config);
                } else {
                    throw new DocBookException(
                        sprintf('DocBook configuration file "%s" seems malformed!', $config_file)
                    );
                }
            } else {
                throw new DocBookException(
                    sprintf('DocBook configuration file not found but is required (searching "%s")!', $config_file)
                );
            }

            // the app paths
            $src_dir    = DirectoryHelper::slashDirname(dirname(__DIR__));
            $base_dir   = DirectoryHelper::slashDirname(dirname($src_dir));

            $tmp_dir    = DirectoryHelper::slashDirname(
                $base_dir . $this->getAppConfig('var_dir', 'tmp')
            );
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

            $web_dir    = DirectoryHelper::slashDirname(
                $base_dir . $this->getAppConfig('web_dir', 'www')
            );
            Helper::ensureDirectoryExists($web_dir);
            $templates_dir = DirectoryHelper::slashDirname(
                $src_dir . $this->getAppConfig('templates_dir', 'templates')
            );
            Helper::ensureDirectoryExists($templates_dir);

            $this
                ->addPath('root_dir', $base_dir)
                ->addPath('base_dir', $src_dir)
                ->addPath('app_manifest', $base_dir.self::APP_MANIFEST)
                ->addPath('base_dir_http', $web_dir)
                ->addPath('tmp', $tmp_dir)
                ->addPath('cache', $cache_dir)
                ->addPath('i18n', $i18n_dir)
                ->addPath('logs', $log_dir)
                ->addPath('base_templates', $templates_dir)
            ;

            // user dir fallback if so
            $user_dir = DirectoryHelper::slashDirname(
                $base_dir . $this->getAppConfig('user_dir', 'user')
            );
            if (file_exists($user_dir)) {
                $this->addPath('user_dir', $user_dir);
                $user_templates_dir = DirectoryHelper::slashDirname(
                    $user_dir . $this->getAppConfig('templates_dir', 'templates')
                );
                if (file_exists($user_templates_dir)) {
                    $this->addPath('user_templates', $user_templates_dir);
                }
            }

            // the actual manifest
            $manifest_ctt = file_get_contents($this->getPath('app_manifest'));
            if ($manifest_ctt!==false) {
                $manifest_data = json_decode($manifest_ctt, true);
                if ($manifest_data) {
                    $this->registry->setConfig('manifest', $manifest_data);
                } else {
                    throw new \Exception(
                        sprintf('Can not parse app manifest "%s" JSON content!', $this->getPath('app_manifest'))
                    );
                }
            } else {
                throw new \Exception(
                    sprintf('App manifest "%s" not found or is empty!', $this->getPath('app_manifest'))
                );
            }

        } catch (\Exception $e) {
            // hard die for startup errors
            header('Content-Type: text/plain');
            echo PHP_EOL.'[DocBook startup error] : '.PHP_EOL;
            echo PHP_EOL."\t".$e->getMessage().PHP_EOL;
            echo PHP_EOL.'-------------------------'.PHP_EOL;
            echo 'For more info, see the "INSTALL.md" file.'.PHP_EOL;
            exit(1);
        }
    }

    /**
     * @param string $config_file
     * @throws \DocBook\DocBookException
     */
    protected function init($config_file)
    {
        // DocBook booting
        $this->boot($config_file);

        // the logger
        $this->logger = new Logger(array(
            'directory'     => $this->getPath('logs'),
            'logfile'       => $this->registry->get('app:logfile', 'history', 'docbook'),
            'error_logfile' => $this->registry->get('app:error_logfile', 'errors', 'docbook'),
            'duplicate_errors' => false,
        ));

        // user configuration
        $internal_config = $this->registry->get('userconf', array(), 'docbook');
        $user_config_file =
            DirectoryHelper::slashDirname($this->getPath('root_dir'))
            .DirectoryHelper::slashDirname($this->getAppConfig('var_dir', 'tmp'))
            .$this->registry->get('app:user_config_file', '.docbook', 'docbook');
        if (file_exists($user_config_file)) {
            $user_config = parse_ini_file($user_config_file, true);
            if (!empty($user_config)) {
                $this->registry->set('user_config', $user_config, 'docbook');
            } else {
                throw new DocBookException(
                    sprintf('Can not read you configuration file "%s"!', $user_config_file)
                );
            }
        } else {
            $this->registry->set('user_config', $internal_config, 'docbook');
        }

        // creating the application default headers
        $charset        = $this->registry->get('html:charset', 'utf-8', 'docbook');
        $content_type   = $this->registry->get('html:content-type', 'text/html', 'docbook');
        $app_name       = $this->registry->get('title', null, 'manifest');
        $app_version    = $this->registry->get('version', null, 'manifest');
        $app_website    = $this->registry->get('homepage', null, 'manifest');
        $this->response->addHeader('Content-type', $content_type.'; charset: '.$charset);

        // expose app ?
        $expose_docbook = $this->registry->get('app:expose_docbook', true, 'docbook');
        if (true===$expose_docbook || 'true'===$expose_docbook || '1'===$expose_docbook) {
            $this->response->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        }

        // the template builder
        $this->setTemplateBuilder(new TemplateBuilder);

        // some PHP configs
        @date_default_timezone_set( $this->registry->get('user_config:timezone', 'Europe/London', 'docbook') );
        
        // the internationalization
        $langs = $this->registry->get('languages:langs', array('en'=>'English'), 'docbook');
        $i18n_loader_opts = array(
            'language_directory' => $this->getPath('i18n'),
            'language_strings_db_directory' =>
                DirectoryHelper::slashDirname($this->getPath('base_dir')).
                $this->getAppConfig('config_dir', 'config'),
            'language_strings_db_filename' => $this->getAppConfig('app_i18n', 'docbook_i18n.csv'),
            'force_rebuild' => true,
            'available_languages' => array_combine(array_keys($langs), array_keys($langs)),
        );
        if (self::isDevMode()) {
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

        $this->getTemplateBuilder()
            ->getTwigEngine()
            ->addExtension(new I18n_Twig_Extension($translator));
    }

    /**
     * @param $message
     * @param string $level
     * @param array $context
     * @param null $logname
     */
    public function log($message, $level = 'debug', array $context = array(), $logname = null)
    {
        if ($this->logger) {
            if (is_int($level)) {
                $level = 'error';
            }
            $this->logger->log($level, $message, $context, $logname);
        }
    }

    /**
     * @param bool $return
     * @throws \DocBook\NotFoundException
     * @throws \DocBook\DocBookRuntimeException if the controller action does not return a valid arrau
     */
    public function distribute($return = false)
    {
        $this->processSessionValues();

        try {
            $routing = $this->request
                ->parseDocBookRequest()
                ->getDocBookRouting();
        } catch (NotFoundException $e) {
            throw $e;
        }

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

    /**
     * @param string $content
     * @param null $template_name
     * @param array $params
     * @param bool $send
     * @return array|string
     */
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

    /**
     * @return $this
     */
    protected function processQueryArguments()
    {
        $args = $this->getQuery();
        if (!empty($args)) {
            $this->parseUserSettings($args);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function processSessionValues()
    {
        if (!empty($_SESSION)) {
            $this->parseUserSettings($_SESSION);
        }
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
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
        return $this;
    }

// ---------------------
// Setters / Getters
// ---------------------

    /**
     * @return bool
     */
    public static function isDevMode()
    {
        return (defined('DOCBOOK_MODE') && DOCBOOK_MODE==='dev');
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAppConfig($name, $default)
    {
        $stack = $this->registry->getConfig('app', array(), 'docbook');
        return (isset($stack[$name]) ? $stack[$name] : $default);
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addPath($name, $value)
    {
        $realpath = realpath($value);
        if (!empty($realpath)) {
            $this->registry->setConfig($name, $realpath, 'paths');
        } else {
            throw new DocBookRuntimeException(
                sprintf('Directory "%s" defined as an application path doesn\'t exist!', $value)
            );
        }
        return $this;
    }

    /**
     * @param $name
     * @return mixed|\Patterns\Commons\misc
     */
    public function getPath($name)
    {
        return $this->registry->getConfig($name, null, 'paths');
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setInputFile($path)
    {
        $this->input_file = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputFile()
    {
        return $this->input_file;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setInputPath($path)
    {
        $this->input_path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputPath()
    {
        return $this->input_path;
    }

    /**
     * @param array $uri
     * @return $this
     */
    public function setQuery(array $uri)
    {
        $this->uri = $uri;
        $this->getRequest()->setArguments($this->uri);
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->uri;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setAction($type)
    {
        $this->action = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return MarkdownExtended
     */
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

    /**
     * @param $name
     * @return mixed|\Patterns\Commons\misc
     */
    public function getTemplate($name)
    {
        return $this->registry->get('templates:'.$name, null, 'docbook');
    }

    /**
     * @return array
     */
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

}

// Endfile
