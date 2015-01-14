<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module\Main\Controller;

use mplx\blockmarket\Service\Database;
use mplx\blockmarket\Service\Web;

use mplx\blockmarket\Util\BlockMarket;

class IndexController extends AbstractMainController
{
    protected $data;

    public function __construct(Database $db, \Twig_Environment $twig, Web $web)
    {
        parent::__construct($db, $twig, $web);

        $this->setActions(
            array('index', 'stock'),
            'stock'
        );

        $this->data = new BlockMarket\BlockData($db);
    }

    protected function executeIndex()
    {
        return $this->twig->render('error.tpl.html');
    }

    protected function executeStock()
    {
        // initialize
        $data = array();

        // which one?
        if (!isset($_GET['stockid']) || !is_numeric($_GET['stockid'])) {
            $id = 87;
        } else {
            $id = $_GET['stockid'];
        }

        // get stock
        $temp = $this->data->getStocks($id);
        if (! $temp) {
            return $this->twig->render('error.tpl.html');
        } else {
            $data['basic'] = $temp[0];
        }

        // meta data
        if (isset($data['basic']['title_wiki'])) {
            $data['basic']['url']['wiki'] = BM_WIKI_URL . 'wiki/' . $data['basic']['title_wiki'];
            //$data['basic']['url']['icon'] = BM_WIKI_URL . 'wiki/File:' . $data['basic']['title_wiki'] . '_Icon.png';
        }
        if (isset($data['basic']['icon_path'])) {
            $data['basic']['url']['icon'] = BM_WIKI_URL . $data['basic']['icon_path'];
        }

        // config
        $data['config']['timezone']['zone'] = date("e");
        $data['config']['timezone']['gmtdiff'] = date("O");
        $data['config']['timezone']['gmtdiffhours'] = substr($data['config']['timezone']['gmtdiff'], 0, 3);
        $data['config']['wiki_url'] = BM_WIKI_URL;
        $data['config']['issues_url'] = BM_DEV_ISSUE_TRACKER;
        $data['config']['source_url'] = BM_DEV_SOURCECODE;

        // favorites
        if ($this->web->getCookie('favorites')) {
            $query = '';
            $result = preg_match_all('/([0-9]+)/', $this->web->getCookie('favorites'), $lookup);
            if (is_array($lookup) && count($lookup[0])>0 && count($lookup[0])<=5) {
                $lookup = array_unique($lookup[0]);
                foreach ($lookup as $fav) {
                    if (is_numeric($fav)) {
                        if ($query != '') {
                            $query .= " OR ";
                        }
                        $query .= "id_stock = " . $fav;
                    }
                }
                $query = "SELECT id_stock, title, icon_path " .
                         "FROM stocks " .
                         "WHERE enabled=1 AND (" . $query . ") " .
                         "ORDER BY title ASC";
                $data['favorites'] = $this->db->query($query);
            }
        }

        // last 100
        $query = sprintf(
            "SELECT marketvalue, UNIX_TIMESTAMP(ts)*1000 AS tstamp " .
            "FROM prices " .
            "WHERE stock_id = %d " .
            "ORDER BY ts DESC LIMIT 0,150",
            $id
        );
        $temp = $this->db->query($query);
        $data['hundret'] = array_reverse($temp);

        // 24h
        $query = sprintf(
            "SELECT marketvalue, UNIX_TIMESTAMP(ts)*1000 AS tstamp " .
            "FROM prices " .
            "WHERE stock_id = %d AND ts >= DATE_SUB(NOW(), INTERVAL 1 DAY)",
            $id
        );
        $temp = $this->db->query($query);
        $data['twentyfour'] = $temp;

        // market data
        $data['current']['marketvalue'] = $data['twentyfour'][count($data['twentyfour'])-1]['marketvalue'];
        $units = 1;
        if ($data['current']['marketvalue']<=0.009999) {
            $units = 100;
        }
        $data['current']['units'] = $units;

        // last month averages
        $query = "SELECT MAX(marketvalue) AS pricemax, MIN(marketvalue) AS pricemin, " .
                    "AVG(marketvalue) AS priceavg, date(ts) AS bmdate, " .
                    "UNIX_TIMESTAMP(date(ts))*1000 AS tstamp " .
                "FROM prices " .
                "WHERE stock_id = %d AND date(ts)>=DATE_SUB(NOW(), INTERVAL 1 MONTH) " .
                "GROUP BY date(ts) " .
                "ORDER BY ts ASC ";
        $query = sprintf($query, $id);
        $temp = $this->db->query($query);
        $data['month'] = $temp;

        // receipts
        $receipts = $this->data->getReceipts($id);
        if (is_array($receipts)) {
            $updated = 1;
            $iterations = 0;
            while ($updated==1 && $iterations<20) {
                $updated = 0;
                foreach ($receipts as $reckey => $receipt) {
                    if ($iterations >= $receipt['level']) {
                        continue;
                    }
                    $clone = $receipts[$reckey];
                    foreach ($clone['items'] as $itemid => $item) {
                        $buildInfo = $this->data->getReceipts($itemid);
                        if (is_array($buildInfo) && $buildInfo[0]['target_qty']>0) {
                            $qty = $clone['items'][$itemid]['qty'];
                            unset($clone['items'][$itemid]);

                            foreach ($buildInfo[0]['items'] as $subkey => $subitem) {
                                if (isset($clone['items'][$subkey])) {
                                    $clone['items'][$subkey]['qty'] =
                                        $clone['items'][$subkey]['qty'] +
                                        $subitem['qty'] * $qty;
                                } else {
                                    $clone['items'][$subkey] = array(
                                        'qty' => $subitem['qty'] * $qty
                                    );
                                }
                            }
                            $updated = 1;
                        }
                    }
                    if ($updated==1) {
                        $clone['level']++;
                        $receipts[] = $clone;
                    }
                }
                $iterations++;
            }

            foreach ($receipts as $reckey => $receipt) {
                $productionprice = 0;
                $stockInfo = $this->data->getStockInfo($id);
                $receipts[$reckey]['target_title'] = $stockInfo['title'];
                $receipts[$reckey]['target_marketvalue'] = $stockInfo['marketvalue'];
                foreach ($receipt['items'] as $ikey => $item) {
                    $stockInfo = $this->data->getStockInfo($ikey);
                    $receipts[$reckey]['items'][$ikey]['id'] = $ikey;
                    $receipts[$reckey]['items'][$ikey]['title'] = $stockInfo['title'];
                    $receipts[$reckey]['items'][$ikey]['marketvalue'] = $stockInfo['marketvalue'];
                    $productionprice = $productionprice + $stockInfo['marketvalue'] * $item['qty'];
                }
                $receipts[$reckey]['target_costs'] = $productionprice;
                $receipts[$reckey]['target_income'] = $receipts[$reckey]['target_marketvalue'] - $productionprice;
            }
            // done
            $data['receipts'] = $receipts;
        }

/*
echo "<pre>";
print_r($receipts);
die();
*/

        // item used in production
        $query = "SELECT s.id_stock AS id, s.title " .
                 "FROM stocks s, receipts r " .
                 "WHERE s.id_stock=r.target_id AND r.target_id<>%d AND (" .
                 "r.ingredient_1_id=%d OR r.ingredient_2_id=%d OR " .
                 "r.ingredient_3_id=%d OR r.ingredient_4_id=%d OR r.ingredient_5_id=%d" .
                 ")" .
                 "ORDER BY s.title ASC";
        $query = sprintf($query, $id, $id, $id, $id, $id, $id);
        $temp = $this->db->query($query);
        $data['itemusedfor'] = $temp;

        // render template
        return $this->twig->render(
            'module/main/index/index.tpl.html',
            array(
                'data' => $data,
                'stocks' => $this->data->getStocks()
            )
        );
    }
}
