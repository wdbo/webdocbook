<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem;

use DocBook\FrontController;

use WebFilesystem\FilesystemIterator,
    WebFilesystem\WebRecursiveDirectoryIterator;

/**
 */
class DocBookRecursiveDirectoryIterator extends WebRecursiveDirectoryIterator
{

    public function __construct(
        $path, $flags = 16432,
        $file_validation_callback = "DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::fileValidation",
        $directory_validation_callback = "DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator::dirValidation"
    ) {
        parent::__construct($path, $flags, $file_validation_callback, $directory_validation_callback);
    }

    public static function fileValidation($file_path)
    {
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_INTERFACE && 
            $name!==FrontController::README_FILE
        );
    }
    
    public static function dirValidation($file_path)
    {
        $name = basename($file_path);
        return (
            $name!==FrontController::DOCBOOK_ASSETS
        );
    }

}

// Endfile
