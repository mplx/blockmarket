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

$importer = new \mplx\blockmarket\Util\ImportExport\Import($db);
return $importer->run(BM_PATH_DATA);
