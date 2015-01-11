<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\ImportExport;

use mplx\blockmarket\Service\Database;

class Export
{
    protected $db;

    protected $file_stocks;
    protected $file_receipts;
    protected $file_export;

    public function __construct(Database $db)
    {
        $this->db = $db;

        $this->file_export = 'export.json';
        $this->file_stocks = 'stocks.json';
        $this->file_receipts = 'receipts.json';
    }

    public function run($path)
    {
        if (! $this->db->getStatus()) {
            die('Database not connected?');
        }
        if (! $this->db->tableExists('config')) {
            die('Database does not exist?');
        }

        $schema = $this->db->getConf('schema');
        echo "Found database schema v" . $schema . PHP_EOL;

        echo "Exporting to " . $path . $this->file_stocks . PHP_EOL;
        $query = "SELECT * FROM stocks ORDER BY title ASC";
        $stocks = $this->db->query($query);
        file_put_contents($path . $this->file_stocks, json_encode($stocks, JSON_PRETTY_PRINT));

        echo "Exporting to " . $path . $this->file_receipts . PHP_EOL;
        $query = "SELECT * FROM receipts ORDER BY target_id ASC";
        $receipts = $this->db->query($query);
        file_put_contents($path . $this->file_receipts, json_encode($receipts, JSON_PRETTY_PRINT));

        echo "Exporting to " . $path . $this->file_export . PHP_EOL;
        $export = array(
            'schema' => $schema,
            'files' => array (
                array(
                    'data' => 'stocks',
                    'name' => $this->file_stocks,
                    'md5' => md5_file($path . $this->file_stocks)
                ),
                array(
                    'data' => 'receipts',
                    'name' => $this->file_receipts,
                    'md5' => md5_file($path . $this->file_receipts)
                )
            ),
            'exported' => date('c'),
            'timestamp' => date('U')
        );
        file_put_contents($path . $this->file_export, json_encode($export, JSON_PRETTY_PRINT));

        echo "Done" . PHP_EOL;
        return true;
    }
}
