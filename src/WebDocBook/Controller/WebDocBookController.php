<?php
/**
 * This file is part of the WebDocBook package.
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook\Controller;

use \WebDocBook\Kernel;
use \WebDocBook\Helper;
use \WebDocBook\Templating\Helper as TemplateHelper;
use \WebDocBook\Abstracts\AbstractController;
use \WebDocBook\Exception\NotFoundException;
use \Library\Converter\Array2INI;

/**
 * Class WebDocBookController
 *
 * The WebDocBook internal controller
 */
class WebDocBookController
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
        return array('credits', '', array('title'=>'About WebDocBook'));
    }

    /**
     * The internal documentation action
     * @return array
     */
    public function docbookdocAction()
    {
        $title              = _T('User manual');
        $md_parser          = $this->wdb->getMarkdownParser();

        // user manual
        $path               = Kernel::getPath('webdocbook_assets') . Kernel::getConfig('pages:user_manual', '');
        $update             = Helper::getDateTimeFromTimestamp(filemtime($path));
        $file_content       = file_get_contents($path);
        $md_content         = $md_parser->transformString($file_content);
        $output_bag         = $md_parser->get('OutputFormatBag');
        $user_manual_menu   = $output_bag->getHelper()
                                ->getToc($md_content, $output_bag->getFormatter());
        $user_manual_content= $md_content->getBody();

        // MD manual
        $path_md            = Kernel::getPath('webdocbook_assets') . Kernel::getConfig('pages:md_manual', '');
        $update_md          = Helper::getDateTimeFromTimestamp(filemtime($path_md));
        $file_content       = file_get_contents($path_md);
        $md_content         = $md_parser->transformString($file_content);
        $output_bag         = $md_parser->get('OutputFormatBag');
        $md_manual_menu     = $output_bag->getHelper()
                                ->getToc($md_content, $output_bag->getFormatter());
        $md_manual_content  = $md_content->getBody();

        // about content
        $about_content            = $this->wdb->display(
            '', 'credits', array('page_tools'=>'false')
        );

        // global page
        $page_infos     = array(
            'name'          => $title,
            'path'          => 'docbookdoc',
            'update'        => $update_md > $update ? $update_md : $update
        );
        $tpl_params     = array(
            'breadcrumbs'   => array($title),
            'title'         => $title,
            'page'          => array(),
            'page_tools'    => 'false'
        );
        $content            = $this->wdb->display(
            '',
            'user_manual',
            array(
                'user_manual_content'   => $user_manual_content,
                'md_manual_content'     => $md_manual_content,
                'user_manual_menu'      => $user_manual_menu,
                'md_manual_menu'        => $md_manual_menu,
                'about_content'         => $about_content,
                'page'                  => $page_infos,
                'page_tools'            => 'false',
                'page_title'            => 'true',
                'title'                 => $title,
            )
        );

        return array('default', $content, $tpl_params);
    }

    /**
     * Admin panel action
     * @return array
     * @throws \WebDocBook\Exception\NotFoundException
     */
    public function adminAction()
    {
        $allowed    = Kernel::getConfig('expose_admin', false, 'user_config');
        $saveadmin  = $this->wdb->getUser()->getSession()->get('saveadmin');
        $this->wdb->getUser()->getSession()->remove('saveadmin');
        if (
            (!$allowed || ('true' !== $allowed && '1' !== $allowed)) &&
            (is_null($saveadmin) || $saveadmin != time())
        ) {
            throw new NotFoundException('Forbidden access!');
        }

        $user_config_file   = Kernel::getPath('user_config_filepath', true);
        $user_config        = Kernel::get('user_config');
        $title              = _T('Administration panel');
        $page_infos         = array(
            'name'              => $title,
            'path'              => 'admin',
        );
        $tpl_params         = array(
            'breadcrumbs'       => array($title),
            'title'             => $title,
            'page'              => $page_infos,
            'page_tools'        => 'false'
        );

        $content            = $this->wdb->display(
            '',
            'admin_panel',
            array(
                'page'          => $page_infos,
                'page_tools'    => 'false',
                'page_title'    => 'true',
                'title'         => $title,
                'user_config_file'=> $user_config_file,
                'user_config'   => $user_config,
                'config'        => Kernel::getConfig(),
            )
        );

        return array('default', $content, $tpl_params);
    }

    /**
     * @return array
     * @throws \WebDocBook\Exception\Exception
     * @throws \WebDocBook\Exception\NotFoundException
     */
    public function saveadminAction()
    {
        $allowed = Kernel::getConfig('expose_admin', false, 'user_config');
        if (!$allowed || ('true' !== $allowed && '1' !== $allowed)) {
            throw new NotFoundException('Forbidden access!');
        }

        if ($this->wdb->getRequest()->isPost()) {

            $this->wdb->getUser()->getSession()->set('saveadmin', time());
            $data           = $this->wdb->getRequest()->getData();
            $config_file    = Kernel::getPath('user_config_filepath');
            $internal_conf  = Kernel::getConfig('userconf', array());
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
                $this->wdb->getUser()->getSession()
                    ->setFlash(
                        _T('OK - Your configuration has been updated'), 'message_ok'
                    );
            } else {
                $this->wdb->getUser()->getSession()
                    ->setFlash(
                        _T("Can't write configuration file!"), 'message_error'
                    );
            }
        }

        $this->wdb->getResponse()->redirect(
            TemplateHelper::getRoute('admin')
        );
        exit();
    }

}

// Endfile
