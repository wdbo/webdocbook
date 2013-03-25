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
class DefaultController extends AbstractController
{

    public function indexAction($path)
    {
        $this->setPath($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $content = file_get_contents($this->getPath());
        return array('default', $md_parser->transform($content));
    }

    public function headerOnlyAction()
    {
        return array('layout_header_only', '');
    }

    public function footerOnlyAction($path)
    {
        if (!empty($path)) {
            $readme = Locator::findPathReadme($path);
            if (file_exists($readme)) {
                $this->docbook->setInputFile($readme);
                $md_parser = $this->docbook->getMarkdownParser();
                $content = file_get_contents($readme);
                return array('layout_noheader', $md_parser->transform($content));
            }
        }
        return array('layout_noheader', '');
    }

    public function htmlOnlyAction($path)
    {
        $this->setPath($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $content = file_get_contents($this->getPath());
        return array('layout_empty_html', $md_parser->transform($content));
    }

    public function plainTextAction($path)
    {
        $this->setPath($path);
        $ctt = $this->docbook->getResponse()->flush(file_get_contents($this->getPath()));
        return array('layout_empty_txt', $ctt);
    }


}

// Endfile
