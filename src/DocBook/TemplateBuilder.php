<?php
/**
 * CarteBlanche - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace DocBook;

use \Patterns\Interfaces\ViewInterface;
use \DocBook\FrontController;

/**
 */
class TemplateBuilder implements ViewInterface
{

    protected $twig;

	/**
	 * The view filename
	 */
	var $view=null;

	/**
	 * The parameters passed to the view (for parsing)
	 */
	var $params=array();

	/**
	 * The final rendering of the view
	 */
	var $output='';

	/**
	 * Constructor
	 */
	public function __construct()
	{
        $docbook = \DocBook\FrontController::getInstance();
        // template engine
        $loader = new \Twig_Loader_Filesystem( array(
            $docbook->getPath('base_templates'),
            $docbook->getPath('user_templates')
        ) );
        $this->twig = new \Twig_Environment($loader, array(
            'cache'             => $docbook->getPath('cache'),
            'charset'           => $docbook->getRegistry()->getConfig('html:charset', 'utf-8'),
            'debug'             => true,
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addExtension(new \DocBook_Twig_Extension());
	}
	
	/**
     * Building of a view content by Twig
	 *
	 * @param string $view The view filename
	 * @param array $params An array of the parameters passed for the view parsing
	 * @throw Throw an Exception if the file view can't be found
	 */
    public function render($view, array $params = array())
    {
		$view_parameters = array_merge($this->getDefaultViewParams(), $params);
	    $this->output = $this->twig->render($view, $view_parameters);
		return $this->output;
    }

	/**
     * Building of a view content including a view file passing it parameters
	 *
	 * @param string $view The view filename
	 * @param array $params An array of the parameters passed for the view parsing
	 * @throw Throw an Exception if the file view can't be found
	 */
    public function renderSafe($view, array $params = array())
    {
		$view_file = $this->getTemplate( $view );
		$view_parameters = array_merge($this->getDefaultViewParams(), $params);
		if ($view_file) {
			if (!empty($view_parameters))
	      		extract($view_parameters, EXTR_OVERWRITE);
			ob_start();
			include $view_file;
	    	$this->output = ob_get_contents();
  	  		ob_end_clean();
		} else {
      		throw new NotFoundException(
      			sprintf('Template "%s" can\'t be found!', $view)
      		);
		}
		return $this->output;
    }

    /**
     * Get an array of the default parameters for all views
     */
    public function getDefaultViewParams()
    {
        $docbook = \DocBook\FrontController::getInstance();
        return array(
            'DB'                => $docbook,
            'app_cfg'           => $docbook->getRegistry()->getConfig('html', array(), 'docbook'),
            'app'               => $docbook->getRegistry()->getConfig('app', array(), 'docbook'),
            'manifest'          => $docbook->getRegistry()->getConfig('manifest', array()),
            'assets'            => '/docbook_assets/',
            'vendor_assets'     => '/docbook_assets/vendor/',
        );
    }

    /**
     * Get a template file path (relative to `option['templates_dir']`)
	 *
	 * @param string $name The view filename
	 * @return misc FALSE if nothing had been find, the filename otherwise
     */
    public function getTemplate($name)
    {
        $docbook = \DocBook\FrontController::getInstance();
		return $docbook->fallbackFinder($name);
    }

}

// Endfile