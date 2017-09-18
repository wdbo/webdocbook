<?php
/**
 * This file is part of the WebDocBook package.
 *
 * Copyleft (ↄ) 2008-2017 Pierre Cassat <me@picas.fr> and contributors
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

/**
 * Class WebDocBook_Templating_Twig_Extension
 *
 * The Twig functions and filters for WebDocBook's templates
 *
 * @see http://twig.sensiolabs.org/api/master/index.html
 */
class WebDocBook_Templating_Twig_Extension
    extends \Twig_Extension
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'WebDocBook';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('route',         '\WebDocBook\Templating\Helper::getRoute'),
            new \Twig_SimpleFilter('absoluteRoute', '\WebDocBook\Templating\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFilter('relativePath',  '\WebDocBook\Templating\Helper::getRelativePath'),
            new \Twig_SimpleFilter('securedPath',   '\WebDocBook\Templating\Helper::getSecuredRealpath'),
            new \Twig_SimpleFilter('readableName',  '\WebDocBook\Templating\Helper::buildPageTitle'),
            new \Twig_SimpleFilter('rssEncode',     '\WebDocBook\Templating\Helper::rssEncode'),
            new \Twig_SimpleFilter('icon',          '\WebDocBook\Templating\Helper::getIcon'),
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('route',           '\WebDocBook\Templating\Helper::getRoute'),
            new \Twig_SimpleFunction('absoluteRoute',   '\WebDocBook\Templating\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFunction('relativePath',    '\WebDocBook\Templating\Helper::getRelativePath'),
            new \Twig_SimpleFunction('securedPath',     '\WebDocBook\Templating\Helper::getSecuredRealpath'),
            new \Twig_SimpleFunction('readableName',    '\WebDocBook\Templating\Helper::buildPageTitle'),
            new \Twig_SimpleFunction('rssEncode',       '\WebDocBook\Templating\Helper::rssEncode'),
            new \Twig_SimpleFunction('icon',            '\WebDocBook\Templating\Helper::getIcon'),
        );
    }

}

// Endfile
