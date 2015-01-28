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
    'BlockMarketBot/1.0; https://github.com/mplx/blockmarket/'
);

// blockmarket data
define('BM_MARKETDATA_URL', 'http://blockmarket.theblockheads.net/');
define('BM_MARKETDATA_REGEX', '/' . 'graph\?item_id=([0-9]{1,4})">([A-Z ]+)<\/a><\/td><td>([0-9\.]+)<\/td>' . '/');
define('BM_WIKI_URL', 'http://theblockheadswiki.com/');

// development
define('BM_DEV_SOURCECODE', 'https://github.com/mplx/blockmarket/');
define('BM_DEV_ISSUE_TRACKER', 'https://github.com/mplx/blockmarket/issues');

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
