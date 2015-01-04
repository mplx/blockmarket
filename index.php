<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

require_once 'src/bootstrap.php';

$router = new mplx\blockmarket\Router();
$router->run();
