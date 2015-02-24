<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module\Main;

use mplx\blockmarket\Module\ModuleInterface;

/**
* Main module
*/
class MainModule implements ModuleInterface
{
    /**
    * Lists available controllers
    * @return array
    */
    public function getControllers()
    {
        return array(
            'index' => __NAMESPACE__ . '\Controller\IndexController'
        );
    }
}
