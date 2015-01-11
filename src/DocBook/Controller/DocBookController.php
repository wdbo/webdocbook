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
use \DocBook\DocBookException;
use \DocBook\NotFoundException;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Converter\Array2INI;

/**
 * Class DocBookController
 *
 * The DocBook internal controller
 *
 * @package DocBook\Controller
 */
class DocBookController
    extends AbstractController
{

    /**
     * The 404 Not Found action
     *
     * @param string $str
     * @return array
     */
    public function notFoundAction($str = '')
    {
        return array('not_found', '', array('message'=>$str));
    }

    /**
     * The 403 Forbidden action
     *
     * @param string $str
     * @return array
     */
    public function forbiddenAction($str = '')
    {
        return array('forbidden', '', array('message'=>$str));
    }

    /**
     * The 500 error action
     *
     * @param string $str
     * @return array
     */
    public function errorAction($str = '')
    {
        return array('error', '', array('message'=>$str));
    }

    /**
     * The credits action
     *
     * This is used by the 'about' box of global template
     *
     * @return array
     */
    public function creditsAction()
    {
        return array('credits', '', array('title'=>'About DocBook'));
    }

    /**
     * The internal documentation action
     * @return array
     */
    public function docbookdocAction()
    {
        $title = _T('User manual');
        $path = DirectoryHelper::slashDirname($this->docbook->getAppConfig('internal_assets_dir', 'docbook_assets'))
            .$this->docbook->getRegistry()->get('pages:user_manual', array());

        $page_infos = array(
            'name'      => $title,
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

    /**
     * Admin panel action
     * @return array
     * @throws NotFoundException
     */
    public function adminAction()
    {
        $allowed = $this->docbook->getRegistry()->get('user_config:expose_admin', false);
        $saveadmin = $this->docbook->getSession()->get('saveadmin');
        $this->docbook->getSession()->remove('saveadmin');
        if (
            (!$allowed || ('true' !== $allowed && '1' !== $allowed)) &&
            (is_null($saveadmin) || $saveadmin != time())
        ) {
            throw new NotFoundException('Forbidden access!');
        }

        $user_config_file   = $this->docbook->getLocator()->getUserConfigPath(true);
        $user_config        = $this->docbook->getRegistry()->get('user_config', array());
        $title              = _T('Administration');
        $path               = DirectoryHelper::slashDirname($this->docbook->getAppConfig('internal_assets_dir', 'docbook_assets'))
                                .$this->docbook->getRegistry()->get('pages:admin_welcome', array());
        $page_infos         = array(
            'name'      => $title,
            'path'      => 'admin',
            'update'    => Helper::getDateTimeFromTimestamp(filemtime($path))
        );
        $tpl_params         = array(
            'breadcrumbs' => array($title),
            'title' => $title,
            'page' => $page_infos,
            'page_tools' => 'false'
        );
        $file_content       = file_get_contents($path);
        $md_parser          = $this->docbook->getMarkdownParser();
        $md_content         = $md_parser->transformString($file_content);
        $output_bag         = $md_parser->get('OutputFormatBag');
        $menu               = $output_bag->getHelper()->getToc($md_content, $output_bag->getFormatter());

        $content            = $this->docbook->display(
            $md_content->getBody(),
            'admin_panel',
            array(
                'page'          => $page_infos,
                'page_tools'    => 'false',
                'page_title'    => 'true',
                'page_notes'    => $md_content->getNotesToString(),
                'title'         => $title,
                'toc'           => $menu,
                'user_config_file'=> $user_config_file,
                'user_config'   => $user_config,
                'config'        => $this->docbook->getRegistry()->getConfigs(),
            )
        );

        return array('default', $content, $tpl_params);
    }

    /**
     * @return array
     * @throws DocBookException
     * @throws NotFoundException
     */
    public function saveadminAction()
    {
        $allowed = $this->docbook->getRegistry()->get('user_config:expose_admin', false);
        if (!$allowed || ('true' !== $allowed && '1' !== $allowed)) {
            throw new NotFoundException('Forbidden access!');
        }

        if ($this->docbook->getRequest()->isPost()) {

            $this->docbook->getSession()->set('saveadmin', time());
            $root_dir       = $this->docbook->getPath('root_dir');
            $data           = $this->docbook->getRequest()->getData();
            $config_file    = $this->docbook->getLocator()->getUserConfigPath();

            $internal_conf = $this->docbook->getRegistry()->get('userconf', array());
            foreach ($internal_conf as $var=>$val) {
                if (!array_key_exists($var, $data) && ($val=='1' || $val=='0')) {
                    $data[$var] = 0;
                }
            }

            if (false !== file_put_contents(
                    $config_file,
                    Array2INI::convert($data),
                    LOCK_EX
            )) {
                $this->docbook->getSession()
                    ->setFlash(
                        _T('OK - Your configuration has been updated'), 'message_ok'
                    );
            } else {
                $this->docbook->getSession()
                    ->setFlash(
                        _T("Can't write configuration file!"), 'message_error'
                    );
            }
        }

        $this->docbook->getResponse()->redirect(
            Helper::getRoute('admin')
        );
        exit();
    }

}

// Endfile
