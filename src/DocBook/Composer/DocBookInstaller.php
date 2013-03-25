<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Composer;

use Composer\Composer,
    Composer\IO\IOInterface,
    Composer\Package\PackageInterface,
    Composer\Script\Event,
    Composer\Util\Filesystem;

/**
 * The installer for the Apache Markdown handler
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class DocBookInstaller
{

    const DOCBOOK_BINDIR = 'bin';

    /**
     */
    public static function postAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $package = $composer->getPackage();

        $filesystem = new Filesystem();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $vendorPath = strtr(realpath($vendorDir), '\\', '/');
        $appBasePath = str_replace($vendorDir, '', $vendorPath);

        $handler_path = rtrim($vendorPath, '/').'/atelierspierrot/extended-markdown/cgi-scripts/emd_apacheHandler.sh';
        $target_path = rtrim($appBasePath, '/').'/'.rtrim(self::DOCBOOK_BINDIR, '/').'/emd_apacheHandler.sh';
        if (file_exists($handler_path)) {
            if (file_exists($target_path)) {
                unlink($target_path);
            }
            copy($handler_path, $target_path);
        }
    }

}

// Endfile