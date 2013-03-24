<?php

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
        );
    }
}

// Endfile