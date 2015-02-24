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
use Symfony\Component\HttpFoundation\Response;

/**
* Abstract Controller
*/
abstract class AbstractController implements ControllerInterface
{
    /**
    * Current action
    *
    * @var string $action
    */
    protected $action;

    /**
    * defaulta action
    *
    * @var string $action_default
    */
    protected $action_default;

    /**
    * List of available actions
    *
    * @var array $actions
    */
    protected $actions = array();

    /**
    * Database object
    *
    *  @var \mplx\blockmarket\Service\Database $db
    */
    protected $db;

    /**
    * Template engine object
    *
    * @var \Twig_Environment $twig
    */
    protected $twig;

    /**
    * Web service
    *
    * @var \mplx\blockmarket\Service\Web $web
    */
    protected $web;

    /**
    * Constructor
    *
    * @param Database $db
    * @param \Twig_Environment $twig
    * @param Web $web
    */
    public function __construct(Database $db, \Twig_Environment $twig, Web $web)
    {
        $this->db = $db;
        $this->twig = $twig;
        $this->web = $web;
    }

    /**
    * Initialize controller, and optionally initialize action
    *
    * @param string $action
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function initialize($action = null)
    {
        if (!in_array($action, $this->actions) || !($result = $this->initializeAction($action))) {
            $result = $this->initializeAction($this->action_default);
        }
        if ($result instanceof Response) {
            return $result;
        } else {
            return new Response($result);
        }
    }

    /**
    * Run action
    *
    * @param string $action
    * @return mixed
    */
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

    /**
    * Set available actions
    *
    * @param string|array $actions
    * @param string $default
    * @param bool $append
    * @return AbstractController
    */
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

    /**
    * Get current action
    * @return string
    */
    public function getAction()
    {
        return $this->action;
    }
}
