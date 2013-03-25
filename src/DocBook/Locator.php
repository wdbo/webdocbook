<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use DocBook\FrontController;

/**
 */
class Locator
{

    public static function findPathReadme($path)
    {
        $readme = rtrim($path, '/').'/README.md';
        return file_exists($readme) ? $readme : null;
    }

    public function getPageAction($action)
    {
        $cfg = FrontController::getInstance()->getRegistry()->getConfig('page_types', array(), 'docbook');
        if (array_key_exists($action, $cfg)) {
            $_cls = 'DocBook\\Page\\'.ucfirst($cfg[$action]);
            if (class_exists($_cls)) {
                return $_cls;
            }
        }
        return null;
    }

    public function locateDocument($path)
    {
        if (file_exists($path)) {
            return $path;
        }
        $file_path = rtrim(FrontController::getInstance()->getPath('base_dir_http'), '/').'/'.trim($path, '/');
        if (file_exists($file_path)) {
            return $file_path;
        }
        return null;
    }
    
// ---------------------
// Fallback process
// ---------------------

    public function fallbackFinder($filename, $filetype = 'template')
    {
        $docbook = FrontController::getInstance();

        $base_path = 'template'===$filetype ? FrontController::TEMPLATES_DIR : FrontController::CONFIG_DIR;
        $file_path = rtrim($base_path, '/').'/'.$filename;
        
        // user first
        $user_file_path = rtrim($docbook->getPath('user_dir'), '/').'/'.$file_path;
        if (file_exists($user_file_path)) {
            return $user_file_path;
        }

        // default
        $def_file_path = rtrim($docbook->getPath('base_dir'), '/').'/'.$file_path;
        if (file_exists($def_file_path)) {
            return $def_file_path;
        }

        // else false        
        return false;
    }

}

// Endfile
