<?php
/**
 * PHP / Markdown Extended : DocBook
 * @author      Pierre Cassat & contributors
 * @package     DocBook
 * @copyleft    Les Ateliers Pierrot <ateliers-pierrot.fr>
 * @license     GPL-v3
 * @sources     http://github.com/atelierspierrot/docbook
 */

use \Twig_Extension, \Twig_SimpleFilter, \Twig_SimpleFunction;

class DocBook_Twig_Extension extends \Twig_Extension
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
