<?php namespace Framework\Controller;

use Framework\Utilities;
use Respect\Config\Container;

class Router extends AbstractRouter {

    static protected $_module;
    static protected $_controller;
    static protected $_action;
    static protected $_parameters;

    protected function setUrlParams($params)
    {
        parent::setUrlParams($params);

        self::$_module = $this->module;
        self::$_controller = $this->controller;
        self::$_action = $this->action;
        self::$_parameters = $this->parameters;
    }

    static public function getUrlParams($format = false)
    {
        return
            [
                'module'        => self::getModule($format),
                'controller'    => self::getController($format),
                'action'        => self::getAction($format),
                'parametros'    => self::getParameters($format)
            ];
    }

    static public function getController($format = false)
    {
        return self::format(self::$_controller, $format);
    }

    static public function getModule($format = false)
    {
        return self::format(self::$_module, $format);
    }

    static public function getAction($format = false)
    {
        return self::format(self::$_action, $format);
    }

    static public function getUrlParameters($format = false)
    {
        return self::format(self::$_parameters, $format);
    }

    static public function format($string, $format)
    {
        $utilities = \Framework::utilities();
        return $utilities->formater($string, $format, Utilities::SEPARATOR_CAMELCASE);
    }

}