<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use Patterns\Abstracts\AbstractResponse;

/**
 * The global response class
 *
 * This is the global response of the application
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Response extends AbstractResponse
{

	/**
	 * The response character set
	 */
	protected $charset = 'utf-8';

	/**
	 * The content type
	 *
	 * @see App\Response::$content_types
	 */
	protected $content_type = 'html';

	/**
	 * The POST arguments
	 */
	static $content_types = array(
		'html' => 'text/html',
		'text' => 'text/plain',
		'css' => 'text/css',
		'xml' => 'application/xml',
		'javascript' => 'application/x-javascript',
	);

	/**
	 * Constructor : defines the current URL and gets the routes
	 */
	public function __construct( $content=null, $type=null )
	{
		if (!is_null($content)) $this->setBody( $content );
		$this->setContentType( $this->content_type );
		if (!is_null($type)) $this->setContentType( $type );
	}

	/**
	 */
	public function setContentType( $type ) 
	{
		if (array_key_exists($type, self::$content_types))
			$this->content_type = self::$content_types[ $type ];
	}

    public function redirect($url, $permanent = false)
    {
        if ($permanent) {
            $this->addHeader('Status', '301 Moved Permanently');
        } else {
            $this->addHeader('Status', '302 Found');
        }
        $this->addHeader('location', $url);
    }
    
	/**
	 * Send the response to the device
	 */
	public function send($content = null, $type = null) 
	{
	    if (!is_null($content)) {
	        $this->setBody($content);
	    }
	    if (!is_null($type)) {
	        $this->setContentType($type);
	    }
	    if (!$this->hasHeader('Content-type')) {
    		$this->addHeader('Content-type', $this->content_type.'; charset='.strtoupper($this->charset));
	    }
		if ($this->content_type=='text/plain')
		{
			$_escaped_output = strip_tags( $this->getBody() );
			if ($_escaped_output != $this->getBody())
			{
				if (preg_match('/(.*)<body(.*)</body>/i', $this->getBody(), $matches))
				{
					$_output = $matches[0];
				} else
					$_output = $this->getBody();
				$this->setBody( strip_tags( str_replace('<br />', "\n", $_output) ) );
			}
		}
	    $this->renderHeaders();
		echo $this->getBody();
		exit;
	}

	/**
	 * Force device to download a file
	 */
	public function download($file = null, $type = null, $file_name = null) 
	{
		if (!empty($file) && @file_exists($file)) {
			if (is_null($file_name)) 
			  $file_name = end( explode('/', $file) );
			$this
			    ->addHeader('Content-disposition', 'attachment; filename='.$file_name)
			    ->addHeader('Content-Type', 'application/force-download')
			    ->addHeader('Content-Transfer-Encoding', $type."\n")
			    ->addHeader('Content-Length', filesize($file))
			    ->addHeader('Pragma', 'no-cache')
			    ->addHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0, public')
			    ->addHeader('Expires', '0'); 
    	    $this->renderHeaders();
			readfile( $file );
			exit;
		}
		return;
	}

	/**
	 * Flush (display) a file content
	 */
	public function flush($file_content = null, $type = null) 
	{
		if (!empty($file_content)) {
			if (empty($type)) {
				$finfo = new \finfo();
				$type = $finfo->buffer( $file_content, FILEINFO_MIME );
	    	}
			$this->setHeaders(array('Content-Type'=>$type));
    	    $this->renderHeaders();
			echo $file_content;
			exit;
		}
		return;
	}

}

// Endfile