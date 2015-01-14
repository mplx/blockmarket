<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

if (php_sapi_name() !== 'cli') {
    die('script needs to be run from CLI');
}

require_once dirname(__FILE__) . '/../src/bootstrap.php';

$web = new \mplx\blockmarket\Service\Web();
$stocks = new \mplx\blockmarket\Util\Updater\UpdateStock($db, $web);
return $stocks->run();
