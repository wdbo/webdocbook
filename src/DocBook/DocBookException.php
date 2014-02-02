<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use \DocBook\FrontController,
use \Exception;

class DocBookException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        FrontController::getInstance()->log(
            $this->getMessage(), $code, array(
                'exception'=>$this
            ), 'error');
    }
}

// Endfile