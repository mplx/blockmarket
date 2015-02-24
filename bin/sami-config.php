<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * php ../vendor/bin/sami.php update sami-config.php
 *
 * @package     blockmarket
 **/

if (php_sapi_name() !== 'cli') {
    die('script needs to be run from CLI');
}

require __DIR__ . '/../vendor/autoload.php';

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$dir = __DIR__ . '/../src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir);

$result = new Sami($iterator, array(
    'title' => 'Blockmarket',
    'build_dir' => __DIR__ . '/../docs/build/',
    'cache_dir' => __DIR__ . '/../docs/cache/',
    'default_opened_level' => 3,
));

return $result;
