<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\BlockMarket;

/**
* Blockmarket data
*/
class BlockData
{
    /**
    * Database connection
    *
    * @var \PDO
    */
    protected $db;

    /**
    * Constructor
    *
    * @param \PDO $db
    */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
    * Get ID from stock title
    *
    * @param string $stock
    * @return int|false
    */
    public function getStockId($stock = null)
    {
        if ($stock) {
            $query = sprintf(
                "SELECT id_stock, title, title_wiki, icon_path FROM stocks WHERE title = '%s'",
                $stock
            );
            $result = $this->db->query($query);
            if (isset($result[0]['id_stock'])) {
                return $result[0]['id_stock'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
    * Get list of all or specific stock
    *
    * @param int $id
    * @return \PDOStatement
    */
    public function getStocks($id = null)
    {
        if ($id !== null) {
            $query = sprintf(
                "SELECT id_stock, title, title_wiki, icon_path FROM stocks WHERE id_stock = %d",
                $id
            );
        } else {
            $query = "SELECT id_stock, title, icon_path FROM stocks WHERE enabled=1 ORDER BY title ASC";
        }
        return $this->db->query($query);
    }

    /**
    * Get receipts for item
    *
    * @param int $id
    * @return array|false
    */
    public function getReceipts($id)
    {
        if ($id && is_numeric($id)) {
            $query = sprintf("SELECT * FROM receipts WHERE target_id = %d", $id);
            $result = $this->db->query($query);
            if ($result) {
                $i = 0;
                $collection = array();
                foreach ($result as $receipt) {
                    $collection[$i] = array(
                        'target_id' => $receipt['target_id'],
                        'target_qty' => $receipt['target_qty'],
                        'level' => 1,
                        'items' => array()
                    );
                    for ($j=1; $j <= 5; $j++) {
                        if ($receipt['ingredient_' . $j . '_id']) {
                            $collection[$i]['items'][$receipt['ingredient_' . $j . '_id']] = array(
                                'qty' => $receipt['ingredient_' . $j . '_qty']
                            );
                        }
                    }
                    $i++;
                }
                return $collection;
            }
        }
        return false;
    }

    /**
    * Get random receipt
    *
    * @return array|false
    */
    public function getRandomReceipt()
    {
        $query = "SELECT * FROM receipts ORDER BY RAND() LIMIT 0,1";
        $receipt = $this->db->query($query);
        if ($receipt) {
            $collection = array();
                $collection = array(
                    'target_id' => $receipt[0]['target_id'],
                    'target_qty' => $receipt[0]['target_qty'],
                    'level' => 1,
                    'items' => array()
                );
                for ($j=1; $j <= 5; $j++) {
                    if ($receipt[0]['ingredient_' . $j . '_id']) {
                        $collection['items'][$receipt[0]['ingredient_' . $j . '_id']] = array(
                            'qty' => $receipt[0]['ingredient_' . $j . '_qty']
                        );
                    }
                }
                return $collection;
        } else {
            return false;
        }
    }

    /**
    * Get current marketvalue for specified stock item
    *
    * @param int $id
    * @return \PDOStatement|false
    */
    public function getStockInfo($id)
    {
        if ($id && is_numeric($id)) {
            $query = sprintf(
                "SELECT s.id_stock, s.title, p.marketvalue, p.ts " .
                "FROM stocks s, prices p " .
                "WHERE s.id_stock=%d AND s.id_stock=p.stock_id " .
                "ORDER BY ts DESC " .
                "LIMIT 0,1 ",
                $id
            );
            $stockinfo = $this->db->query($query);
            if (isset($stockinfo[0])) {
                return $stockinfo[0];
            }
        }
        return false;
    }
}
