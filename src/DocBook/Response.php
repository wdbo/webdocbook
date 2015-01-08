<?php
/**
 * This file is part of the DocBook package.
 *
 * Copyleft (ↄ) 2008-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/docbook>.
 */

namespace DocBook;

use \Patterns\Abstracts\AbstractResponse;
use \Library\HttpFundamental\Response as BaseResponse;

/**
 * Class Response
 *
 * This is the global response of the application
 *
 * @package DocBook
 */
class Response
    extends BaseResponse
{

    /**
     * Constructor : defines the current URL and gets the routes
     * @param null $content
     * @param null $type
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
     * @param null $content
     * @param null $type
     * @return mixed
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

    /**
     * Make a redirection to a new route (HTTP redirect)
     * @param mixed $pathinfo The path information to redirect to
     * @param string $hash A hash tag to add to the generated URL
     */
    public function redirect($pathinfo, $hash = null)
    {
        $uri = is_string($pathinfo) ? $pathinfo : $this->generateUrl($pathinfo);
        if (!headers_sent()) {
            header("Location: $uri");
        } else {
            echo <<<MESSAGE
<!DOCTYPE HTML>
<head>
<meta http-equiv='Refresh' content='0; url={$uri}'><title>HTTP 302</title>
</head><body>
<h1>HTTP 302</h1>
<p>Your browser will be automatically redirected.
<br />If not, please click on next link: <a href="{$uri}">{$uri}</a>.</p>
</body></html>
MESSAGE;
        }
        exit;
    }

}

// Endfile
