<?php
/**
 */

// Show errors at least initially
@ini_set('display_errors','1'); @error_reporting(E_ALL ^ E_NOTICE);

// Set a default timezone to avoid PHP5 warnings
if (!isset($default_timezone)) $default_timezone = @date_default_timezone_get();
@date_default_timezone_set( isset($default_timezone) ? $default_timezone : 'Europe/Paris' );

// -----------------------------------
// Get Composer autoloader
// -----------------------------------

$composerAutoLoader = __DIR__.'/../src/vendor/autoload.php';
if (@file_exists($composerAutoLoader)) {
    require_once $composerAutoLoader;
} else {
    die(PHP_EOL."You need to run Composer on the project to build dependencies and auto-loading"
        ." (see: http://getcomposer.org/doc/00-intro.md#using-composer)!".PHP_EOL.PHP_EOL);
}

// -----------------------------------
// PROCESS
// -----------------------------------

// the query string
$uri = $_SERVER['REQUEST_URI'];

// the application 
$main = new \DocBook\FrontController();
if ($main) $main->setQueryString($uri)->distribute();
else trigger_error( "Main DocBook application can't be loaded!", E_USER_ERROR);

// Endfile
