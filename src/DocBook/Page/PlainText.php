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
class PlainText extends AbstractPage
{

    public static $template_name = 'layout_empty';

    public function parse()
    {
        $docbook = \DocBook\FrontController::getInstance();
        $charset = $docbook->getRegistry()->get('html:charset', 'utf-8', 'docbook');
        $docbook->addHeader('Content-type', 'text/plain; charset: '.$charset);
        return file_get_contents($this->getPath());
    }

}

// Endfile
