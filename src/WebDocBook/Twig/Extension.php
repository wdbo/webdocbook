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

use \Twig_Extension;
use \Twig_SimpleFilter;
use \Twig_SimpleFunction;

/**
 * Class WebDocBook_Twig_Extension
 *
 * The Twig functions and filters for WebDocBook's templates
 *
 * @see http://twig.sensiolabs.org/api/master/index.html
 */
class WebDocBook_Twig_Extension
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
            new \Twig_SimpleFilter('route',         '\WebDocBook\Helper::getRoute'),
            new \Twig_SimpleFilter('absoluteRoute', '\WebDocBook\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFilter('relpath',       '\WebDocBook\Helper::getRealPath'),
            new \Twig_SimpleFilter('relativePath',  '\WebDocBook\Helper::getRelativePath'),
            new \Twig_SimpleFilter('securedPath',   '\WebDocBook\Helper::getSecuredRealpath'),
            new \Twig_SimpleFilter('readableName',  '\WebDocBook\Helper::buildPageTitle'),
            new \Twig_SimpleFilter('rssEncode',     '\WebDocBook\Helper::rssEncode'),
            new \Twig_SimpleFilter('icon',          '\WebDocBook\Helper::getIcon'),
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('route',           '\WebDocBook\Helper::getRoute'),
            new \Twig_SimpleFunction('absoluteRoute',   '\WebDocBook\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFunction('relpath',         '\WebDocBook\Helper::getRealPath'),
            new \Twig_SimpleFunction('relativePath',    '\WebDocBook\Helper::getRelativePath'),
            new \Twig_SimpleFunction('securedPath',     '\WebDocBook\Helper::getSecuredRealpath'),
            new \Twig_SimpleFunction('readableName',    '\WebDocBook\Helper::buildPageTitle'),
            new \Twig_SimpleFunction('rssEncode',       '\WebDocBook\Helper::rssEncode'),
            new \Twig_SimpleFunction('icon',            '\WebDocBook\Helper::getIcon'),
        );
    }

}

// Endfile
