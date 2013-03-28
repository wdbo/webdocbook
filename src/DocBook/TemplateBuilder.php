<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use Patterns\Abstracts\AbstractView;

use DocBook\FrontController,
    DocBook\Locator,
    DocBook\DocBookException;

/**
 */
class TemplateBuilder extends AbstractView
{

    /**
     * The TWIG template engine
     */
    protected $twig;

    /**
     * Constructor
     */
    public function __construct()
    {
        $docbook = FrontController::getInstance();
        // template engine
        $loader = new \Twig_Loader_Filesystem( array(
            $docbook->getPath('user_templates'),
            $docbook->getPath('base_templates')
        ) );
        $this->twig = new \Twig_Environment($loader, array(
            'cache'             => $docbook->getPath('cache'),
            'charset'           => $docbook->getRegistry()->get('html:charset', 'utf-8', 'docbook'),
            'debug'             => true,
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addExtension(new \DocBook_Twig_Extension());
    }
    
// ------------------
// Process
// ------------------

    /**
     * Building of a view content by Twig
     *
     * @param string $view The view filename
     * @param array $params An array of the parameters passed for the view parsing
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
     *
     * @param string $view The view filename
     * @param array $params An array of the parameters passed for the view parsing
     * @throw Throws an DocBookRuntimeException if the file view can't be found
     */
    public function renderSafe($view, array $params = array())
    {
        $this->setView( $this->getTemplate( $view ) );
        $this->setParams( array_merge($this->getDefaultViewParams(), $params) );
        if ($this->getView()) {
            $view_parameters = $this->getParams();
            if (!empty($view_parameters))
                extract($view_parameters, EXTR_OVERWRITE);
            ob_start();
            include $this->getView();
            $this->setOutput( ob_get_contents() );
            ob_end_clean();
        } else {
            throw new DocBookException(
                sprintf('Template "%s" can\'t be found!', $this->getView())
            );
        }
        return $this->getOutput();
    }

    /**
     * Get an array of the default parameters for all views
     */
    public function getDefaultViewParams()
    {
        $docbook = FrontController::getInstance();
        return array(
            'DB'                => $docbook,
            'app_cfg'           => $docbook->getRegistry()->getConfig('html', array(), 'docbook'),
            'app'               => $docbook->getRegistry()->getConfig('app', array(), 'docbook'),
            'manifest'          => $docbook->getRegistry()->getConfig('manifest', array()),
            'assets'            => '/'.FrontController::DOCBOOK_ASSETS.'/',
            'vendor_assets'     => '/'.FrontController::DOCBOOK_ASSETS.'/vendor/',
            'chapters'          => $docbook->getChapters(),
            'search_str'        => $docbook->getRequest()->getGet('s'),
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
        $locator = new Locator;
        return $locator->fallbackFinder($name);
    }

}

// Endfile
