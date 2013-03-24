<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Page;

use DocBook\Abstracts\AbstractPage;

/**
 */
class DefaultPage extends AbstractPage
{

    public function parse()
    {
        $docbook = \DocBook\FrontController::getInstance();
        $md_parser = $docbook->getMarkdownParser();
        $content = file_get_contents($this->getPath());
        return $md_parser->transform($content);
    }

}

// Endfile
