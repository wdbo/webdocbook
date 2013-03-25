<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

use DocBook\NotFoundException;

/**
 */
abstract class AbstractPage
{

    public static $template_name = 'default';
    protected $path;

// ------------------
// Construction
// ------------------

    public function __construct($path = null)
    {
        if (!empty($path)) $this->setPath($path);
        $this->init();
    }
    
    protected function init(){}
    
    public function setPath($path)
    {
        if (file_exists($path)) {
            $this->path = $path;
        } else {
            throw new NotFoundException(
                sprintf('The requested page was not found (searching "%s")!', $path)
            );
        }
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

// ------------------
// Abstracts
// ------------------

    abstract public function parse();

}

// Endfile
