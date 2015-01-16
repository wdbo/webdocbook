<?php
/**
 * This file is part of the WebDocBook package.
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook\Exception;

use \WebDocBook\FrontController;
use \WebDocBook\Kernel;

/**
 * Class RuntimeException
 *
 * WebDocBook components should use this in place of classic `\RuntimeException`
 */
class RuntimeException
    extends \RuntimeException
{

    /**
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $wdb = FrontController::getInstance();
        if ($wdb) {
            $wdb->log(
                $this->getMessage(), $code, array(
                'exception'=>$this
            ), 'error');
            if (!Kernel::isDevMode()) {
                $wdb->display('', 'error', array('message'=>$message), true);
            }
        }

    }
}

// Endfile