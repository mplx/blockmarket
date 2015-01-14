<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

// @codingStandardsIgnoreFile

// paths
define('BM_PATH_SRC', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('BM_PATH_INC', BM_PATH_SRC . 'includes' . DIRECTORY_SEPARATOR);
define('BM_PATH_TPL', BM_PATH_SRC . 'templates' . DIRECTORY_SEPARATOR);
define('BM_PATH_DATA', BM_PATH_SRC . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR);

// time zone
if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}

// find config file
$path_conf = BM_PATH_SRC . '..' . DIRECTORY_SEPARATOR . 'config.local.php';
if (file_exists($path_conf)) {
    include_once $path_conf;
}

// debugging
if (!defined('BM_DEBUG')) {
    define('BM_DEBUG', false);
}

if (BM_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_erors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// composer autoloader
$vendor_autoload = BM_PATH_SRC . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($vendor_autoload)) {
    die('autoloader missing - did you run composer!?');
}
require_once $vendor_autoload;

// blockmarket autoloader
spl_autoload_register(function($class) {
    $class = ltrim($class, '\\');
    $path_parts = explode('\\', $class);
    $filename = array_pop($path_parts);
    $path = BM_PATH_SRC . implode(DIRECTORY_SEPARATOR, $path_parts) .
            DIRECTORY_SEPARATOR .
            $filename . '.class.php';
    if (file_exists($path)) {
        require_once $path;
        return;
    }
}, true, true);

// list of include files
$includes = array (
    'config.inc.php'
);
foreach ($includes as $file) {
    include_once (BM_PATH_INC . $file);
}

// init db connection
$db = new mplx\blockmarket\Service\Database();
