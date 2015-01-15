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

namespace DocBook\Composer\Script;

use \DocBook\Composer\Manager;
use \DocBook\Composer\ScriptInterface;
use \Composer\Script\Event;

/**
 * Class Initialize
 *
 * Initialize DocBook: install configuration files
 */
class Initialize
    implements ScriptInterface
{

    /**
     * @return string
     */
    public static function getName()
    {
        return 'docbook-init';
    }

    /**
     * @param   \Composer\Script\Event $event
     * @return  void
     * @throws  \Exception
     */
    public static function run(Event $event)
    {
        try {
            Manager::init($event, new self());
            if (\DocBook\Kernel::installConfig()) {
                Manager::info('installing configuration: user/config/');
            }
        } catch (\Exception $e) {
            Manager::error($e);
        }
    }

}

// Endfile