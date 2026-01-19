<?php
declare(strict_types=1);

use Phalcon\Mvc\Router\Group as RouterGroup;

$routes = new RouterGroup([
    'module'     => \Phlexus\Modules\BaseUser\Module::getModuleName(),
    'namespace'  => \Phlexus\Modules\BaseUser\Module::getHandlersNamespace() . '\\Controllers',
    'controller' => 'index',
    'action'     => 'index',
]);

$routes->addGet('/user', [
    'controller' => 'index',
    'action'     => 'index',
]);

$routes->addGet('/user/auth/create', [
    'controller' => 'auth',
    'action'     => 'create',
]);

$routes->addPost('/user/auth/doCreate', [
    'controller' => 'auth',
    'action'     => 'doCreate',
]);

$routes->addGet('/user/auth/activate/{hashCode:[0-9a-zA-Z]+}', [
    'controller' => 'auth',
    'action'     => 'activate',
]);

$routes->addGet('/user/auth', [
    'controller' => 'auth',
    'action'     => 'login',
]);

$routes->addPost('/user/auth/doLogin', [
    'controller' => 'auth',
    'action'     => 'doLogin',
]);

// Social login: Google
$routes->addGet('/user/auth/google', [
    'controller' => 'auth',
    'action'     => 'google',
]);

$routes->addGet('/user/auth/google/callback', [
    'controller' => 'auth',
    'action'     => 'googleCallback',
]);

// Social login: Apple
$routes->addGet('/user/auth/apple', [
    'controller' => 'auth',
    'action'     => 'apple',
]);

$routes->addGet('/user/auth/apple/callback', [
    'controller' => 'auth',
    'action'     => 'appleCallback',
]);

$routes->addGet('/user/auth/remind', [
    'controller' => 'auth',
    'action'     => 'remind',
]);

$routes->addPost('/user/auth/doRemind', [
    'controller' => 'auth',
    'action'     => 'doRemind',
]);

$routes->addGet('/user/auth/recover/{hashCode:[0-9a-zA-Z]+}', [
    'controller' => 'auth',
    'action'     => 'recover',
]);

$routes->addPost('/user/auth/doRecover', [
    'controller' => 'auth',
    'action'     => 'doRecover',
]);

$routes->addGet('/user/auth/logout', [
    'controller' => 'auth',
    'action'     => 'logout',
]);


$routes->addGet('/user/users', [
    'controller' => 'user',
    'action'     => 'view',
]);

$routes->addGet('/baseuser/user', [
    'controller' => 'user',
    'action'     => 'view',
]);

foreach (['create', 'view'] as $action) {
    $routes->addGet('/user/' . $action, [
        'controller' => 'user',
        'action'     => $action,
    ]);
}

$routes->addGet('/baseuser/user/edit/{id:[0-9]+}', [
    'controller' => 'user',
    'action'     => 'edit',
]);

$routes->addPost('/baseuser/user/save', [
    'controller' => 'user',
    'action'     => 'save',
]);

$routes->addPost('/baseuser/user/delete/{id:[0-9]+}', [
    'controller' => 'user',
    'action'     => 'delete',
]);

$routes->addGet('/profile', [
    'controller' => 'profile',
    'action'     => 'edit',
]);

$routes->addPost('/profile/save', [
    'controller' => 'profile',
    'action'     => 'save',
]);

$routes->addPost('/profile/requestRemoval', [
    'controller' => 'profile',
    'action'     => 'requestProfileRemoval',
]);

return $routes;
