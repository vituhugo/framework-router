<?php namespace Framework\Controller;

use Respect\Config\Container;
use Respect\Rest;
use Respect\Validation\Exceptions\CallException;
use Respect\Validation\Exceptions\ExecutableException;
use Respect\Validation\Exceptions\FileException;
use Framework\Utilities;
use Framework\ExceptionHttpResponse;

class AbstractRouter implements Rest\Routable {

    const CONTROLLER_PATH = \Framework::CONTROLLER_PATH;

    protected $name_module;
    protected $name_controller;
    protected $name_action;
    protected $parameters;
    protected $obj_controller;

    public function __construct(String $name_module = null)
    {
        $this->module = $name_module;
    }

    public function get($params)
    {
        $this->setUrlParams($params);
        return $this->callAction();
    }

    protected function callAction()
    {
        $this->validController();

        $class_controller = $this->getNamespaceController();
        $this->obj_controller = new $class_controller;

        $this->validAction();

        return call_user_func(
            [
                $this->obj_controller,
                $this->name_action,
            ],
            $this->parameters
        );
    }

    protected function validAction()
    {
        $this->validController();

        if (false === is_callable(array($this->getNamespaceController(), $this->action)))
        {
            throw new \Exception("Action '$this->action' não encontroada", 404);
        }
    }

    protected function validController()
    {
        if (false === class_exists($this->getNamespaceController()))
        {
            throw new ExceptionHttpResponse("Classe ".$this->name_controller." não foi encontrodada", 404);
        }
    }

    protected function getNamespaceController()
    {
        $module = $this->name_module ? $this->name_module."\\" : "";
        $controller = $this->name_controller;
        return self::CONTROLLER_PATH.$module.$controller;
    }

    protected function setUrlParams($url_params)
    {
        $utilities = \Framework::utilities();

        $this->name_controller   = isset($url_params[0])
            ? $utilities->formater($url_params[0], Utilities::FORMAT_CAMELCASE) : 'Index';

        $this->name_action       = isset($url_params[1])
            ? $utilities->formater($url_params[1], Utilities::FORMAT_CAMELCASE_2) : 'index';

        $this->parameters   = isset($url_params[2])
            ? array_slice($url_params, 2) : [];
    }
}