<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

require_once dirname(__FILE__) . '/../src/bootstrap.php';

$migrations = new \mplx\blockmarket\Util\Migrations\Migrate($db);
return $migrations->run();
