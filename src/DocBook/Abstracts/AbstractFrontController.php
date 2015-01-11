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

namespace DocBook\Abstracts;

use \DocBook\Locator;
use \DocBook\HttpFundamental\Response;
use \DocBook\HttpFundamental\Request;
use \DocBook\TemplateBuilder;
use \Library\Command;
use \Library\Session\FlashSession;
use \MarkdownExtended\MarkdownExtended;
use \Patterns\Abstracts\AbstractSingleton;
use \Patterns\Commons\ConfigurationRegistry;

/**
 * Class AbstractFrontController
 *
 * Base of the \DocBook\FrontController
 */
abstract class AbstractFrontController
    extends AbstractSingleton
{

    /**
     * @var array
     */
    protected $routing = array();

    /**
     * @var \DocBook\Locator
     */
    protected $locator;

    /**
     * @var \Patterns\Commons\ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var \DocBook\HttpFundamental\Request
     */
    protected $request;

    /**
     * @var \DocBook\HttpFundamental\Response
     */
    protected $response;

    /**
     * @var \DocBook\Controller\misc
     */
    protected $controller;

    /**
     * @var \DocBook\TemplateBuilder
     */
    protected $template_builder;

    /**
     * @var \Library\Command
     */
    protected $terminal;

    /**
     * @var \MarkdownExtended\MarkdownExtended
     */
    protected $markdown_parser;

    /**
     * @var \Library\Session\FlashSession
     */
    protected $session;

    /**
     * Construction: init dependencies
     */
    protected function __construct()
    {
        $this
            ->setRegistry(new ConfigurationRegistry)
            ->setResponse(new Response)
            ->setRequest(new Request)
            ->setLocator(new Locator)
            ->setTerminal(new Command)
            ->setSession(new FlashSession)
        ;
    }

    /**
     * @param \Library\Command $terminal
     * @return $this
     * @access private
     */
    private function setTerminal(Command $terminal)
    {
        $this->terminal = $terminal;
        return $this;
    }

    /**
     * @return \Library\Command
     */
    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * @param \DocBook\HttpFundamental\Response $response
     * @return $this
     * @access private
     */
    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \DocBook\HttpFundamental\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \DocBook\Locator $locator
     * @return $this
     * @access private
     */
    private function setLocator(Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * @return \DocBook\Locator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @param \DocBook\HttpFundamental\Request $request
     * @return $this
     * @access private
     */
    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \DocBook\HttpFundamental\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Patterns\Commons\ConfigurationRegistry $registry
     * @return $this
     * @access private
     */
    private function setRegistry(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * @return \DocBook\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param null $name
     * @param null $default
     * @param string $scope
     * @return array|mixed|null
     */
    public function getConfig($name = null, $default = null, $scope = 'docbook')
    {
        if (is_null($name)) {
            return $this->registry->getConfigs();
        } else {
            return $this->registry->get($name, $default, $scope);
        }
    }

    /**
     * @param $name
     * @param $value
     * @param string $scope
     * @return mixed
     */
    public function setConfig($name, $value, $scope = 'docbook')
    {
        return $this->registry->set($name, $value, $scope);
    }

    /**
     * @param \DocBook\Abstracts\AbstractController $ctrl
     * @return $this
     */
    public function setController(AbstractController $ctrl)
    {
        $this->controller = $ctrl;
        return $this;
    }

    /**
     * @return \DocBook\Abstracts\AbstractController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param \DocBook\TemplateBuilder $builder
     * @return $this
     */
    public function setTemplateBuilder(TemplateBuilder $builder)
    {
        $this->template_builder = $builder;
        return $this;
    }

    /**
     * @return \DocBook\TemplateBuilder
     */
    public function getTemplateBuilder()
    {
        return $this->template_builder;
    }

    /**
     * @param \MarkdownExtended\MarkdownExtended $parser
     * @return $this
     */
    public function setMarkdownParser(MarkdownExtended $parser)
    {
        $this->markdown_parser = $parser;
        return $this;
    }

    /**
     * @return \MarkdownExtended\MarkdownExtended
     */
    public function getMarkdownParser()
    {
        return $this->markdown_parser;
    }

    /**
     * @param \Library\Session\FlashSession $session
     * @return $this
     * @access private
     */
    private function setSession(FlashSession $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return \Library\Session\FlashSession
     */
    public function getSession()
    {
        return $this->session;
    }

// ------------------
// Abstracts
// ------------------

    /**
     * @return void
     */
    abstract public function distribute();

    /**
     * @param string $path
     * @return $this
     */
    abstract public function setInputFile($path);

    /**
     * @return string
     */
    abstract public function getInputFile();

    /**
     * @param string $path
     * @return $this
     */
    abstract public function setInputPath($path);

    /**
     * @return string
     */
    abstract public function getInputPath();

    /**
     * @param array $uri
     * @return $this
     */
    abstract public function setQuery(array $uri);

    /**
     * @return array
     */
    abstract public function getQuery();

    /**
     * @param string $action
     * @return $this
     */
    abstract public function setAction($action);

    /**
     * @return string
     */
    abstract public function getAction();

}

// Endfile
