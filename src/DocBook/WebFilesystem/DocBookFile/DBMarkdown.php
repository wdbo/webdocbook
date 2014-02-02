<?php
/**
 * PHP / Markdown Extended : DocBook
 * @author      Pierre Cassat & contributors
 * @package     DocBook
 * @copyleft    Les Ateliers Pierrot <ateliers-pierrot.fr>
 * @license     GPL-v3
 * @sources     http://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem\DocBookFile;

use \DocBook\FrontController,
    \DocBook\Helper,
    \DocBook\WebFilesystem\DocBookFile,
    \DocBook\WebFilesystem\DocBookFileInterface;

use \WebFilesystem\WebFilesystem,
    \WebFilesystem\WebFileInfo,
    \WebFilesystem\WebFilesystemIterator,
    \WebFilesystem\Finder;

use \Library\Helper\Directory as DirectoryHelper;

use \FilesystemIterator;

/**
 */
class DBMarkdown
    extends WebFileInfo
    implements DocBookFileInterface
{

    /**
     * @param array $params
     * @return string
     */
    public function viewFileInfos(array $params = array())
    {
        $docbook = FrontController::getInstance();
        $md_parser = $docbook->getMarkdownParser();
        $md_content = $md_parser->transformSource($this->getRealPath());
        $output_bag = $md_parser->get('OutputFormatBag');

        $page_notes = $md_content->getNotesToString();
        $params['page_notes'] = $page_notes;

        $page_footnotes = $md_content->getFootnotes();
        $page_glossary = $md_content->getGlossaries();
        $page_citations = $md_content->getCitations();
        if (!empty($page_citations) || !empty($page_glossary)) {
            $params['page_footnotes'] = $page_footnotes;
            $params['page_glossary'] = $page_glossary;
            $params['page_citations'] = $page_citations;
        }
        $params['toc'] = $output_bag->getHelper()
            ->getToc($md_content, $output_bag->getFormater());

        return $docbook->display($md_content->getBody(), 'content', $params);
/*
var_dump($md_content->getFootnotes());
var_dump($md_content->getGlossaries());
var_dump($md_content->getCitations());
*/
    }

    /**
     * @param array $params
     * @return string
     */
    public function getIntroduction(array $params = array())
    {
        $docbook = FrontController::getInstance();
        $md_parser = $docbook->getMarkdownParser();
        $md_content = $md_parser->transformSource($this->getRealPath());
        return $md_content->getBody();
    }

}

// Endfile
