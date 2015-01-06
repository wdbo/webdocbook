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

namespace DocBook\Abstracts;

use \DocBook\FrontController;
use \DocBook\NotFoundException;

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
