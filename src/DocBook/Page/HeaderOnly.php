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
class HeaderOnly extends AbstractPage
{

    public static $template_name = 'layout_header_only';

    public function parse()
    {
        return '';
    }

}

// Endfile
