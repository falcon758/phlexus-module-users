<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phlexus\Modules\BaseUser\Models\Profiles;

final class Acl
{
    private $isAllowed = false;

    public function __construct(DefaultAcl $acl, Dispatcher $dispatcher, View $view)
    {
        $profile = Profiles::getUserProfile();

        if($profile === null) {
            $profile = Profiles::GUEST;
        }

        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $resource = strtolower($module) . '/' . strtolower($controller);

        if(!$acl->isAllowed($profile->name, $resource, $action)) {
            $this->isAllowed = true;
        }

        var_dump($this->isAllowed); exit();
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }
}