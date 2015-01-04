<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

require_once dirname(__FILE__) . '/../src/bootstrap.php';

$stocks = new \mplx\blockmarket\Util\Updater\UpdateStock($db);
return $stocks->run();
