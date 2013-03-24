<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

/**
 */
abstract class AbstractPage
{

    public static $template_name = 'default';
    protected $path;

    abstract public function parse();

    public function __construct($path = null)
    {
        if (!empty($path)) $this->setPath($path);
        $this->init();
    }
    
    protected function init(){}
    
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

}

// Endfile
