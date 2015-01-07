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
use \DocBook\Response;
use \DocBook\Request;
use \DocBook\TemplateBuilder;
use \Library\Command;
use \MarkdownExtended\MarkdownExtended;
use \Patterns\Abstracts\AbstractSingleton;
use \Patterns\Commons\ConfigurationRegistry;

/**
 * Class AbstractFrontController
 *
 * Base of the \DocBook\FrontController
 *
 * @package DocBook\Abstracts
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
     * @var \DocBook\Request
     */
    protected $request;

    /**
     * @var \DocBook\Response
     */
    protected $response;

    /**
     * @var \DocBook\Controller
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
     * @param \DocBook\Response $response
     * @return $this
     * @access private
     */
    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response
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
     * @param \DocBook\Request $request
     * @return $this
     * @access private
     */
    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \DocBook\Request
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
     * @return \Patterns\Commons\ConfigurationRegistry
     */
    public function getRegistry()
    {
        return $this->registry;
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
    abstract public function setMarkdownParser(MarkdownExtended $parser);
    abstract public function getMarkdownParser();

}

// Endfile
