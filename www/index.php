<?php
/**
 * PHP / Markdown Extended : DocBook
 * @author      Pierre Cassat & contributors
 * @package     DocBook
 * @copyleft    Les Ateliers Pierrot <ateliers-pierrot.fr>
 * @license     GPL-v3
 * @sources     http://github.com/atelierspierrot/docbook
 */

/**
 * Show errors at least initially
 *
 * `E_ALL` => for hard dev
 * `E_ALL & ~E_STRICT` => for hard dev in PHP5.4 avoiding strict warnings
 * `E_ALL & ~E_NOTICE & ~E_STRICT` => classic setting
 */
//@ini_set('display_errors','1'); @error_reporting(E_ALL);
//@ini_set('display_errors','1'); @error_reporting(E_ALL & ~E_STRICT);
@ini_set('display_errors','1'); @error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// Set a default timezone to avoid PHP5 warnings
$dtmz = @date_default_timezone_get();
@date_default_timezone_set( $dtmz?:'Europe/London' );

// Get Composer autoloader
$composerAutoLoader = __DIR__.'/../src/vendor/autoload.php';
if (@file_exists($composerAutoLoader)) {
    require_once $composerAutoLoader;
} else {
    die("You need to run Composer on the project to build dependencies and auto-loading"
    ." (see: <a href=\"http://getcomposer.org/doc/00-intro.md#using-composer\">http://getcomposer.org/doc/00-intro.md#using-composer</a>)!");
}

// uncomment in dev mode
define('DOCBOOK_MODE', 'dev');

// the application 
\DocBook\FrontController::getInstance()->distribute();

// Endfile
