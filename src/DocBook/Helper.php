<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

/**
 */
class Helper
{

    public static function buildPageTitle($filename)
    {
        $name = basename($filename);
        return ucfirst(
            str_replace(array('_', '.'), ' ', 
                str_replace('.md', '', $name)
            )
        );

    }

    public static function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            if (file_exists($directory)) {
                throw new \RuntimeException(
                    sprintf('"%s" exists and is not a directory!', $directory)
                );
            }
            if (!@mkdir($directory, 0777, true)) {
                throw new \RuntimeException(
                    sprintf('An error occured while trying to create directory "%s"!', $directory)
                );
            }
        }
    }

    public static function getDateTimeFromTimestamp($timestamp)
    {
        $time = new \DateTime;
        $time->setTimestamp( $timestamp );
        return $time;
    }

    public static function findPathReadme($path)
    {
        $readme = rtrim($path, '/').'/README.md';
        return file_exists($readme) ? $readme : null;
    }

    public static function getRoute($path, $type = null)
    {
        $route = $path;
        $docbook = \DocBook\FrontController::getInstance();
        $rel_path = str_replace($docbook->getPath('base_dir_http'), '', $path);

        return '/'.trim($rel_path, '/').(!empty($type) ? '/'.$type : '');
    }

}

// Endfile
