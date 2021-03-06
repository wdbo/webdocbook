<?php
/**
 * This file is part of the WebDocBook package.
 *
 * Copyleft (ↄ) 2008-2017 Pierre Cassat <me@picas.fr> and contributors
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

namespace WebDocBook\Filesystem\WDBFileType;

use \WebDocBook\FrontController;
use \WebDocBook\Filesystem\WDBFileInterface;
use \WebFilesystem\WebFileInfo;

/**
 * Class Embed
 */
class Embed
    extends WebFileInfo
    implements WDBFileInterface
{

    /**
     * @param array $params
     * @return string
     */
    public function viewFileInfos(array $params = array())
    {
        return FrontController::getInstance()->display(
            file_get_contents($this->getRealPath()), 'file_content', $params
        );
    }

    /**
     * @param array $params
     * @return string
     */
    public function getIntroduction(array $params = array())
    {
        return htmlentities(file_get_contents($this->getRealPath()));
    }

}

// Endfile
