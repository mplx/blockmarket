<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

require_once 'src/bootstrap.php';

try {
    $router = new mplx\blockmarket\Router();
    $router->run();
} catch (\Exception $e) {
    echo "The server made a boo boo...";
}

