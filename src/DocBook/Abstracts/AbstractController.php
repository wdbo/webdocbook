<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

use DocBook\Abstracts\AbstractPage,
    DocBook\TemplateBuilder;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
abstract class AbstractController
{

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
