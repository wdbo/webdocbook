<?php
/**
 * PHP / Markdown Extended : DocBook
 * @author      Pierre Cassat & contributors
 * @package     DocBook
 * @copyleft    Les Ateliers Pierrot <ateliers-pierrot.fr>
 * @license     GPL-v3
 * @sources     http://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem;

use \DocBook\FrontController,
    \DocBook\Helper;

use \Library\Helper\Directory as DirectoryHelper;

use \WebFilesystem\FilesystemIterator,
    \WebFilesystem\WebRecursiveDirectoryIterator;

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
        $file_validation_callback = "DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::fileValidation",
        $directory_validation_callback = "DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::dirValidation"
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
