<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use \Exception;
use DocBook\FrontController;

/**
 */
class NotFoundException extends Exception
{

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $docbook = FrontController::getInstance();
        return $docbook->notFound( $message );
    }

}

// Endfile