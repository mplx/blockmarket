<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\ImportExport;

use mplx\blockmarket\Service\Database;

class Import
{
    protected $db;

    protected $compatibility_min;
    protected $compatibility_max;

    protected $metadata_file;
    protected $stocks_file;
    protected $stocks_hash;
    protected $receipts_file;
    protected $receipts_hash;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->compatibility_min = 3;
        $this->compatibility_max= 4;

        $this->metadata_file = 'export.json';
        $this->stocks_file = null;
        $this->receipts_file = null;
    }

    public function run($path, $hashcheck = true)
    {
        $queries = array();
        $queries[] = "TRUNCATE stocks;";
        $queries[] = "TRUNCATE receipts;";

        // check database
        if (! $this->db->getStatus()) {
            die('ERROR: Database not connected?');
        }
        if (! $this->db->tableExists('config')) {
            die('ERROR: Database does not exist?');
        }

        $schema = $this->db->getConf('schema');
        echo "Found database schema v" . $schema . PHP_EOL;

        // Compatibility check
        if ($schema < $this->compatibility_min || $schema > $this->compatibility_max) {
            die('ERROR: Importer is not compatible with your current database version');
        }

        // Metadata
        if (! file_exists($path . $this->metadata_file)) {
            die('ERROR: Dump metafile not found!?');
        }
        echo "Fetching dump metadata from " . $this->metadata_file . PHP_EOL;
        $metadata = json_decode(file_get_contents($path . $this->metadata_file));
        if (! is_object($metadata) || !isset($metadata->schema)) {
            die('ERROR: Metadata corrupted!?');
        }
        if ($metadata->schema != $schema) {
            die('ERROR: Dump (v' . $metadata->schema . ') does not match database schema(v' . $schema . ')!?');
        }

        foreach ($metadata->files as $file) {
            switch ($file->data) {
                case 'stocks':
                    $this->stocks_file = $file->name;
                    $this->stocks_hash = $file->md5;
                    break;
                case 'receipts':
                    $this->receipts_file = $file->name;
                    $this->receipts_hash = $file->md5;
                    break;
                default:
                    die('ERROR: unknown data type in metafile!?');
                    break;
            }
        }

        // Hashcheck
        if ($hashcheck) {
            if ($this->stocks_hash != md5_file($path . $this->stocks_file)) {
                die('ERROR: invalid hash for stocks dump!?');
            }
            if ($this->receipts_hash != md5_file($path . $this->receipts_file)) {
                die('ERROR: invalid hash for receipts dump!?');
            }
        }

        // Importing stocks
        echo "Fetching stocks dump from " . $this->stocks_file . PHP_EOL;
        $stocks = json_decode(file_get_contents($path . $this->stocks_file));
        if (! is_array($stocks) || !isset($stocks[0]->id_stock)) {
            die('ERROR: stocks dump corrupted!?');
        }

        $sql = "INSERT INTO stocks VALUES(%d, %s, %s, %s, %s, %d, %s)";
        foreach ($stocks as $row) {
            $queries[] = sprintf(
                $sql,
                $row->id_stock,
                $this->db->quoteOrNull($row->title),
                $this->db->quoteOrNull($row->title_original),
                $this->db->quoteOrNull($row->title_wiki),
                $this->db->quoteOrNull($row->icon_path),
                $row->enabled,
                $this->db->quoteOrNull($row->lastmodified)
            );
        }

        // Importing crafting receipts
        echo "Fetching receipts dump from " . $this->receipts_file . PHP_EOL;
        $receipts = json_decode(file_get_contents($path . $this->receipts_file));
        if (! is_array($receipts) || !isset($receipts[0]->id_receipt)) {
            die('ERROR: receipts dump corrupted!?');
        }

        $sql = "INSERT INTO receipts VALUES(%d, %d, %d, %f, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
        foreach ($receipts as $row) {
            $receipt = sprintf(
                $sql,
                $row->id_receipt,
                $row->target_id,
                $row->target_qty,
                $row->tc_rush,
                $row->tc,
                $this->db->nullToNull($row->ingredient_1_id),
                $this->db->nullToNull($row->ingredient_1_qty),
                $this->db->nullToNull($row->ingredient_2_id),
                $this->db->nullToNull($row->ingredient_2_qty),
                $this->db->nullToNull($row->ingredient_3_id),
                $this->db->nullToNull($row->ingredient_3_qty),
                $this->db->nullToNull($row->ingredient_4_id),
                $this->db->nullToNull($row->ingredient_4_qty),
                $this->db->nullToNull($row->ingredient_5_id),
                $this->db->nullToNull($row->ingredient_5_qty),
                $this->db->quoteOrNull($row->lastmodified)
            );
            $queries[] = $receipt;
        }

        // execute sql
        echo "Importing to database" . PHP_EOL;
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }

        // done
        echo "Done" . PHP_EOL;
        return true;
    }
}
