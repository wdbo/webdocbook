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
use \Library\Helper\Directory as DirectoryHelper;
use \MarkdownExtended\MarkdownExtended;

/**
 */
class DocBookController
    extends AbstractController
{

    public function notFoundAction($str = '')
    {
        return array('not_found', '', array('message'=>$str));
    }

    public function forbiddenAction($str = '')
    {
        return array('forbidden', '', array('message'=>$str));
    }

    public function errorAction($str = '')
    {
        return array('error', '', array('message'=>$str));
    }

    public function creditsAction()
    {
        return array('credits', '', array('title'=>'About DocBook'));
    }

    public function docbookdocAction()
    {
        $title = _T('User manual');
        $path = DirectoryHelper::slashDirname(FrontController::DOCBOOK_ASSETS)
            .'USER_MANUAL.md';

        $page_infos = array(
            'name'      => 'USER_MANUAL.md',
            'path'      => 'docbookdoc',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params = array(
            'breadcrumbs' => array($title),
            'title' => $title,
            'page' => $page_infos,
            'page_tools' => 'false'
        );

        $file_content = file_get_contents($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformString($file_content);
        $output_bag = $md_parser->get('OutputFormatBag');
        $menu = $output_bag->getHelper()
            ->getToc($md_content, $output_bag->getFormatter());
        $content = $this->docbook->display(
            $md_content->getBody(),
            'content',
            array(
                'page'=>$page_infos,
                'page_tools' => 'false',
                'page_title' => 'true',
                'page_notes' => $md_content->getNotesToString(),
                'title' => $title,
                'toc'=>$menu,
            )
        );

        return array('default', $content, $tpl_params);
    }

    public function adminAction()
    {
        $title = _T('Administration');
        $path = DirectoryHelper::slashDirname(FrontController::DOCBOOK_ASSETS)
            .'ADMIN_WELCOME.md';

        $page_infos = array(
            'name'      => 'ADMIN_WELCOME.md',
            'path'      => 'admin',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params = array(
            'breadcrumbs' => array($title),
            'title' => $title,
            'page' => $page_infos,
            'page_tools' => 'false'
        );

        $file_content = file_get_contents($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformString($file_content);
        $output_bag = $md_parser->get('OutputFormatBag');
        $menu = $output_bag->getHelper()
            ->getToc($md_content, $output_bag->getFormatter());

        $content = $this->docbook->display(
            $md_content->getBody(),
            'admin_panel',
            array(
                'page'=>$page_infos,
                'page_tools' => 'false',
                'page_title' => 'true',
                'page_notes' => $md_content->getNotesToString(),
                'title' => $title,
                'toc'=>$menu,
                'config' => $this->docbook->getRegistry()->getConfigs(),
            )
        );

        return array('default', $content, $tpl_params);
    }

    public function preferencesAction()
    {
        $title = _T('Preferences');
        $path = DirectoryHelper::slashDirname(FrontController::DOCBOOK_ASSETS)
            .'USER_PREFERENCES.md';

        $page_infos = array(
            'name'      => 'USER_PREFERENCES.md',
            'path'      => 'prefs',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params = array(
            'breadcrumbs' => array($title),
            'title' => $title,
            'page' => $page_infos,
            'page_tools' => 'false'
        );

        $file_content = file_get_contents($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $md_content = $md_parser->transformString($file_content);
        $output_bag = $md_parser->get('OutputFormatBag');
        $menu = $output_bag->getHelper()
            ->getToc($md_content, $output_bag->getFormatter());

        $content = $this->docbook->display(
            $md_content->getBody(),
            'admin_panel',
            array(
                'page'=>$page_infos,
                'page_tools' => 'false',
                'page_title' => 'true',
                'page_notes' => $md_content->getNotesToString(),
                'title' => $title,
                'toc'=>$menu,
                'config' => $this->docbook->getRegistry()->getConfigs(),
            )
        );

        return array('default', $content, $tpl_params);
    }

}

// Endfile
