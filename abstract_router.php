<?php namespace Framework\Controller;

use Respect\Config\Container;
use Respect\Rest;
use Respect\Validation\Exceptions\CallException;
use Respect\Validation\Exceptions\ExecutableException;
use Respect\Validation\Exceptions\FileException;

class AbstractRouter implements Rest\Routable {

    const CONTROLLER_PATH = \Framework::CONTROLLER_PATH;

    protected $module;
    protected $controller;
    protected $action;
    protected $parameters;

    public function __construct(String $modules = null)
    {
        $this->modules_enable = $modules;
    }

    public function get($params)
    {
        $this->setUrlParams($params);
        return $this->callAction();
    }

    protected function callAction()
    {
        $this->validAction();
        return call_user_func(
            [
                $this->getNamespaceController(),
                $this->action,
            ],
            $this->parameters
        );
    }

    protected function validAction()
    {
        self::validController();

        if (false === is_callable(array($this->getNamespaceController(), $this->action)))
        {
            throw new \Exception("Action '$this->action' não encontroada", 404);
        }
    }

    protected function validController()
    {
        if (false === class_exists($this->generateControllerNamespace()))
        {
            throw new \Exception("Classe ".$this->controller." não foi encontrodada", 404);
        }
    }

    private function setUrlParams($url_params)
    {
        $utilities = \Framework::get();

        $this->controller   = isset($url_params[0])
            ? $utilities->formater($url_params[0], \Utilities::FORMAT_CAMELCASE) : 'Index';

        $this->action       = isset($url_params[1])
            ? $utilities->formater($url_params[1], \Utilities::FORMAT_CAMELCASE_2) : 'index';

        $this->parameters   = isset($url_params[2])
            ? array_slice($url_params, 2) : [];
    }

    private function getNamespaceController()
    {
        if (empty($this->namespace_controller))
        {
            $module = $this->module ? $this->module."\\" : "";
            $controller = $this->controller;
            $this->namespace_controller = CONTROLLER_PATH.$module.$controller;
        }

        return $this->namespace_controller;
    }
}