<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

use DocBook\FrontController,
    DocBook\Abstracts\AbstractPage,
    DocBook\NotFoundException,
    DocBook\TemplateBuilder;

use Markdown\Parser,
    Markdown\ExtraParser;

/**
 */
abstract class AbstractController
{

    protected $path;
    protected $docbook;

// ------------------
// Construction
// ------------------

    public function __construct($path = null)
    {
        $this->docbook = FrontController::getInstance();
        if (!empty($path)) $this->setPath($path);
        $this->init();
    }
    
    protected function init(){}
    
// ------------------
// Path management
// ------------------

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


}

// Endfile
