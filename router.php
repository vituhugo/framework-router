<?php namespace Framework\Controller;

use Respect\Config\Container;

class Router extends AbstractRouter {

    static protected $_module;
    static protected $_controller;
    static protected $_action;
    static protected $_parameters;

    public function get($params)
    {
        $retorno = parent::get($params);

        self::$_module = $this->module;
        self::$_controller = $this->controller;
        self::$_action = $this->action;
        self::$_parameters = $this->parameters;

        return $retorno;
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
        return self::format(self::$controller, $format);
    }

    static public function getModule($format = false)
    {
        return self::format(self::$module, $format);
    }

    static public function getAction($format = false)
    {
        return self::format(self::$action, $format);
    }

    static public function getUrlParameters($format = false)
    {
        return self::format(self::$parameters, $format);
    }

    static public function format($string, $format)
    {
        $utilities = \Framework::utilities();
        return $utilities->formater($string, $format, \Utilities::SEPARATOR_CAMELCASE);
    }

}