<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module\Main\Controller;

use mplx\blockmarket\Module\AbstractController;

use mplx\blockmarket\Service\Database;
use mplx\blockmarket\Service\Web;

/**
* Abstract main controller
*/
abstract class AbstractMainController extends AbstractController
{
    /**
    * {@inheritdoc}
    */
    public function __construct(Database $db, \Twig_Environment $twig, Web $web)
    {
        parent::__construct($db, $twig, $web);
    }
}
