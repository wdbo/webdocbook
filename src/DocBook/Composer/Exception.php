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

namespace DocBook\Composer;

use \DocBook\Composer\ScriptInterface;

/**
 * Class Exception
 */
class Exception
    extends \Exception
{

    /**
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     * @param \DocBook\Composer\ScriptInterface $cmd
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, ScriptInterface $cmd = null)
    {
        $previous_msg = !is_null($previous) ? $previous->getMessage() : null;
        $composer_msg = !is_null($cmd) ? $cmd->getName() : null;
        $message =
            "[DocBook] An error occurred while trying to execute a script ...".PHP_EOL
            .(!is_null($composer_msg) ? "You should correct the error and try: 'composer $composer_msg'.".PHP_EOL : '')
            .(!is_null($previous_msg) ? "Caught exception: '$previous_msg'." : '')
        ;
        parent::__construct($message, $code, $previous);
    }

}

// Endfile