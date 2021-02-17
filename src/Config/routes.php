<?php
declare(strict_types=1);

use Phalcon\Mvc\Router\Group as RouterGroup;

$routes = new RouterGroup([
    'module' => \Phlexus\Modules\BaseUser\Module::getModuleName(),
    'namespace' => \Phlexus\Modules\BaseUser\Module::getHandlersNamespace() . '\\Controllers',
    'controller' => 'index',
    'action' => 'index',
]);

$routes->addGet('/user', [
    'controller' => 'index',
    'action' => 'index',
]);

$routes->addGet('/user/auth', [
    'controller' => 'auth',
    'action' => 'login',
]);

$routes->addPost('/user/auth/doLogin', [
    'controller' => 'auth',
    'action' => 'doLogin',
]);

$routes->addGet('/user/auth/remind', [
    'controller' => 'auth',
    'action' => 'remind',
]);

$routes->addGet('/user/auth/logout', [
    'controller' => 'auth',
    'action' => 'logout',
]);

return $routes;
