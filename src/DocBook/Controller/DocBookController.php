<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Controller;

use DocBook\FrontController,
    DocBook\Locator,
    DocBook\Abstracts\AbstractController;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
class DocBookController extends AbstractController
{

    public function notFoundAction($str = '')
    {
        return array('not_found', $str);
    }

    public function creditsAction()
    {

        return array('layout_empty_txt', 'YO');
    }

}

// Endfile
