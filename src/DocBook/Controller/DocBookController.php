<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Controller;

use DocBook\FrontController,
    DocBook\Helper,
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
        return array('credits', 'YO', array('title'=>'About DocBook'));
    }

    public function docbookdocAction()
    {
        $path = Helper::slashDirname(FrontController::DOCBOOK_ASSETS)
            .'USER_MANUAL.md';

        $page_infos = array(
            'name'      => 'USER_MANUAL.md',
            'path'      => 'docbookdoc',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params = array(
            'breadcrumbs' => array('DocBook user manual'),
            'title' => 'User manual',
            'page' => $page_infos,
            'page_tools' => 'false'
        );

        $file_content = file_get_contents($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $content = $this->docbook->display(
            $md_parser->transform($file_content),
            'content',
            array(
                'page'=>$page_infos,
                'page_tools' => 'false'
            )
        );

        return array('default', $content, $tpl_params);
    }

}

// Endfile
