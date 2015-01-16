<?php
/**
 * This file is part of the WebDocBook package.
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook\Abstracts;

use \WebDocBook\HttpFundamental\Response;
use \WebDocBook\HttpFundamental\Request;
use \WebDocBook\TemplateBuilder;
use \Library\Command;
use \Library\Session\FlashSession;
use \MarkdownExtended\MarkdownExtended;
use \Patterns\Abstracts\AbstractSingleton;

/**
 * Class AbstractFrontController
 *
 * Base of the \WebDocBook\FrontController
 */
abstract class AbstractFrontController
    extends AbstractSingleton
{

    /**
     * @var array
     */
    protected $routing = array();

    /**
     * @var \WebDocBook\HttpFundamental\Request
     */
    protected $request;

    /**
     * @var \WebDocBook\HttpFundamental\Response
     */
    protected $response;

    /**
     * @var \WebDocBook\Controller\mixed
     */
    protected $controller;

    /**
     * @var \WebDocBook\TemplateBuilder
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
            ->setResponse(new Response)
            ->setRequest(new Request)
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
     * @param \WebDocBook\HttpFundamental\Response $response
     * @return $this
     * @access private
     */
    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \WebDocBook\HttpFundamental\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \WebDocBook\HttpFundamental\Request $request
     * @return $this
     * @access private
     */
    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \WebDocBook\HttpFundamental\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \WebDocBook\Abstracts\AbstractController $ctrl
     * @return $this
     */
    public function setController(AbstractController $ctrl)
    {
        $this->controller = $ctrl;
        return $this;
    }

    /**
     * @return \WebDocBook\Abstracts\AbstractController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param \WebDocBook\TemplateBuilder $builder
     * @return $this
     */
    public function setTemplateBuilder(TemplateBuilder $builder)
    {
        $this->template_builder = $builder;
        return $this;
    }

    /**
     * @return \WebDocBook\TemplateBuilder
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
