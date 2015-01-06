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

namespace DocBook\WebFilesystem;

use \DocBook\FrontController;
use \DocBook\Helper;
use \Library\Helper\Directory as DirectoryHelper;
use \WebFilesystem\FilesystemIterator;
use \WebFilesystem\WebRecursiveDirectoryIterator;

/**
 */
class DocBookRecursiveDirectoryIterator
    extends WebRecursiveDirectoryIterator
{

    /**
     * New flag to make `current()` returns a `DocBookFile` object
     *
     * It is part of the default object's flags
     */
    const CURRENT_AS_DOCBOOKFILE    = 0x00000040;

    /**
     * Default flags are : WebFilesystemIterator::KEY_AS_PATHNAME | self::CURRENT_AS_DOCBOOKFILE | WebFilesystemIterator::SKIP_DOTTED
     */
    public function __construct(
        $path, $flags = 16448,
        $file_validation_callback = 'DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::fileValidation',
        $directory_validation_callback = 'DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::dirValidation'
    ) {
        parent::__construct($path, $flags, $file_validation_callback, $directory_validation_callback);
    }

    public static function fileValidation($file_path)
    {
        return Helper::isFileValid($file_path);
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_INTERFACE && 
            $name!==FrontController::README_FILE
        );
    }
    
    public static function dirValidation($file_path)
    {
        return Helper::isDirValid($file_path);
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_ASSETS
        );
    }

    /**
     * @return mixed
    */
    public function current()
    {
        if ($this->getFlags() & self::CURRENT_AS_DOCBOOKFILE) {
            return new DocBookFile(
                DirectoryHelper::slashDirname($this->original_path).$this->getFilename()
            );
        }
        return parent::current();
    }

}

// Endfile
