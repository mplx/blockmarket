<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module\Main;

use mplx\blockmarket\Module\ModuleInterface;

class MainModule implements ModuleInterface
{
    public function getControllers()
    {
        return array(
            'index' => __NAMESPACE__ . '\Controller\IndexController'
        );
    }
}
