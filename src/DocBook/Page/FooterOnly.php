<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Page;

use DocBook\FrontController,
    DocBook\Abstracts\AbstractPage,
    DocBook\Locator;

/**
 */
class FooterOnly extends AbstractPage
{

    public static $template_name = 'layout_noheader';

    public function parse()
    {
        $docbook = FrontController::getInstance();
        $path = $this->getPath();
        if (!empty($path)) {
            $readme = Locator::findPathReadme($path);
            if (file_exists($readme)) {
                $docbook->setInputFile($readme);
                $md_parser = $docbook->getMarkdownParser();
                $content = file_get_contents($readme);
                return $md_parser->transform($content);
            }
        }
        return '';
    }

}

// Endfile
