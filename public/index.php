<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

require_once '../src/bootstrap.php';

try {
    $router = new mplx\blockmarket\Router();
    $router->run();
} catch (\Exception $e) {
    if (BM_DEBUG) {
        echo "<h1>The server made a boo boo...</h1>";
        echo "<h2>Debug Mode</h2>";
        echo $e->getMessage();
    } else {
        echo "The server made a boo boo...";
    }
}
