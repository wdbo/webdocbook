<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use Patterns\Abstracts\AbstractResponse;
use Library\HttpFundamental\Response as BaseResponse;

use DocBook\Request;

/**
 * The global response class
 *
 * This is the global response of the application
 *
 * @author      Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Response extends BaseResponse
{

    /**
     * Constructor : defines the current URL and gets the routes
     */
    public function __construct($content = null, $type = null)
    {
        if (!is_null($content)) {
            $this->setContents(is_array($content) ? $content : array($content));
        }
        $this->setContentType(!is_null($type) ? $type : 'html');
    }

    /**
     * Send the response to the device
     */
    public function send($content = null, $type = null) 
    {
        if (!is_null($content)) {
            $this->setContents(is_array($content) ? $content : array($content));
        }
        if (!is_null($type)) {
            $this->setContentType($type);
        }
        return parent::send();
    }
    
}

// Endfile
