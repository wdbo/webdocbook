<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

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
            new \Twig_SimpleFilter('relpath', '\DocBook\Helper::getRealPath'),
            new \Twig_SimpleFilter('relativePath', '\DocBook\Helper::getRelativePath'),
            new \Twig_SimpleFilter('securedPath', '\DocBook\Helper::getSecuredRealpath'),            
            new \Twig_SimpleFilter('readableName', '\DocBook\Helper::buildPageTitle'),            
        );
    }
}

// Endfile
