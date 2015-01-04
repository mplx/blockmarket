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
        $this->setActions(array('index'), 'index');
    }

    protected function executeIndex()
    {
        return $this->twig->render('module/main/index/index.tpl.html', array());
    }
}
