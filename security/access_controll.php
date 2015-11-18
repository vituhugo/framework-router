<?php namespace Framework\Controller;



class AccessControll
{
    protected $users_permissioned = [];
    protected $actions_without_permission = [];

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->setUsersPermissioned($controller);
        $this->setActionsWithoutPermission($controller);
    }

    protected function setActionsWithoutPermission($controller)
    {
        if (isset($controller->actions_without_permission))
            $this->users_permissioned = $controller->actions_without_permission;
    }

    protected function setUsersPermissioned($controller)
    {
        if (isset($controller->group_users_permissioned))
            $this->users_permissioned = $controller->group_users_permissioned;
    }

    public function isAccessible($action)
    {
        if (false === $this->isPermissioned($action))
            return false;

        return true;
    }

    protected function isPermissioned()
    {
        return true;
    }
}