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

namespace WebDocBook\Filesystem;

use \WebDocBook\Helper as WDBHelper;
use \WebDocBook\Filesystem\Helper as FilesystemHelper;
use \WebDocBook\Model\File;
use \WebFilesystem\WebRecursiveDirectoryIterator;

/**
 * Class RecursiveDirectoryIterator
 */
class RecursiveDirectoryIterator
    extends WebRecursiveDirectoryIterator
{

    /**
     * New flag to make `current()` returns a `WDBFile` object
     *
     * It is part of the default object's flags
     */
    const CURRENT_AS_WDBFILE    = 0x00000040;

    /**
     * Constructor
     *
     * Default flags are :
     *
     *      WebFilesystemIterator::KEY_AS_PATHNAME | self::CURRENT_AS_WDBFILE | WebFilesystemIterator::SKIP_DOTTED
     *
     * @param string $path
     * @param int $flags
     * @param string $file_validation_callback
     * @param string $directory_validation_callback
     */
    public function __construct(
        $path, $flags = 16448,
        $file_validation_callback = 'WebDocBook\Filesystem\RecursiveDirectoryIterator::fileValidation',
        $directory_validation_callback = 'WebDocBook\Filesystem\RecursiveDirectoryIterator::dirValidation'
    ) {
        parent::__construct($path, $flags, $file_validation_callback, $directory_validation_callback);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function fileValidation($file_path)
    {
        return WDBHelper::isFileValid($file_path);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function dirValidation($file_path)
    {
        return WDBHelper::isDirValid($file_path);
    }

    /**
     * @return mixed
    */
    public function current()
    {
        if ($this->getFlags() & self::CURRENT_AS_WDBFILE) {
            return new File(
                FilesystemHelper::slashDirname($this->original_path).$this->getFilename()
            );
        }
        return parent::current();
    }

}

// Endfile
