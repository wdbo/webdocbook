<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

use DocBook\Abstracts\AbstractPage,
    DocBook\Locator,
    DocBook\Response,
    DocBook\Request,
    DocBook\TemplateBuilder;

use Markdown\Parser,
    Markdown\ExtraParser;

use Patterns\Abstracts\AbstractSingleton,
    Patterns\Commons\ConfigurationRegistry;

/**
 */
abstract class AbstractFrontController extends AbstractSingleton
{

    // dependences
    protected $registry;
    protected $request;
    protected $response;

// ------------------
// Dependencies init
// ------------------

    protected function __construct()
    {
        $this
            ->setRegistry(new ConfigurationRegistry)
            ->setResponse(new Response)
            ->setRequest(new Request);
    }

// ------------------
// Dependencies getters/setters
// ------------------

    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getResquest()
    {
        return $this->request;
    }

    private function setRegistry(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

// ------------------
// Abstracts
// ------------------

    abstract public function distribute();

    abstract public function setInputFile($path);
    abstract public function getInputFile();
    abstract public function setQueryString($uri);
    abstract public function getQueryString();
    abstract public function setParams(array $params);
    abstract public function addParam($name, $value = null);
    abstract public function getParams();
    abstract public function getParam($name);
    abstract public function setPage(AbstractPage $page);
    abstract public function getPage();
    abstract public function setPageType($type);
    abstract public function getPageType();
    abstract public function setMarkdownParser(ExtraParser $parser);
    abstract public function getMarkdownParser();
    abstract public function setTemplateBuilder(TemplateBuilder $builder);
    abstract public function getTemplateBuilder();

}

// Endfile
