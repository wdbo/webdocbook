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

namespace WebDocBook\Abstracts;

use \WebDocBook\FrontController;
use \WebDocBook\Exception\NotFoundException;

/**
 * Class AbstractController
 *
 * Any controller MUST extend this one
 */
abstract class AbstractController
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \WebDocBook\FrontController
     */
    protected $wdb;

    /**
     * @param string $path
     * @throws \WebDocBook\Exception\NotFoundException
     */
    public function __construct($path = null)
    {
        $this->wdb = FrontController::getInstance();
        if (!empty($path)) {
            $this->setPath($path);
        }
        $this->init();
    }

    /**
     * Singleton `init()`
     */
    protected function init(){}

    /**
     * Define the path of the document to treat
     *
     * @param string $path
     * @return $this
     * @throws \WebDocBook\Exception\NotFoundException
     */
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

    /**
     * Get the path of the document to treat
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

}

// Endfile
