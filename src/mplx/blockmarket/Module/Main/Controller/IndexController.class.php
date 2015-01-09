<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module\Main\Controller;

use mplx\blockmarket\Service\Database;

class IndexController extends AbstractMainController
{

    public function __construct(Database $db, \Twig_Environment $twig)
    {
        parent::__construct($db, $twig);
        $this->setActions(
            array('index', 'stock'), 'stock')
        ;
    }

    protected function executeIndex()
    {
        return $this->twig->render('error.tpl.html');
    }

    protected function executeStock()
    {
        if (!isset($_GET['stockid']) || !is_numeric($_GET['stockid'])) {
            $id = 87;
        } else {
            $id = $_GET['stockid'];
        }

        $data = array();

        $temp = $this->db->getStocks($id);
        if (! $temp) {
            return $this->twig->render('error.tpl.html');
        } else {
            $data['basic'] = $temp[0];
        }

        $query = sprintf("SELECT marketvalue, UNIX_TIMESTAMP(ts)*1000 AS tstamp FROM prices WHERE stock_id = %d ORDER BY ts DESC LIMIT 0,100", $id);
        $temp = $this->db->query($query);
        $data['hundret'] = array_reverse($temp);

        $query = sprintf("SELECT marketvalue, UNIX_TIMESTAMP(ts)*1000 AS tstamp FROM prices WHERE stock_id = %d AND ts >= DATE_SUB(NOW(), INTERVAL 1 DAY)", $id);
        $temp = $this->db->query($query);
        $data['twentyfour'] = $temp;

        $query = "SELECT MAX(marketvalue) AS pricemax, MIN(marketvalue) AS pricemin, AVG(marketvalue) AS priceavg, date(ts) AS bmdate, UNIX_TIMESTAMP(date(ts))*1000 AS tstamp " .
                "FROM prices " .
                "WHERE stock_id = %d AND date(ts)>=DATE_SUB(NOW(), INTERVAL 1 MONTH) " .
                "GROUP BY date(ts) " .
                "ORDER BY ts ASC ";
        $query = sprintf($query, $id);
        $temp = $this->db->query($query);
        $data['month'] = $temp;

        return $this->twig->render(
            'module/main/index/index.tpl.html',
            array(
                'data' => $data,
                'stocks' => $this->db->getStocks()
            )
        );
    }
}
