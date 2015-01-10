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
        $fields = array(
            array ('name'=>'title_original', 'type'=>'%s'),
            array ('name'=>'title_wiki', 'type'=>'%s'),
        );
        $qfields = $qdirective = $qupdate = '';
        foreach ($fields as $f) {
            $qfields .= ', ' . $f['name'];
            $qdirective .= ', ' . $f['type'];
            $qupdate .= ', ' . $f['name'] . '=VALUES(' . $f['name'] . ')';
        }
        $sql ="INSERT INTO stocks (id_stock, title" . $qfields . ") VALUES (%d, %s" . $qdirective . ") ".
              "ON DUPLICATE KEY UPDATE title=VALUES(title)" . $qupdate;

        $queries = array();
        foreach ($data as $d) {
            $title_org = $d['name'];
            $title = preg_replace_callback(
                '/(?<=\s|^)[a-z]/',
                function($match) {
                    return strtoupper($match[0]);
                },
                strtolower($title_org)
            );
            $title_wiki = str_replace(' ', '_', $title);

            $queries[] = sprintf(
                $sql,
                $d['blockid'],
                $this->db->pdo()->quote($title),
                $this->db->pdo()->quote($title_org),
                $this->db->pdo()->quote($title_wiki)
            );
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
