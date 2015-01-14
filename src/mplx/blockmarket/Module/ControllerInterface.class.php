<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module;

use mplx\blockmarket\Service\Database;
use mplx\blockmarket\Service\Web;

interface ControllerInterface
{
    public function __construct(Database $db, \Twig_Environment $twig, Web $web);
    public function initialize();
}
