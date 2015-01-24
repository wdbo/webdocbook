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

namespace WebDocBook;

use \WebDocBook\Exception\RuntimeException;
use \Patterns\Abstracts\AbstractView;

/**
 * Class TemplateBuilder
 */
class TemplateBuilder
    extends AbstractView
{

    /**
     * The TWIG template engine
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Constructor
     * 
     * The TWIG template engine is designed to first search the templates in the `user/templates/`
     * directory if it exists.
     */
    public function __construct()
    {
        // template engine
        $templates_dirs = array();
        try {
            $user_templates = Kernel::getPath('user_templates');
        } catch (\Exception $e) {}
        if (!empty($user_templates) && file_exists($user_templates)) {
            $templates_dirs[] = $user_templates;
        }
        $templates_dirs[]   = Kernel::getPath('templates');
        $loader             = new \Twig_Loader_Filesystem($templates_dirs);
        $this->twig         = new \Twig_Environment($loader, array(
            'cache'             => Kernel::getPath('cache'),
            'charset'           => Kernel::getConfig('html:charset', 'utf-8'),
            'debug'             => true,
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addExtension(new \WebDocBook_Twig_Extension());
    }
    
// ------------------
// Process
// ------------------

    /**
     * Building of a view content by Twig
     * @param string $view The view filename
     * @param array $params An array of the parameters passed for the view parsing
     * @return string
     */
    public function render($view, array $params = array())
    {
        $this->setView($view);
        $this->setParams( array_merge($this->getDefaultViewParams(), $params) );
        $this->setOutput( $this->twig->render($this->getView(), $this->getParams()) );
        return $this->getOutput();
    }

    /**
     * Building of a view content including a view file passing it parameters
     * @param string $view The view filename
     * @param array $params An array of the parameters passed for the view parsing
     * @return string
     * @throws \WebDocBook\Exception\RuntimeException if the file view can't be found
     */
    public function renderSafe($view, array $params = array())
    {
        $this->setView( $this->getTemplate( $view ) );
        $this->setParams( array_merge($this->getDefaultViewParams(), $params) );
        if ($this->getView()) {
            $view_parameters = $this->getParams();
            if (!empty($view_parameters)) {
                extract($view_parameters, EXTR_OVERWRITE);
            }
            ob_start();
            include $this->getView();
            $this->setOutput( ob_get_contents() );
            ob_end_clean();
        } else {
            throw new RuntimeException(
                sprintf('Template "%s" can\'t be found!', $this->getView())
            );
        }
        return $this->getOutput();
    }

    /**
     * Get an array of the default parameters for all views
     * @return array
     */
    public function getDefaultViewParams()
    {
        $wdb        = FrontController::getInstance();
        $session    = $wdb->getUser()->getSession();
        return array(
            'WDB'               => $wdb,
            'user_cfg'          => Kernel::get('user_config'),
            'manifest'          => Kernel::get('manifest'),
            'app_cfg'           => Kernel::getConfig('html', array()),
            'app'               => Kernel::getConfig('app', array()),
            'langs'             => Kernel::getConfig('languages', array()),
            'assets'            => '/'.Kernel::getPath('webdocbook_assets', true, 'web'),
            'vendor_assets'     => '/'.Kernel::getPath('vendor_assets', true, 'web'),
            'chapters'          => $wdb->getChapters(),
            'search_str'        => $wdb->getRequest()->getGet('s'),
            'session'           => $session->hasFlash() ? $session->allFlashes() : array(),
        );
    }

    /**
     * Get a template file path (relative to `option['templates_dir']`)
     * @param string $name The view filename
     * @return bool|string FALSE if nothing had been find, the filename otherwise
     */
    public function getTemplate($name)
    {
        return Kernel::findTemplate($name);
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwigEngine()
    {
        return $this->twig;
    }

}

// Endfile
