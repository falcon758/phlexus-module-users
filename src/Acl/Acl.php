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
        } else {
            $profile = $profile->name;
        }

        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        if($acl->isComponent(strtolower($module))) {
            if(!$acl->isAllowed($profile, $controller, $action)) {
                $this->isAllowed = true;
            }
        } else {
            throw new \Exception('Resource not found');
        }
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }
}