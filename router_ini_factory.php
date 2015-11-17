<?php namespace Framework;

use Respect\Config\Container;
use Respect\Rest\Router;

class RouterIniFactory extends Router {

    public $routes_ini;

    public function __construct(Container $routes_ini, $virtualHost = null)
    {
        $this->routes_ini = $routes_ini;
        $this->virtualHost = $virtualHost;
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
        if ($this->haveModules())
            return $this->modulesGenerator();

        else
            return $this->any("/**", new Controller\Router());
    }

    private function modulesGenerator()
    {
        foreach($this->routes_ini->module as $module_uri => $module_real_path)
            yield $this->any("/" . rtrim($module_uri, "/") . "/**", new Controller\Router($module_real_path));
    }

    protected function iniExceptionRoute()
    {
        $this->exceptionRoute(
            '\\Exception',
            function($e)
            {
                if ($e instanceof \Framework\ExceptionHttpResponse)
                {
                    $code = $e->getCode();
                    http_response_code($code);
                }

                if (class_exists("\\Application\\Mvc\\Controller\\Exception", true))
                {
                    $e_controller = new \Application\Mvc\Controller\Exception();
                    $e_controller->dispatch($e);
                } else
                {
                    return null;
                }
            }
        );
    }

    protected function iniCustomGenerate()
    {
        if (false === $this->haveCustom())
            return null;

        $routes_custom = $this->routes_ini->custom;
        foreach($routes_custom as $custom_path => $real_path_in_pieces)
        {
            $this->newCustomRoute($custom_path, ...array_reverse($real_path_in_pieces));
        }
    }

    protected function newCustomRoute($path, $action, $controller, $module = null)
    {
        $callActionController = function() use ($action, $controller, $module)
        {
            $url_params = array_merge(array_filter([$module, $controller, $action]) , func_get_args());

            $manager_cotroller = new Controller\Router($module);
            return $manager_cotroller->get($url_params);
        };

        $this->get($path, $callActionController);
    }

    private function haveModules()
    {
        return isset($this->routes_ini->module) && isset($this->routes_ini->module[0]);
    }

    private function haveCustom() {
        return isset($this->routes_ini->custom);
    }

    protected function alwaysInJson()
    {
        return $this->always('Accept', [
                'text/html' =>
                function($data)
                {
                    if (empty($data))
                        return null;

                    $_SERVER['CONTENT_TYPE'] = "application/json";
                    header('Content-type: application/json');

                    if (is_string($data))
                        $data = [$data];

                    return json_encode($data,true);
                }
            ]
        );
    }
}
