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
use \DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator;
use \DocBook\Exception\Exception;
use \DocBook\Exception\RuntimeException;
use \MarkdownExtended\MarkdownExtended;
use \I18n\I18n;
use \I18n\Loader as I18n_Loader;
use \I18n\Twig\I18nExtension as I18n_Twig_Extension;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Logger;

/**
 * Class FrontController
 */
class FrontController
    extends AbstractFrontController
{

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
     * @return void
     */
    protected function boot()
    {
        try {
            Kernel::boot();
//            Kernel::debug();

            $this->session->start();

            // the docbook config (required)
            $config_file = Kernel::getPath('app_config');
            if (file_exists($config_file)) {
                $config =  parse_ini_file($config_file, true);
                if ($config) {
                    $this->setConfig('docbook', $config, null);
                    Kernel::parseConfig($config);
                } else {
                    throw new Exception(
                        sprintf('DocBook configuration file "%s" seems malformed!', $config_file)
                    );
                }
            } else {
                throw new Exception(
                    sprintf('DocBook configuration file not found but is required (searching "%s")!', $config_file)
                );
            }

            // the actual manifest
            $manifest_ctt = file_get_contents(Kernel::getPath('app_manifest'));
            if ($manifest_ctt!==false) {
                $manifest_data = json_decode($manifest_ctt, true);
                if ($manifest_data) {
                    $this->setConfig('manifest', $manifest_data, null);
                } else {
                    throw new \Exception(
                        sprintf('Can not parse app manifest "%s" JSON content!', Kernel::getPath('app_manifest'))
                    );
                }
            } else {
                throw new \Exception(
                    sprintf('App manifest "%s" not found or is empty!', Kernel::getPath('app_manifest'))
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
     * @throws \DocBook\Exception\Exception
     */
    protected function init($config_file)
    {
        // DocBook booting
        $this->boot($config_file);

        // the logger
        $this->logger = new Logger(array(
            'directory'     => Kernel::getPath('log'),
            'logfile'       => $this->getConfig('app:logfile', 'history'),
            'error_logfile' => $this->getConfig('app:error_logfile', 'errors'),
            'duplicate_errors' => false,
        ));

        // user configuration
        $internal_config    = $this->getConfig('userconf', array());
        $user_config_file   = $this->getLocator()->getUserConfigPath();
        if (file_exists($user_config_file)) {
            $user_config = parse_ini_file($user_config_file, true);
            if (!empty($user_config)) {
                $this->setConfig('user_config', $user_config);
            } else {
                throw new Exception(
                    sprintf('Can not read you configuration file "%s"!', $user_config_file)
                );
            }
        } else {
            $this->setConfig('user_config', $internal_config);
        }

        // creating the application default headers
        $charset        = $this->getConfig('html:charset', 'utf-8');
        $content_type   = $this->getConfig('html:content-type', 'text/html');
        $app_name       = $this->getConfig('title', null, 'manifest');
        $app_version    = $this->getConfig('version', null, 'manifest');
        $app_website    = $this->getConfig('homepage', null, 'manifest');
        $this->response->addHeader('Content-type', $content_type.'; charset: '.$charset);

        // expose app ?
        $expose_docbook = $this->getConfig('app:expose_docbook', true);
        if (true===$expose_docbook || 'true'===$expose_docbook || '1'===$expose_docbook) {
            $this->response->addHeader('Composed-by', $app_name.' '.$app_version.' ('.$app_website.')');
        }

        // the template builder
        $this->setTemplateBuilder(new TemplateBuilder);

        // some PHP configs
        @date_default_timezone_set( $this->getConfig('user_config:timezone', 'Europe/London') );
        
        // the internationalization
        $langs = $this->getConfig('languages:langs', array('en'=>'English'));
        $i18n_loader_opts = array(
            'language_directory'            => Kernel::getPath('i18n'),
            'language_strings_db_directory' => Kernel::getPath('user_config'),
            'language_strings_db_filename'  => Kernel::getPath('app_i18n'),
            'force_rebuild'                 => true,
            'available_languages'           => array_combine(array_keys($langs), array_keys($langs)),
        );
        if (Kernel::isDevMode()) {
            $i18n_loader_opts['show_untranslated'] = true;
        }
        $translator = I18n::getInstance(new I18n_Loader($i18n_loader_opts));

        // language
        $def_ln = $this->getConfig('languages:default', 'auto');
        if (!empty($def_ln) && $def_ln==='auto') {
            $translator->setDefaultFromHttp();
            $def_ln = $this->getConfig('languages:fallback_language', 'en');
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
     * @throws \DocBook\Exception\NotFoundException
     * @throws \DocBook\Exception\RuntimeException if the controller action does not return a valid array
     * @return void
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
                $input_file = Kernel::getPath('web').trim($input_path, '/');
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
            throw new RuntimeException(
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
        if ($this->getRequest()->isAjax()) {
            $this->response->setContentType('json', true);
            $full_content = array_merge($params, array('body' => $full_content));
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
                    $langs = $this->getConfig('languages:langs', array('en'=>'English'));
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
            $emd_config = $this->getConfig('markdown', array());
            $emd_config_strs = $this->getConfig('markdown_i18n', array());
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
        return $this->getConfig('templates:'.$name, null);
    }

    /**
     * @return array
     */
    public function getChapters()
    {
        $www_http = Kernel::getPath('web');
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
