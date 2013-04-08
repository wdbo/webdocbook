<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\FrontController,
    DocBook\Helper;

use Patterns\Interfaces\RouterInterface;

use Library\Helper\Url as UrlHelper;

/**
 */
class Router
{

    protected $route;

    protected $routes;

    protected $arguments_table;

    protected $matcher;

    public function __construct(
        $route = null, Collection $routes = null, array $arguments_table = array(), $matcher = null
    ) {
        if (!is_null($routes)) {
            $this->setRoutes($routes);
        }
        if (!is_null($arguments_table)) {
            $this->setArgumentsTable($arguments_table);
        }
        if (!is_null($matcher)) {
            $this->setMatcher($matcher);
        }
        if (!is_null($route)) {
            $this->setRoute($route);
        } else {
            $this->setRoute(UrlHelper::getRequestUrl());
        }
    }

// ----------------------
// Setters / Getters
// ----------------------

    /**
     * Set the current route
     *
     * @param string $route The current application route to distribute
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get the current route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the routes collection
     *
     * @param obj $collection A `Patterns\Commons\Collection` object
     */
    public function setRoutes(Collection $collection)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * Get the routes collection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the arguments correspondences table
     *
     * @param array $arguments A table of correspondences like ( true arg in URL => true arg name in the app )
     */
    public function setArgumentsTable(array $arguments)
    {
        $this->arguments_table = $arguments;
        return $this;
    }

    /**
     * Get the arguments table
     */
    public function getArgumentsTable()
    {
        return $this->arguments_table;
    }

    /**
     * Set the route matcher
     *
     * @param string $matcher A mask to parse and match a route URL
     */
    public function setMatcher($matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    /**
     * Get the route matcher
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * Check if a route exists
     *
     * @param string $route The route to test
     */
    public function routeExists($route)
    {
        return is_array($this->getRoutes()) && array_key_exists($route, $this->getRoutes());
    }

// ----------------------
// Processes & utilities
// ----------------------

    /**
     * Build a new route URL
     *
     * @param misc $route_infos The informations about the route to analyze
     * @param string $hash A hash tag to add to the generated URL
	 * @param string $separator The argument/value separator (default is escaped ampersand : '&amp;')
     * @return string The application valid URL for the route
     */
    public function generateUrl($route_infos, $hash = null, $separator = '&amp;')
	{
		$url_args = $this->getArgumentsTable();
		// ....
	}

    /**
     * Test if an URL has a corresponding route
     *
     * @param misc $pathinfo The path information to test
     */
    public function matchUrl($pathinfo)
    {
    }

    /**
     * Actually dispatch the current route
     */
    public function distribute()
    {
    }

    /**
     * Forward the application to a new route (no HTTP redirect)
     *
     * @param misc $pathinfo The path information to forward to
     * @param string $hash A hash tag to add to the generated URL
     */
    public function forward($pathinfo, $hash = null)
    {
    }

    /**
     * Make a redirection to a new route (HTTP redirect)
     *
     * @param misc $pathinfo The path information to redirect to
     * @param string $hash A hash tag to add to the generated URL
     */
    public function redirect($pathinfo, $hash = null)
	{
	    $uri = is_string($pathinfo) ? $pathinfo : $this->generateUrl($pathinfo);
		if (!headers_sent()) {
		  header("Location: $uri");
		} else {
			echo <<<MESSAGE
<!DOCTYPE HTML>
<head>
<meta http-equiv='Refresh' content='0; url={$uri}'><title>HTTP 302</title>
</head><body>
<h1>HTTP 302</h1>
<p>Your browser will be automatically redirected.
<br />If not, please clic on next link: <a href="{$uri}">{$uri}</a>.</p>
</body></html>
MESSAGE;
		}
		exit;
	}

}

// Endfile