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

use \Twig_Extension;
use \Twig_SimpleFilter;
use \Twig_SimpleFunction;

class DocBook_Twig_Extension
    extends \Twig_Extension
{

    public function getName()
    {
        return 'DocBook';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('route', '\DocBook\Helper::getRoute'),
            new \Twig_SimpleFilter('absoluteRoute', '\DocBook\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFilter('relpath', '\DocBook\Helper::getRealPath'),
            new \Twig_SimpleFilter('relativePath', '\DocBook\Helper::getRelativePath'),
            new \Twig_SimpleFilter('securedPath', '\DocBook\Helper::getSecuredRealpath'),            
            new \Twig_SimpleFilter('readableName', '\DocBook\Helper::buildPageTitle'), 
            new \Twig_SimpleFilter('rssEncode', '\DocBook\Helper::rssEncode'), 
            new \Twig_SimpleFilter('icon', '\DocBook\Helper::getIcon'), 
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('route', '\DocBook\Helper::getRoute'),
            new \Twig_SimpleFunction('absoluteRoute', '\DocBook\Helper::getAbsoluteRoute'),
            new \Twig_SimpleFunction('relpath', '\DocBook\Helper::getRealPath'),
            new \Twig_SimpleFunction('relativePath', '\DocBook\Helper::getRelativePath'),
            new \Twig_SimpleFunction('securedPath', '\DocBook\Helper::getSecuredRealpath'),            
            new \Twig_SimpleFunction('readableName', '\DocBook\Helper::buildPageTitle'), 
            new \Twig_SimpleFunction('rssEncode', '\DocBook\Helper::rssEncode'), 
            new \Twig_SimpleFunction('icon', '\DocBook\Helper::getIcon'), 
        );
    }

}

// Endfile
