<?php namespace Framework;

use Respect\Config\Container;
use Respect\Rest\Router;

class RouterIniFactory extends Router {

    public $routes_ini;

    public function __construct(Container $routes_ini)
    {
        if (!isset($routes_in_ini->virtualHost))
            throw new \Exception("É necessário Definir a diretiva virtualHost no seu container de rotas.");

        $this->routes_ini = $routes_ini;
        $this->virtualHost = $this->routes_ini->virtualHost;
        $this->routesGenerate();
    }

    protected function routesGenerate()
    {
        $this->iniModulesGenerate();
        $this->iniCustomGenerate();
        $this->iniExceptionRoute();
        $this->alwaysInJson();
    }

    protected function iniModulesGenerate()
    {
        if (!$this->haveModules)
            return $this->any("/**", new Controller\Router());

        foreach($this->routes_ini->modules as $module_uri => $module_real_path)
            yield $this->any("/" . rtrim($module_uri, "/") . "/**", new Controller\Router($module_real_path));
    }

    protected function iniExceptionRoute()
    {
        $this->exceptionRoute(
            '\\Exception',
            function(Exception $e)
            {
                if ($e instanceof \Framework\ExceptionHttpResponse)
                {
                    $code = $e->getCode();
                    http_response_code($code);
                }

                if (class_exists("\\Application\\Mvc\\Controller\\Exception", true))
                {
                    $e_controller = new \Application\Mvc\Controller\Exception();
                    return $e_controller->dispatch($e);
                } else
                {
                    return $e->getMessage();
                }
            }
        );
    }

    protected function iniCustomGenerate()
    {
        if (false === $this->haveCustom())
            return null;

        $routes_custom = $this->routes_ini->custom;
        foreach($routes_custom as $probably_controller => $route)
        {
            foreach($route as $probably_action => $probably_path_action)
            {
                if (is_object($probably_path_action))
                {
                    //It's Wrong! path_action is a container of $real_path_action
                    foreach($probably_path_action as $real_action => $real_path_action)
                    {
                        self::newCustomRoute
                        (
                            $real_path_action,
                            $real_action,
                            $probably_action, //Controller Name
                            $probably_controller //Module Name
                        ); continue;
                    }
                }
                self::newCustomRoute
                (
                    $probably_path_action,
                    $probably_action,
                    $probably_controller
                );
            }
        }
    }

    public function newCustomRoute($path, $action, $controller, $module = null)
    {
        $callControllerAction = function() use ($action, $controller, $module)
        {
            $url_params = array_merge(array_filter([$module, $controller, $action]) , func_get_arg());

            $manager_cotroller = new Controller\Router($module);
            return $manager_cotroller->get($url_params);
        };

        $this->get($path, $callActionController);
    }

    private function haveModules()
    {
        return isset($this->routes_ini->modules;
    }

    private function haveCustom() {
        return isset($this->routes_ini->custom);
    }

    protected function alwaysInJson()
    {
        return $this->always('Accept', [
                'application/json' =>
                function($data)
                {
                    if (empty($data)) return null;

                    header('Content-type: application/json');
                    if ( is_string($data))
                    {
                        $data = array($data);
                    }
                    return json_encode($data,true);
                }
            ]
        );
    }
}