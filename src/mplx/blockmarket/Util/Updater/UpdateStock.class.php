<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\Updater;

use mplx\blockmarket\Service\Database;

class UpdateStock
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run()
    {
        $data = $this->fetchData(BM_MARKETDATA_URL);
        $result = $this->storeStocks($data);
        $result = $this->storePrices($data);
        return true;
    }

    private function fetchData($target)
    {
        $timestamp = date('Y-m-d G:i:s');
        $website = bm_curl_get($target);
        $result = preg_match_all(BM_MARKETDATA_REGEX, $website, $data);
        $stocks = array();
        foreach ($data[1] as $k => $d) {
            $stocks[] = array(
                'blockid'  => $d,
                'name' => $data[2][$k],
                'price' => $data[3][$k],
                'timestamp' => $timestamp
            );
        }
        return $stocks;
    }

    private function storeStocks($data)
    {
        $queries = array();
        $sql ="INSERT INTO stocks (id_stock, title) VALUES (%d, %s) ".
              "ON DUPLICATE KEY UPDATE title=VALUES(title)";
        foreach ($data as $d) {
            $queries[] = sprintf($sql, $d['blockid'], $this->db->pdo()->quote($d['name']));
        }
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }
        return true;
    }

    private function storePrices($data)
    {
        $queries = array();
        $sql = "INSERT INTO prices (stock_id, ts, marketvalue) VALUES (%d, %s, %f)";
        foreach ($data as $d) {
            $queries[] = sprintf($sql, $d['blockid'], $this->db->pdo()->quote($d['timestamp']), $d['price']);
        }
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }
        return true;
    }
}
