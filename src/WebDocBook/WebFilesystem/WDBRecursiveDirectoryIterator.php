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

namespace WebDocBook\WebFilesystem;

use \WebDocBook\FrontController;
use \WebDocBook\Helper;
use \WebDocBook\Util\Filesystem;
use \WebFilesystem\FilesystemIterator;
use \WebFilesystem\WebRecursiveDirectoryIterator;

/**
 * Class WDBRecursiveDirectoryIterator
 */
class WDBRecursiveDirectoryIterator
    extends WebRecursiveDirectoryIterator
{

    /**
     * New flag to make `current()` returns a `WDBFile` object
     *
     * It is part of the default object's flags
     */
    const CURRENT_AS_DOCBOOKFILE    = 0x00000040;

    /**
     * Constructor
     *
     * Default flags are :
     *
     *      WebFilesystemIterator::KEY_AS_PATHNAME | self::CURRENT_AS_DOCBOOKFILE | WebFilesystemIterator::SKIP_DOTTED
     *
     */
    public function __construct(
        $path, $flags = 16448,
        $file_validation_callback = 'WebDocBook\WebFilesystem\WDBRecursiveDirectoryIterator::fileValidation',
        $directory_validation_callback = 'WebDocBook\WebFilesystem\WDBRecursiveDirectoryIterator::dirValidation'
    ) {
        parent::__construct($path, $flags, $file_validation_callback, $directory_validation_callback);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function fileValidation($file_path)
    {
        return Helper::isFileValid($file_path);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function dirValidation($file_path)
    {
        return Helper::isDirValid($file_path);
    }

    /**
     * @return mixed
    */
    public function current()
    {
        if ($this->getFlags() & self::CURRENT_AS_DOCBOOKFILE) {
            return new WDBFile(
                Filesystem::slashDirname($this->original_path).$this->getFilename()
            );
        }
        return parent::current();
    }

}

// Endfile
