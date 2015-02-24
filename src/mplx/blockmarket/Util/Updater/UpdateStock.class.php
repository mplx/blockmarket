<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\Updater;

use mplx\blockmarket\Service\Database;
use mplx\blockmarket\Service\Web;

/**
* Update stock data
*/
class UpdateStock
{
    /**
    * Database connection
    *
    * @var \mplx\blockmarket\Service\Database
    */
    protected $db;

    /**
    * Web service
    *
    * @var \mplx\blockmarket\Service\Web
    */
    protected $web;

    /**
    * Constructor
    *
    * @param \mplx\blockmarket\Service\Database $db
    * @param \mplx\blockmarket\Service\Web $web
    */
    public function __construct(Database $db, Web $web)
    {
        $this->db = $db;
        $this->web = $web;
    }

    /**
    * Run stock item updates
    *
    * @return true
    */
    public function run()
    {
        $data = $this->fetchData(BM_MARKETDATA_URL);
        $result = $this->storeStocks($data);
        $result = $this->storePrices($data);
        $result = $this->storePricesAvg($data);
        return true;
    }

    /**
    * Calculate and store daily avg prices
    *
    * @return true
    */
    public function storePricesAvg()
    {
        $queries = array();
        // @codingStandardsIgnoreStart
        $queries[] =
            "SELECT stock_id, DATE(ts) AS day, MAX(marketvalue) AS max_value, MIN(marketvalue) AS min_value, TRUNCATE(AVG(marketvalue),5) AS avg_value " .
            "FROM prices " .
            "WHERE (ts > (DATE_SUB(NOW(), INTERVAL 2 DAY)) AND ts < (DATE_SUB(NOW(), INTERVAL 1 DAY))) " .
            "GROUP BY stock_id";

        $queries[] = "REPLACE INTO prices_avg(stock_id, date, daily_max, daily_min, daily_avg) " .
                    "SELECT stock_id, DATE(ts) AS day, MAX(marketvalue) AS max_value, MIN(marketvalue) AS min_value, TRUNCATE(AVG(marketvalue),5) AS avg_value " .
                    "FROM prices " .
                    "WHERE DATE(ts) = DATE(NOW()) " .
                    "GROUP BY stock_id";
            // @codingStandardsIgnoreEnd
        // execute
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }
        return true;
    }

    /**
    * Reduce prices and optimize database table
    *
    * @return true
    */
    public function optimize()
    {
        $queries = array();
        // optimize prices table
        $queries[] = "DELETE FROM prices WHERE (ts < (DATE_SUB(NOW(), INTERVAL 14 DAY)))";
        $queries[] = "OPTIMIZE TABLE prices";
        // execute
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }
        return true;
    }

    /**
    * Fetch items and prices from website
    *
    * @param string $target
    * @return array
    */
    private function fetchData($target)
    {
        $timestamp = date('Y-m-d G:i:s');
        $website = $this->web->getUrl($target);
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

    /**
    * Store fetched items into database
    *
    * @param array $data
    * @return true
    */
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

    /**
    * Store fetched prices into database
    *
    * @param array $data
    * @return true
    */
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
