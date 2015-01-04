<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Module;

use mplx\blockmarket\Service\Database;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController implements ControllerInterface
{
    protected $action;
    protected $action_default;
    protected $actions = array();

    protected $db;
    protected $twig;

    public function __construct(Database $db, \Twig_Environment $twig)
    {
        $this->db = $db;
        $this->twig = $twig;
    }

    public function initialize($action = null)
    {
        $action = 'index';
        if (!in_array($action, $this->actions) || !($result = $this->initializeAction($action))) {
            $result = $this->initializeAction($this->action_default);
        }
        if ($result instanceof Response) {
            return $result;
        } else {
            return new Response($result);
        }
    }

    protected function initializeAction($action)
    {
        $method = 'execute' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->action = $action;
            $result = $this->$method();
            return ($result === null) ? true : $result;
        } else {
            return false;
        }
    }

    protected function setActions($actions, $default = null, $append = true)
    {
        if (!is_array($actions)) {
            $actions = array($actions);
        }
        if ($append) {
            $this->actions = array_merge($actions);
        } else {
            $this->actions = $actions;
        }
        if ($default !== null) {
            $this->action_default = $default;
        }
        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }
}
