<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket;

use Symfony\Component\HttpFoundation\Response;

class Router
{
    protected $map = array();
    protected $services = array();
    public $default_module;

    public function __construct()
    {
        global $db;

        $this->default_module = 'index';
        $this->services['db'] = $db;

        $loader = new \Twig_Loader_Filesystem(BM_PATH_TPL . BM_THEME);
        $this->services['twig'] = new \Twig_Environment($loader);
        if (BM_DEBUG) {
            $this->services['twig']->enableDebug();
        }

        $filter = new \Twig_SimpleFilter('coins', '\mplx\blockmarket\Util\BlockMarket\BlockUtil::toCoinsString');
        $this->services['twig']->addFilter($filter);

        $this->services['web'] = new \mplx\blockmarket\Service\Web();

        $modules = $this->getModules();
        foreach ($modules as $id => $module) {
            $this->map[$id] = $module->getControllers();
        }
    }

    public function getModules()
    {
        return array(
            'index' => new Module\Main\MainModule()
        );
    }

    public function run()
    {
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/') {
            $module = $this->default_module;
            $action = null;
        } elseif (isset($_SERVER['REQUEST_URI']) && preg_match(BM_PRETTYURL, $_SERVER['REQUEST_URI'], $requesturi)) {
            $module = $requesturi[1];
            $action = $requesturi[2];
        } else {
            $module = $this->services['web']->getRequestGet('module', $this->default_module);
            $action = $this->services['web']->getRequestGet('action', null);
        }

        $controller = $this->getController($module);
        $response = $controller->initialize($action);
        if (!($response instanceof Response)) {
            throw new \LogicException('Controller did not return a Response object.');
        }
        $response->send();
    }

    public function getController($mod)
    {
        $controller = $this->getControllerClass($mod);
        if ($controller === false) {
            throw new \InvalidArgumentException('Controller is not registered');
        }
        $controller = new $controller($this->services['db'], $this->services['twig'], $this->services['web']);
        if (!$controller instanceof \mplx\blockmarket\Module\ControllerInterface) {
            throw new \Exception('Controller does not use ControllerInterface');
        }
        return $controller;
    }

    protected function getControllerClass($mod)
    {
        if (strpos($mod, '_') !== false) {
            list($mod, $controller) = explode('_', $mod);
        } else {
            $controller = $mod;
        }
        if (!isset($this->map[$mod][$controller]) || !class_exists($this->map[$mod][$controller])) {
            return false;
        } else {
            return $this->map[$mod][$controller];
        }
    }
}
