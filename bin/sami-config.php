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
use Sami\Version\GitVersionCollection;

$dir = __DIR__ . '/../src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir);

$versions = GitVersionCollection::create($dir)
    ->add('master', 'Blockmarket Dev');

$result = new Sami($iterator, array(
    'title' => 'Blockmarket',
    'versions' => $versions,
    'build_dir' => __DIR__ . '/../docs/build/%version%',
    'cache_dir' => __DIR__ . '/../docs/cache/%version%',
    'default_opened_level' => 2,
));

return $result;
