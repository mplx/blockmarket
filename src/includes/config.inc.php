<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

// version
define('BM_VERSION', '1.0.0');

// curl
define('BM_CURL_TIMEOUT', 10);
define(
    'BM_CURL_USERAGENT',
    'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36'
);

// blockmarket data
define('BM_MARKETDATA_URL', 'http://blockmarket.theblockheads.net/');
define('BM_MARKETDATA_REGEX', '/' . 'graph\?item_id=([0-9]{1,4})">([A-Z ]+)<\/a><\/td><td>([0-9\.]+)<\/td>' . '/');
define('BM_WIKI_URL', 'http://theblockheadswiki.com/');

// theme
define('BM_THEME', 'default');

// DB credentials via cli/server environment
if (!defined('BM_DB_ENV_HOST_NAME')) {
    define('BM_DB_ENV_HOST_NAME', 'DB1_HOST');
}
if (!defined('BM_DB_ENV_USER_NAME')) {
    define('BM_DB_ENV_USER_NAME', 'DB1_USER');
}
if (!defined('BM_DB_ENV_PASS_NAME')) {
    define('BM_DB_ENV_PASS_NAME', 'DB1_PASS');
}
if (!defined('BM_DB_ENV_DBNAME_NAME')) {
    define('BM_DB_ENV_DBNAME_NAME', 'DB1_NAME');
}
if (!defined('BM_DB_ENV_PORT_NAME')) {
    define('BM_DB_ENV_PORT_NAME', 'DB1_PORT');
}
