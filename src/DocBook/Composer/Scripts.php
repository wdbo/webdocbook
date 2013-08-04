<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
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
        if (file_exists($path) && is_dir($path)) {
            if (!is_dir($path) || is_link($path)) {
                return unlink($path);
            }
            $ok = true;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path), 
                \RecursiveIteratorIterator::SELF_FIRST | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
            );
            foreach($iterator as $item) {
                if (in_array($item->getFilename(), array('.', '..'))) {
                    continue;
                }
                if ($item->isDir()) {
                    $ok = self::remove($item);
                } else {
                    $ok = unlink($item);
                }
            }
            if ($ok) rmdir($path);
            clearstatcache();
            return true;
        }
        return false;
    }

}

// Endfile
