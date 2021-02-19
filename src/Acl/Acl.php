<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phlexus\Modules\BaseUser\Models\Profiles;
use Phalcon\Mvc\Dispatcher\Exception as MvcDispatcherException;

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

        $component = strtolower($module . '_' . $controller);
        if($acl->isComponent($component)) {
            if($acl->isAllowed($profile, $component, strtolower($action))) {
                $this->isAllowed = true;
            }
        } else {
            throw new MvcDispatcherException('Resource not found.');
        }
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }
}