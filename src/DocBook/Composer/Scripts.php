<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Composer;

use Composer\Script\Event;

/**
 */
class Scripts
{

    public static function emptyCache(Event $event)
    {
        $_ds = DIRECTORY_SEPARATOR;
        $composer = $event->getComposer();
        $io = $event->getIO();
        $app_base_path = realpath(__DIR__.'/../../..') . $_ds;
        if (self::remove($app_base_path.'tmp')) {
            $io->write( '<info>Docbook cache has been cleared</info>' );
        }
    }

    /**
     */
    public static function remove($path)
    {
        $_ds = DIRECTORY_SEPARATOR;
        if (file_exists($path)) {
            if (is_file($path)) {
                return unlink($path);
            }
            $iterator = new \DirectoryIterator($path);
            foreach($iterator as $item) {
                if (!in_array($item, array('.', '..'))) {
                    $fullpath = rtrim($path, '/') . $_ds . $item;
                    if (is_file($fullpath)) {
                        unlink($fullpath);
                    } else {
                        self::remove($fullpath);
                    }
                }
            } 
            return rmdir($path);
        }
        return false;
    }

}

// Endfile
