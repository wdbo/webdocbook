<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Page;

use DocBook\Abstracts\AbstractPage;
use DocBook\Helper;

/**
 */
class FooterOnly extends AbstractPage
{

    public static $template_name = 'layout_noheader';

    public function parse()
    {
        $docbook = \DocBook\FrontController::getInstance();
        $path = $this->getPath();
        if (!empty($path)) {
            $readme = Helper::findPathReadme($path);
            if (file_exists($readme)) {
                $md_parser = $docbook->getMarkdownParser();
                $content = file_get_contents($readme);
                return $md_parser->transform($content);
            }
        }
        return '';
    }

}

// Endfile
