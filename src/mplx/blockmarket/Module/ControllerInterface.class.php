<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module;

use mplx\blockmarket\Service\Database;

interface ControllerInterface
{
    public function __construct(Database $db, \Twig_Environment $twig);
    public function initialize();
}
