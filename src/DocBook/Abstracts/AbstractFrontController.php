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
 */
abstract class AbstractFrontController
    extends AbstractSingleton
{

    // dependences
    protected $routing = array();
    protected $locator;
    protected $registry;
    protected $request;
    protected $response;
    protected $controller;
    protected $template_builder;
    protected $terminal;

// ------------------
// Dependencies init
// ------------------

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

// ------------------
// Dependencies getters/setters
// ------------------

    /**
     * @access private
     */
    private function setTerminal(Command $terminal)
    {
        $this->terminal = $terminal;
        return $this;
    }

    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * @access private
     */
    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @access private
     */
    private function setLocator(Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @access private
     */
    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @access private
     */
    private function setRegistry(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function setController(AbstractController $ctrl)
    {
        $this->controller = $ctrl;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
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

// ------------------
// Abstracts
// ------------------

    abstract public function distribute();
    abstract public function setInputFile($path);
    abstract public function getInputFile();
    abstract public function setInputPath($path);
    abstract public function getInputPath();
    abstract public function setQuery(array $uri);
    abstract public function getQuery();
    abstract public function setAction($action);
    abstract public function getAction();
    abstract public function setMarkdownParser(MarkdownExtended $parser);
    abstract public function getMarkdownParser();

}

// Endfile
