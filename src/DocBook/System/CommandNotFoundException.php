<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\System;

use \RuntimeException;

class CommandNotFoundException extends RuntimeException
{

    public function __construct($command = '', $code = 0, Exception $previous = null)
    {
        parent::__construct(
            sprintf('The required binary command "%s" can\'t be found in your system!', $command),
            $code, $previous
        );
    }

}

// Endfile
