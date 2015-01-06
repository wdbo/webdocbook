<?php
/**
 * This file is part of the DocBook package.
 *
 * Copyleft (ↄ) 2008-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/docbook>.
 */

namespace DocBook\Controller;

use \DocBook\FrontController;
use \DocBook\Helper;
use \DocBook\Abstracts\AbstractController;
use \DocBook\WebFilesystem\DocBookFile;
use \MarkdownExtended\MarkdownExtended;
use \WebFilesystem\WebFilesystem;
use \WebFilesystem\WebFileInfo;
use \WebFilesystem\FileType\WebImage;

/**
 */
class DefaultController
    extends AbstractController
{

    public function indexAction($path)
    {
        if (@is_dir($path)) {
            return $this->directoryAction($path);
        } else {
            return $this->fileAction($path);
        }
    }

// -------------
// routes
// -------------

    public function directoryAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getpath());
        $readme_content = $dir_content = '';

        $index = $dbfile->findIndex();
        if (file_exists($index)) {
            return $this->fileAction($index);
        }

        $tpl_params = array(
            'page' => $dbfile->getDocBookFullStack(),
            'dirscan' => $dbfile->getDocBookScanStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
        );
/*/
var_dump($dbfile);
var_dump($tpl_params);
exit('yo');
//*/
        $readme = $dbfile->findReadme();
        if (file_exists($readme)) {
            $this->docbook->setInputFile($readme);
            $readme_dbfile = new DocBookFile($readme);
            $readme_content = $readme_dbfile->viewFileInfos();
        }

        $tpl_params['inpage_menu'] = !empty($readme_content) ? 'true' : 'false';
        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = _T('Home');
            }
        }

        $dir_content = $this->docbook
            ->display('', 'dirindex', $tpl_params);

        return array('default', $dir_content.$readme_content, $tpl_params);
    }

    public function rssFeedAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getpath());

        $tpl_params = array(
            'page' => $dbfile->getDocBookFullStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
        );
        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = _T('Home');
            }
        }

        FrontController::getInstance()->getResponse()
            ->setContentType('xml');

        $page = $dbfile->getDocBookStack();
        if ($dbfile->isDir()) {
            $contents = Helper::getFlatDirscans($dbfile->getDocBookScanStack(true), true);
            foreach ($contents['dirscan'] as $i=>$item) {
                if ($item['type']!=='dir' && file_exists($item['path'])) {
                    $dbfile = new DocBookFile($item['path']);
                    $contents['dirscan'][$i]['content'] = 
                        $dbfile->viewIntroduction(4000, false);
                }
            }
        } else {
            $page['content'] = $dbfile->viewIntroduction(4000, false);
        }
        
        $rss_content = $this->docbook->display('', 'rss', array(
            'page' => $page,
            'contents'=>$contents
        ));

        return array('layout_empty_xml', $rss_content);
    }

    public function sitemapAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getpath());
        FrontController::getInstance()->getResponse()
            ->setContentType('xml');
        $contents = Helper::getFlatDirscans($dbfile->getDocBookScanStack(true));
        $rss_content = $this->docbook->display('', 'sitemap', array(
            'page' => $dbfile->getDocBookStack(),
            'contents'=>$contents
        ));
        return array('layout_empty_xml', $rss_content);
    }

    public function fileAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getPath());
        $tpl_params = array(
            'page' => $dbfile->getDocBookFullStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
        );

        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = _T('Home');
            }
        }
        $content = $dbfile->viewFileInfos();
        return array('default',
            $content,
            $tpl_params);
    }

    public function htmlOnlyAction($path)
    {
        $this->setPath($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformSource($this->getPath());
        return array('layout_empty_html', 
            $md_content->getBody(),
            array('page_notes'=>$md_content->getNotesToString())
        );
    }

    public function plainTextAction($path)
    {
        $this->setPath($path);
        $ctt = $this->docbook->getResponse()->flush(file_get_contents($this->getPath()));
        return array('layout_empty_txt', $ctt);
    }

    public function downloadAction($path)
    {
        $this->setPath($path);
        $this->docbook->getResponse()->download($path, 'text/plain');
        exit;
    }

    public function searchAction($path)
    {
        $this->setPath($path);
        $search = $this->docbook->getRequest()->getArgument('s');
        if (empty($search)) return $this->indexAction($path);
        $_s = Helper::processDocBookSearch($search, $this->getPath());

        $title = _T('Search for "%search_str%"', array('search_str'=>$search));
        $breadcrumbs = Helper::getBreadcrumbs($this->getPath());
        $breadcrumbs[] = $title;
        $dbfile = new DocBookFile($this->getpath());
        $page = $dbfile->getDocBookStack();
        $page['type'] = 'search';
        $tpl_params = array(
            'page' => $page,
            'breadcrumbs' => $breadcrumbs,
            'title' => $title,
        );

        $search_content = $this->docbook->display($_s, 'search', array(
            'search_str' => $search,
            'path' => Helper::buildPageTitle($this->getPath()),
        ));
        return array('default', $search_content, $tpl_params);
    }

}

// Endfile
