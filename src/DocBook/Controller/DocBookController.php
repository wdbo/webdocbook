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

use Library\Helper\Directory as DirectoryHelper;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
class DocBookController extends AbstractController
{

    public function notFoundAction($str = '')
    {
        return array('not_found', '', array('message'=>$str));
    }

    public function forbiddenAction($str = '')
    {
        return array('forbidden', '', array('message'=>$str));
    }

    public function creditsAction()
    {
        return array('credits', 'YO', array('title'=>'About DocBook'));
    }

    public function docbookdocAction()
    {
        $path = DirectoryHelper::slashDirname(FrontController::DOCBOOK_ASSETS)
            .'USER_MANUAL.md';

        $page_infos = array(
            'name'      => 'USER_MANUAL.md',
            'path'      => 'docbookdoc',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params = array(
            'breadcrumbs' => array('DocBook user manual'),
            'title' => _T('User manual'),
            'page' => $page_infos,
            'page_tools' => 'false'
        );

        $file_content = file_get_contents($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformString($file_content);
        $content = $this->docbook->display(
            $md_content->getBody(),
            'content',
            array(
                'page'=>$page_infos,
                'page_tools' => 'false',
                'page_notes' => $md_content->getNotesToString()
            )
        );

        return array('default', $content, $tpl_params);
    }

}

// Endfile
