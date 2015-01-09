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
        if (isset($_GET['module'])) {
            $module = $_GET['module'];
        } else {
            $module = $this->default_module;
        }
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
        } else {
            $action = null;
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
        $controller = new $controller($this->services['db'], $this->services['twig']);
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
