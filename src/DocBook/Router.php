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

namespace DocBook;

use \Patterns\Interfaces\RouterInterface;
use \Library\Helper\Url as UrlHelper;
use \Patterns\Commons\Collection;

/**
 * Class Router
 * @package DocBook
 */
class Router
    implements RouterInterface
{

    /**
     * @var string
     */
    protected $route;

    /**
     * @var \Patterns\Commons\Collection
     */
    protected $routes;

    /**
     * @var array
     */
    protected $arguments_table;

    /**
     * @var string
     */
    protected $matcher;

    /**
     * @param null $route
     * @param \Patterns\Commons\Collection $routes
     * @param array $arguments_table
     * @param null $matcher
     */
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
     * @param string $route The current application route to distribute
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get the current route
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the routes collection
     * @param \Patterns\Commons\Collection $collection
     * @return $this
     */
    public function setRoutes(Collection $collection)
    {
        $this->routes = $collection;
        return $this;
    }

    /**
     * Get the routes collection
     * @return \Patterns\Commons\Collection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the arguments correspondences table
     * @param array $arguments A table of correspondences like ( true arg in URL => true arg name in the app )
     * @return $this
     */
    public function setArgumentsTable(array $arguments)
    {
        $this->arguments_table = $arguments;
        return $this;
    }

    /**
     * Get the arguments table
     * @return array
     */
    public function getArgumentsTable()
    {
        return $this->arguments_table;
    }

    /**
     * Set the route matcher
     * @param string $matcher A mask to parse and match a route URL
     * @return $this
     */
    public function setMatcher($matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    /**
     * Get the route matcher
     * @return string
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * Check if a route exists
     * @param string $route The route to test
     * @return bool
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
     * @param mixed $route_infos The information about the route to analyze
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
     * @param mixed $pathinfo The path information to test
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
     * @param mixed $pathinfo The path information to forward to
     * @param string $hash A hash tag to add to the generated URL
     */
    public function forward($pathinfo, $hash = null)
    {
    }

    /**
     * Make a redirection to a new route (HTTP redirect)
     * @param mixed $pathinfo The path information to redirect to
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
<br />If not, please click on next link: <a href="{$uri}">{$uri}</a>.</p>
</body></html>
MESSAGE;
        }
        exit;
    }

}

// Endfile