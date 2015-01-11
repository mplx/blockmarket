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

$exporter = new \mplx\blockmarket\Util\ImportExport\Export($db);
return $exporter->run(BM_PATH_DATA);
