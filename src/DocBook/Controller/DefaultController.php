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

use \DocBook\Helper;
use \DocBook\Abstracts\AbstractController;
use \DocBook\Exception\NotFoundException;
use \DocBook\WebFilesystem\DocBookFile;

/**
 * Class DefaultController
 *
 * This is the default controller of DocBook, which may
 * handle most of the requests.
 */
class DefaultController
    extends AbstractController
{

    /**
     * Default action
     *
     * @param string $path
     * @return array
     */
    public function indexAction($path)
    {
        if (@is_dir($path)) {
            return $this->directoryAction($path);
        } else {
            return $this->fileAction($path);
        }
    }

    /**
     * Directory path action
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function directoryAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getpath());
        if ($dbfile->isFile()) {
            return $this->fileAction($this->getPath());
        }

        $readme_content = $dir_content = '';

        $index = $dbfile->findIndex();
        if (file_exists($index)) {
            return $this->fileAction($index);
        }

        $tpl_params = array(
            'page'          => $dbfile->getDocBookFullStack(),
            'dirscan'       => $dbfile->getDocBookScanStack(),
            'breadcrumbs'   => Helper::getBreadcrumbs($this->getPath()),
            'title'         => Helper::buildPageTitle($this->getPath()),
        );
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = _T('Home');
            }
        }

        $readme = $dbfile->findReadme();
        if (file_exists($readme)) {
            $this->docbook->setInputFile($readme);
            $readme_dbfile  = new DocBookFile($readme);
            $readme_content = $readme_dbfile->viewFileInfos();
        }
        $tpl_params['inpage_menu']  = !empty($readme_content) ? 'true' : 'false';

        $dir_content = $this->docbook
            ->display('', 'dirindex', $tpl_params);

        return array('default', $dir_content.$readme_content, $tpl_params);
    }

    /**
     * File path action
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function fileAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getPath());
        if ($dbfile->isDir()) {
            return $this->directoryAction($this->getPath());
        }

        $tpl_params = array(
            'page'          => $dbfile->getDocBookFullStack(),
            'breadcrumbs'   => Helper::getBreadcrumbs($this->getPath()),
            'title'         => Helper::buildPageTitle($this->getPath()),
        );
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

    /**
     * RSS action for concerned path
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function rssFeedAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile     = new DocBookFile($this->getpath());
        $contents   = array();
        $tpl_params = array(
            'page'          => $dbfile->getDocBookFullStack(),
            'breadcrumbs'   => Helper::getBreadcrumbs($this->getPath()),
            'title'         => Helper::buildPageTitle($this->getPath()),
        );
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = _T('Home');
            }
        }

        $this->docbook->getResponse()->setContentType('xml');

        $page = $dbfile->getDocBookStack();
        if ($dbfile->isDir()) {
            $contents = Helper::getFlatDirscans($dbfile->getDocBookScanStack(true), true);
            foreach ($contents['dirscan'] as $i=>$item) {
                if ($item['type']!=='dir' && file_exists($item['path'])) {
                    $dbfile = new DocBookFile($item['path']);
                    $contents['dirscan'][$i]['content'] = $dbfile->viewIntroduction(4000, false);
                }
            }
        } else {
            $page['content'] = $dbfile->viewIntroduction(4000, false);
        }
        
        $rss_content = $this->docbook->display('', 'rss', array(
            'page'      => $page,
            'contents'  => $contents
        ));

        return array('layout_empty_xml', $rss_content);
    }

    /**
     * Sitemap action for a path
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function sitemapAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getpath());
        if (!$dbfile->isDir()) {
            throw new NotFoundException(
                'Can not build a sitemap from a single file!',
                0, null, Helper::getRoute($dbfile->getDocBookPath())
            );
        }

        $this->docbook->getResponse()->setContentType('xml');

        $contents       = Helper::getFlatDirscans($dbfile->getDocBookScanStack(true));
        $rss_content    = $this->docbook->display('', 'sitemap', array(
            'page'          => $dbfile->getDocBookStack(),
            'contents'      => $contents
        ));
        return array('layout_empty_xml', $rss_content);
    }

    /**
     * HTML only version of a path
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function htmlOnlyAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getpath());
        if (!$dbfile->isFile()) {
            throw new NotFoundException(
                'Can not send raw content of a directory!',
                0, null, Helper::getRoute($dbfile->getDocBookPath())
            );
        }

        $md_parser  = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformSource($this->getPath());
        return array('layout_empty_html', 
            $md_content->getBody(),
            array('page_notes' => $md_content->getNotesToString())
        );
    }

    /**
     * Raw plain text action of a path
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function plainTextAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getpath());
        if (!$dbfile->isFile()) {
            throw new NotFoundException(
                'Can not send raw content of a directory!',
                0, null, Helper::getRoute($dbfile->getDocBookPath())
            );
        }

        $ctt = $this->docbook->getResponse()
            ->flush(file_get_contents($this->getPath()));
        return array('layout_empty_txt', $ctt);
    }

    /**
     * Download action of a path
     *
     * @param string $path
     * @throws \DocBook\Exception\NotFoundException
     */
    public function downloadAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $dbfile = new DocBookFile($this->getpath());
        if (!$dbfile->isFile()) {
            throw new NotFoundException(
                'Can not send raw content of a directory!',
                0, null, Helper::getRoute($dbfile->getDocBookPath())
            );
        }

        $this->docbook->getResponse()
            ->download($path, 'text/plain');
        exit(0);
    }

    /**
     * Global search action of a path
     *
     * @param string $path
     * @return array
     * @throws \DocBook\Exception\NotFoundException
     */
    public function searchAction($path)
    {
        try {
            $this->setPath($path);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $search = $this->docbook->getRequest()->getArgument('s');
        if (empty($search)) {
            return $this->indexAction($path);
        }

        $_s = Helper::processDocBookSearch($search, $this->getPath());

        $title          = _T('Search for "%search_str%"', array('search_str'=>$search));
        $breadcrumbs    = Helper::getBreadcrumbs($this->getPath());
        $breadcrumbs[]  = $title;
        $dbfile         = new DocBookFile($this->getpath());
        $page           = $dbfile->getDocBookStack();
        $page['type']   = 'search';
        $tpl_params     = array(
            'page'          => $page,
            'breadcrumbs'   => $breadcrumbs,
            'title'         => $title,
        );

        $search_content = $this->docbook->display($_s, 'search', array(
            'search_str'    => $search,
            'path'          => Helper::buildPageTitle($this->getPath()),
        ));
        return array('default', $search_content, $tpl_params);
    }

}

// Endfile
