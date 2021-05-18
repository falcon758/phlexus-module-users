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

$routes->addGet('/user/auth/create', [
    'controller' => 'auth',
    'action' => 'create',
]);

$routes->addPost('/user/auth/doCreate', [
    'controller' => 'auth',
    'action' => 'doCreate',
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

$routes->addPost('/user/auth/doRemind', [
    'controller' => 'auth',
    'action' => 'doRemind',
]);

$routes->addGet('/user/auth/recover/{hash_code:[0-9a-zA-Z]+}', [
    'controller' => 'auth',
    'action' => 'recover',
]);

$routes->addPost('/user/auth/doRecover', [
    'controller' => 'auth',
    'action' => 'doRecover',
]);

$routes->addGet('/user/auth/logout', [
    'controller' => 'auth',
    'action' => 'logout',
]);

return $routes;
